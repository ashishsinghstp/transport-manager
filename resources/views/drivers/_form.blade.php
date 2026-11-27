<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="full_name" class="block text-sm font-medium text-gray-700">
            Full Name <span class="text-red-600">*</span>
        </label>
        <input type="text"
               name="full_name"
               id="full_name"
               value="{{ old('full_name', $driver?->full_name) }}"
               required
               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        @error('full_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
            Email Address <span class="text-red-600">*</span>
        </label>
        <input type="email"
               name="email"
               id="email"
               value="{{ old('email', $driver?->email) }}"
               required
               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700">
            Phone Number <span class="text-red-600">*</span>
        </label>
        <input type="text"
               name="phone"
               id="phone"
               value="{{ old('phone', $driver?->phone) }}"
               required
               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="pay_rate" class="block text-sm font-medium text-gray-700">
            Pay Rate <span class="text-red-600">*</span>
        </label>
        <input type="number"
               name="pay_rate"
               id="pay_rate"
               value="{{ old('pay_rate', $driver?->pay_rate) }}"
               min="0"
               step="0.01"
               required
               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        @error('pay_rate')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="company_address" class="block text-sm font-medium text-gray-700">Company Address</label>
        <textarea name="company_address"
                  id="company_address"
                  rows="3"
                  class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('company_address', $driver?->company_address) }}</textarea>
        @error('company_address')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="hst_number" class="block text-sm font-medium text-gray-700">HST Number</label>
        <input type="text"
               name="hst_number"
               id="hst_number"
               value="{{ old('hst_number', $driver?->hst_number) }}"
               class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        @error('hst_number')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="document" class="block text-sm font-medium text-gray-700">
            Documents <span class="text-red-600">*</span>
        </label>
        @if ($driver?->currentDocument)
            <p class="mb-1 text-sm text-gray-600">
                Current:
                <a class="text-indigo-600 hover:underline"
                   href="{{ route('drivers.documents.download', [$driver, $driver->currentDocument]) }}">view document</a>
            </p>
        @endif

        @if ($driver && $driver->documents->isNotEmpty())
            <div class="mb-3 overflow-x-auto rounded border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-gray-50 text-left uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-3 py-2">File</th>
                            <th class="px-3 py-2">Uploaded</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($driver->documents as $storedDocument)
                            <tr>
                                <td class="px-3 py-2">
                                    {{ $storedDocument->original_name }}
                                    @if ($storedDocument->is_current)
                                        <span class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-medium text-green-700">Current</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-gray-500">{{ $storedDocument->created_at->format('M j, Y g:i A') }}</td>
                                <td class="px-3 py-2 text-right">
                                    <a class="text-indigo-600 hover:underline"
                                       href="{{ route('drivers.documents.download', [$driver, $storedDocument]) }}">Download</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mb-1 text-xs text-gray-500">Uploading a new file adds a new document; previous documents are kept.</p>
        @endif

        <input type="file"
               name="document"
               id="document"
               accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
               {{ $driver ? '' : 'required' }}
               class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
        <p class="mt-1 text-xs text-gray-500">PDF, Word, Excel, or image. Max 10 MB.</p>
        @error('document')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">
            Status <span class="text-red-600">*</span>
        </label>
        <select name="status"
                id="status"
                required
                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="active" @selected(old('status', $driver?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $driver?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
            {{ $driver ? 'Update Driver' : 'Create Driver' }}
        </button>
        <a href="{{ route('drivers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
    </div>
</form>
