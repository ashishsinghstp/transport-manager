<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Http\Resources\DriverResource;
use App\Models\Document;
use App\Models\Driver;
use App\Services\DriverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriverController extends Controller
{
    public function __construct(private DriverService $driverService) {}

    /**
     * List all drivers.
     */
    public function index(): AnonymousResourceCollection
    {
        return DriverResource::collection(
            Driver::with('currentDocument')
                ->withCount('documents')
                ->latest()
                ->get()
        );
    }

    /**
     * Show a single driver.
     */
    public function show(Driver $driver): DriverResource
    {
        return DriverResource::make(
            $driver->load('currentDocument')->loadCount('documents')
        );
    }

    /**
     * Persist a new driver.
     */
    public function store(StoreDriverRequest $request): JsonResponse
    {
        $driver = $this->driverService->create($request->validated());

        return DriverResource::make(
            $driver->load('currentDocument')->loadCount('documents')
        )->response()->setStatusCode(201);
    }

    /**
     * Update an existing driver.
     */
    public function update(UpdateDriverRequest $request, Driver $driver): DriverResource
    {
        $this->driverService->update($driver, $request->validated());

        return DriverResource::make(
            $driver->load('currentDocument')->loadCount('documents')
        );
    }

    /**
     * Soft delete a driver and their documents.
     */
    public function destroy(Driver $driver): Response
    {
        $this->driverService->delete($driver);

        return response()->noContent();
    }

    /**
     * Stream a stored document as a download.
     */
    public function document(Driver $driver, Document $document): StreamedResponse
    {
        abort_unless(
            $document->documentable_type === Driver::class
                && $document->documentable_id === $driver->id,
            404
        );

        abort_unless(Storage::exists($document->path), 404);

        return Storage::download($document->path, $document->original_name);
    }
}
