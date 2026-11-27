@extends('layouts.app')

@section('title', 'Drivers — Transport Manager')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Driver Manager</h1>
        <a href="{{ route('drivers.create') }}"
           class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
            Add Driver
        </a>
    </div>

    <div class="overflow-x-auto rounded border border-gray-200 bg-white shadow">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-3">Full Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Pay Rate</th>
                    <th class="px-4 py-3">Company Address</th>
                    <th class="px-4 py-3">HST Number</th>
                    <th class="px-4 py-3">Document</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($drivers as $driver)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $driver->full_name }}</td>
                        <td class="px-4 py-3">{{ $driver->email }}</td>
                        <td class="px-4 py-3">{{ $driver->phone }}</td>
                        <td class="px-4 py-3">${{ number_format((float) $driver->pay_rate, 2) }}</td>
                        <td class="px-4 py-3">{{ $driver->company_address ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $driver->hst_number ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if ($driver->currentDocument)
                                <a class="text-indigo-600 hover:underline"
                                   href="{{ route('drivers.documents.download', [$driver, $driver->currentDocument]) }}">View</a>
                                @if ($driver->documents_count > 1)
                                    <span class="text-xs text-gray-500">({{ $driver->documents_count }} total)</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($driver->status === 'active')
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a class="text-indigo-600 hover:underline"
                               href="{{ route('drivers.edit', $driver) }}">Edit</a>
                            <form class="ml-3 inline"
                                  action="{{ route('drivers.destroy', $driver) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete {{ $driver->full_name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-8 text-center text-gray-500" colspan="9">
                            No drivers yet.
                            <a class="text-indigo-600 hover:underline" href="{{ route('drivers.create') }}">Add the first one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
