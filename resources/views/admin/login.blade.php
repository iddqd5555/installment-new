{{-- resources/views/admin/login.blade.php --}}
<x-filament-panels::layouts.auth>
    <x-filament-panels::form wire:submit="authenticate" class="space-y-8">
        <x-filament-panels::input.wrapper>
            <x-filament-panels::input.label for="prefix" :value="'Prefix'" />
            <x-filament-panels::input.text wire:model.lazy="prefix" id="prefix" autofocus />
            <x-filament-panels::input.error for="prefix" />
        </x-filament-panels::input.wrapper>
        <x-filament-panels::input.wrapper>
            <x-filament-panels::input.label for="username" :value="'Username'" />
            <x-filament-panels::input.text wire:model.lazy="username" id="username" />
            <x-filament-panels::input.error for="username" />
        </x-filament-panels::input.wrapper>
        <x-filament-panels::input.wrapper>
            <x-filament-panels::input.label for="password" :value="'Password'" />
            <x-filament-panels::input.text wire:model.lazy="password" id="password" type="password" />
            <x-filament-panels::input.error for="password" />
        </x-filament-panels::input.wrapper>
        <x-filament-panels::button type="submit" class="w-full">เข้าสู่ระบบ</x-filament-panels::button>
    </x-filament-panels::form>
</x-filament-panels::layouts.auth>
