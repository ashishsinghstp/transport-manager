<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Document;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DriverControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_index_renders_driver_list(): void
    {
        $driver = Driver::factory()->create();

        $response = $this->get('/drivers');

        $response->assertOk();
        $response->assertSee($driver->full_name);
        $response->assertSee($driver->email);
    }

    public function test_create_renders_driver_form(): void
    {
        $response = $this->get('/drivers/create');

        $response->assertOk();
        $response->assertSee('Full Name');
        $response->assertSee('Documents');
    }

    public function test_edit_renders_form_with_current_driver_values(): void
    {
        $driver = Driver::factory()->create();

        $response = $this->get("/drivers/{$driver->id}/edit");

        $response->assertOk();
        $response->assertSee($driver->full_name);
        $response->assertSee($driver->email);
    }

    public function test_store_creates_driver_with_current_document(): void
    {
        Storage::fake('local');

        $response = $this->post('/drivers', $this->validPayload());

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('success');

        $driver = Driver::where('email', 'jane@example.com')->firstOrFail();

        $this->assertModelExists($driver);
        $this->assertSame('Jane Doe', $driver->full_name);
        $this->assertSame('active', $driver->status);
        $this->assertSame('32.50', $driver->pay_rate);

        $document = $driver->documents()->sole();

        $this->assertSame('license.pdf', $document->original_name);
        $this->assertTrue($document->is_current);
        Storage::disk('local')->assertExists($document->path);
    }

    public function test_store_rejects_empty_payload_with_validation_errors(): void
    {
        $response = $this->post('/drivers', []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['full_name', 'email', 'phone', 'pay_rate', 'document', 'status']);
        $this->assertDatabaseCount('drivers', 0);
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_store_rejects_unsupported_document_type(): void
    {
        Storage::fake('local');

        $response = $this->post('/drivers', $this->validPayload([
            'document' => UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload'),
        ]));

        $response->assertSessionHasErrors('document');
        $this->assertDatabaseCount('drivers', 0);
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_store_rejects_status_outside_allowed_values(): void
    {
        $response = $this->post('/drivers', $this->validPayload(['status' => 'archived']));

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseCount('drivers', 0);
    }

    public function test_update_adds_new_document_and_keeps_previous_document(): void
    {
        Storage::fake('local');
        $oldPath = 'documents/old-license.pdf';
        Storage::disk('local')->put($oldPath, 'old content');

        $driver = Driver::factory()->create();
        $oldDocument = $this->addDocument($driver, $oldPath, 'old-license.pdf');

        $response = $this->put("/drivers/{$driver->id}", $this->validPayload([
            'email' => 'changed@example.com',
            'document' => UploadedFile::fake()->create('new-license.pdf', 50, 'application/pdf'),
        ]));

        $response->assertRedirect(route('drivers.index'));

        $driver->refresh();
        $oldDocument->refresh();

        $this->assertSame('changed@example.com', $driver->email);
        $this->assertCount(2, $driver->documents()->get());
        $this->assertFalse($oldDocument->is_current);
        Storage::disk('local')->assertExists($oldPath);

        $current = $driver->currentDocument;

        $this->assertNotNull($current);
        $this->assertNotSame($oldDocument->id, $current->id);
        $this->assertSame('new-license.pdf', $current->original_name);
        Storage::disk('local')->assertExists($current->path);
    }

    public function test_update_keeps_existing_document_when_no_file_uploaded(): void
    {
        Storage::fake('local');
        $oldPath = 'documents/keep-license.pdf';
        Storage::disk('local')->put($oldPath, 'content');

        $driver = Driver::factory()->create();
        $oldDocument = $this->addDocument($driver, $oldPath, 'keep-license.pdf');

        $payload = $this->validPayload();
        unset($payload['document']);

        $response = $this->put("/drivers/{$driver->id}", $payload);

        $response->assertRedirect(route('drivers.index'));

        $driver->refresh();
        $oldDocument->refresh();

        $this->assertCount(1, $driver->documents()->get());
        $this->assertTrue($oldDocument->is_current);
        $this->assertSame($oldPath, $driver->currentDocument->path);
        Storage::disk('local')->assertExists($oldPath);
    }

    public function test_destroy_soft_deletes_driver_and_documents_but_keeps_files(): void
    {
        Storage::fake('local');
        $path = 'documents/doomed.pdf';
        Storage::disk('local')->put($path, 'content');

        $driver = Driver::factory()->create();
        $document = $this->addDocument($driver, $path, 'doomed.pdf');

        $response = $this->delete("/drivers/{$driver->id}");

        $response->assertRedirect(route('drivers.index'));
        $this->assertSoftDeleted($driver);
        $this->assertSoftDeleted('documents', ['id' => $document->id]);
        Storage::disk('local')->assertExists($path);
    }

    public function test_document_download_returns_stored_file(): void
    {
        Storage::fake('local');
        $path = 'documents/certificate.pdf';
        Storage::disk('local')->put($path, 'pdf-bytes');

        $driver = Driver::factory()->create();
        $document = $this->addDocument($driver, $path, 'certificate.pdf');

        $response = $this->get(route('drivers.documents.download', [$driver, $document]));

        $response->assertOk();
        $response->assertDownload('certificate.pdf');
    }

    public function test_document_download_returns_404_when_file_missing(): void
    {
        Storage::fake('local');

        $driver = Driver::factory()->create();
        $document = $this->addDocument($driver, 'documents/missing.pdf', 'missing.pdf');

        $response = $this->get(route('drivers.documents.download', [$driver, $document]));

        $response->assertNotFound();
    }

    public function test_document_download_returns_404_for_another_driver(): void
    {
        Storage::fake('local');
        $path = 'documents/private.pdf';
        Storage::disk('local')->put($path, 'content');

        $owner = Driver::factory()->create();
        $other = Driver::factory()->create();
        $document = $this->addDocument($owner, $path, 'private.pdf');

        $response = $this->get(route('drivers.documents.download', [$other, $document]));

        $response->assertNotFound();
    }

    private function addDocument(Driver $driver, string $path, string $originalName): Document
    {
        return $driver->documents()->create([
            'original_name' => $originalName,
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size' => 10,
            'is_current' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '416-555-0100',
            'pay_rate' => 32.50,
            'company_address' => '123 Queen St, Toronto, ON',
            'hst_number' => '123456789RT0001',
            'document' => UploadedFile::fake()->create('license.pdf', 100, 'application/pdf'),
            'status' => 'active',
        ], $overrides);
    }
}
