{{-- resources/views/admin/login.blade.php --}}
<form wire:submit.prevent="authenticate" class="max-w-md mx-auto mt-16 space-y-6 p-8 bg-white rounded shadow">
    <div>
        <label for="prefix" class="block font-medium">Prefix</label>
        <input wire:model.lazy="prefix" id="prefix" class="block mt-1 w-full border-gray-300 rounded" autofocus />
        @error('prefix') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="username" class="block font-medium">Username</label>
        <input wire:model.lazy="username" id="username" class="block mt-1 w-full border-gray-300 rounded" />
        @error('username') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <div>
        <label for="password" class="block font-medium">Password</label>
        <input wire:model.lazy="password" id="password" type="password" class="block mt-1 w-full border-gray-300 rounded" />
        @error('password') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
    <button type="submit" class="w-full bg-yellow-500 text-white py-2 px-4 rounded mt-6">เข้าสู่ระบบ</button>
</form>
