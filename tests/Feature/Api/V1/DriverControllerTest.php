<?php

namespace Tests\Feature\Api\V1;

use App\Models\Document;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->dispatcher()->create());
    }

    public function test_guests_cannot_access_drivers(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/v1/drivers')->assertUnauthorized();
    }

    public function test_index_returns_drivers(): void
    {
        $driver = Driver::factory()->create();

        $this->getJson('/api/v1/drivers')
            ->assertOk()
            ->assertJsonFragment(['email' => $driver->email]);
    }

    public function test_show_returns_a_driver(): void
    {
        $driver = Driver::factory()->create();

        $this->getJson("/api/v1/drivers/{$driver->id}")
            ->assertOk()
            ->assertJsonPath('id', $driver->id)
            ->assertJsonPath('email', $driver->email);
    }

    public function test_store_creates_driver_with_document(): void
    {
        Storage::fake('local');

        $response = $this->post('/api/v1/drivers', $this->validPayload(), [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('email', 'jane@example.com');
        $response->assertJsonPath('current_document.original_name', 'license.pdf');

        $driver = Driver::where('email', 'jane@example.com')->firstOrFail();
        $document = $driver->documents()->sole();

        Storage::disk('local')->assertExists($document->path);
    }

    public function test_store_validates_payload(): void
    {
        $this->post('/api/v1/drivers', [], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name', 'email', 'phone', 'pay_rate', 'document', 'status']);

        $this->assertDatabaseCount('drivers', 0);
    }

    public function test_update_adds_new_current_document(): void
    {
        Storage::fake('local');
        $oldPath = 'documents/old.pdf';
        Storage::disk('local')->put($oldPath, 'old');

        $driver = Driver::factory()->create();
        $oldDocument = $this->addDocument($driver, $oldPath, 'old.pdf');

        $payload = $this->validPayload([
            'email' => 'changed@example.com',
            'document' => UploadedFile::fake()->create('new.pdf', 20, 'application/pdf'),
        ]);

        $this->put("/api/v1/drivers/{$driver->id}", $payload, ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('email', 'changed@example.com')
            ->assertJsonPath('current_document.original_name', 'new.pdf');

        $driver->refresh();
        $oldDocument->refresh();

        $this->assertCount(2, $driver->documents()->get());
        $this->assertFalse($oldDocument->is_current);
    }

    public function test_destroy_soft_deletes_driver(): void
    {
        $driver = Driver::factory()->create();

        $this->deleteJson("/api/v1/drivers/{$driver->id}")->assertNoContent();

        $this->assertSoftDeleted($driver);
    }

    public function test_document_download_returns_file(): void
    {
        Storage::fake('local');
        $path = 'documents/certificate.pdf';
        Storage::disk('local')->put($path, 'pdf-bytes');

        $driver = Driver::factory()->create();
        $document = $this->addDocument($driver, $path, 'certificate.pdf');

        $this->get("/api/v1/drivers/{$driver->id}/documents/{$document->id}")
            ->assertOk()
            ->assertDownload('certificate.pdf');
    }

    public function test_document_download_rejects_document_from_another_driver(): void
    {
        Storage::fake('local');
        $path = 'documents/private.pdf';
        Storage::disk('local')->put($path, 'content');

        $owner = Driver::factory()->create();
        $other = Driver::factory()->create();
        $document = $this->addDocument($owner, $path, 'private.pdf');

        $this->get("/api/v1/drivers/{$other->id}/documents/{$document->id}")
            ->assertNotFound();
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
