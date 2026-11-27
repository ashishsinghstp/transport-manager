<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Models\Document;
use App\Models\Driver;
use App\Services\DriverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DriverController extends Controller
{
    public function __construct(private DriverService $driverService) {}

    /**
     * List all drivers.
     */
    public function index(): View
    {
        return view('drivers.index', [
            'drivers' => Driver::with('currentDocument')
                ->withCount('documents')
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('drivers.create');
    }

    /**
     * Persist a new driver.
     */
    public function store(StoreDriverRequest $request): RedirectResponse
    {
        $this->driverService->create($request->validated());

        return redirect()->route('drivers.index')->with('success', 'Driver created successfully.');
    }

    /**
     * Show the edit form.
     */
    public function edit(Driver $driver): View
    {
        $driver->load(['documents' => fn ($query) => $query->latest()]);

        return view('drivers.edit', ['driver' => $driver]);
    }

    /**
     * Update an existing driver.
     */
    public function update(UpdateDriverRequest $request, Driver $driver): RedirectResponse
    {
        $this->driverService->update($driver, $request->validated());

        return redirect()->route('drivers.index')->with('success', 'Driver updated successfully.');
    }

    /**
     * Soft delete a driver and their documents, keeping the files for history.
     */
    public function destroy(Driver $driver): RedirectResponse
    {
        $this->driverService->delete($driver);

        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully.');
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
