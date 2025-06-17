<div>
    <form wire:submit.prevent="save">
        <div>
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <div>
            <label for="file" class="block text-sm font-medium text-gray-700">Upload JSON File</label>
            <input id="file" wire:model="file" type="file" class="mt-1 block w-full">
            @error('file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mt-4">
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Upload and Process
            </button>
        </div>
    </form>
</div>
