<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <form wire:submit.prevent="authenticate" class="bg-white shadow-lg rounded-lg p-10 w-full max-w-md">
        <h1 class="mb-8 text-2xl font-bold text-center text-yellow-700">WISDOM GOLD ADMIN LOGIN</h1>
        <div class="mb-5">
            <label for="prefix" class="block font-medium text-gray-700">Prefix</label>
            <input wire:model.lazy="prefix" id="prefix" class="block mt-1 w-full border-gray-300 rounded bg-blue-50 px-3 py-2" autofocus />
            @error('prefix') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div class="mb-5">
            <label for="username" class="block font-medium text-gray-700">Username</label>
            <input wire:model.lazy="username" id="username" class="block mt-1 w-full border-gray-300 rounded bg-blue-50 px-3 py-2" />
            @error('username') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        <div class="mb-8">
            <label for="password" class="block font-medium text-gray-700">Password</label>
            <input wire:model.lazy="password" id="password" type="password" class="block mt-1 w-full border-gray-300 rounded bg-blue-50 px-3 py-2" />
            @error('password') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>
        @if ($errors->has('username'))
            <div class="mb-4 text-red-600 text-center text-sm">{{ $errors->first('username') }}</div>
        @endif
        <button type="submit" class="w-full bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600 font-bold shadow">เข้าสู่ระบบ</button>
    </form>
</div>
