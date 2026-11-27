<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Driver;
use Illuminate\Http\UploadedFile;

class DriverService
{
    /**
     * Create a driver and attach the provided document as current.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Driver
    {
        $document = $data['document'] ?? null;
        unset($data['document']);

        $driver = Driver::create($data);

        if ($document instanceof UploadedFile) {
            $this->addDocument($driver, $document);
        }

        return $driver;
    }

    /**
     * Update a driver, optionally replacing the current document.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Driver $driver, array $data): Driver
    {
        $document = $data['document'] ?? null;
        unset($data['document']);

        $driver->update($data);

        if ($document instanceof UploadedFile) {
            $driver->documents()->where('is_current', true)->update(['is_current' => false]);
            $this->addDocument($driver, $document);
        }

        return $driver;
    }

    /**
     * Soft delete a driver and their documents, keeping files for history.
     */
    public function delete(Driver $driver): void
    {
        $driver->documents()->delete();
        $driver->delete();
    }

    /**
     * Store the uploaded file and link it to the driver as the current document.
     */
    public function addDocument(Driver $driver, UploadedFile $file): Document
    {
        return $driver->documents()->create([
            'original_name' => $file->getClientOriginalName(),
            'path' => $file->store('documents'),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'is_current' => true,
        ]);
    }
}
