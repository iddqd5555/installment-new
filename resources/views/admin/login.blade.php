<x-filament::layouts.auth>
    <form wire:submit.prevent="authenticate" class="space-y-8">
        <div>
            <label for="prefix" class="block font-medium text-sm text-gray-700">Prefix</label>
            <input wire:model.lazy="prefix" id="prefix" class="block mt-1 w-full border-gray-300 rounded-md" autofocus />
            @error('prefix') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div>
            <label for="username" class="block font-medium text-sm text-gray-700">Username</label>
            <input wire:model.lazy="username" id="username" class="block mt-1 w-full border-gray-300 rounded-md" />
            @error('username') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div>
            <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
            <input wire:model.lazy="password" id="password" type="password" class="block mt-1 w-full border-gray-300 rounded-md" />
            @error('password') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="w-full bg-primary text-white py-2 px-4 rounded">เข้าสู่ระบบ</button>
    </form>
</x-filament::layouts.auth>
