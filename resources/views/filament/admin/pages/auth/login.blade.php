<x-filament-panels::page.simple>
    <form wire:submit.prevent="authenticate" class="bg-white shadow-lg rounded-lg p-10 w-full max-w-md">
        <h1 class="mb-8 text-2xl font-bold text-center text-yellow-700">WISDOM GOLD ADMIN LOGIN</h1>

        {{ $this->form }}

        @if ($errors->has('username'))
            <div class="mb-4 text-red-600 text-center text-sm">{{ $errors->first('username') }}</div>
        @endif

        <button type="submit" class="w-full bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600 font-bold shadow">
            เข้าสู่ระบบ
        </button>
    </form>
</x-filament-panels::page.simple>
