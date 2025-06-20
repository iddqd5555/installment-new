{{-- resources/views/admin/login.blade.php --}}
<x-filament::layouts.auth>
    <x-filament-panels::form wire:submit="authenticate" class="space-y-8">
        <x-filament::input.wrapper>
            <x-filament::input.label for="prefix" :value="'Prefix'" />
            <x-filament::input.text wire:model.lazy="prefix" id="prefix" autofocus />
            <x-filament::input.error for="prefix" />
        </x-filament::input.wrapper>
        <x-filament::input.wrapper>
            <x-filament::input.label for="username" :value="'Username'" />
            <x-filament::input.text wire:model.lazy="username" id="username" />
            <x-filament::input.error for="username" />
        </x-filament::input.wrapper>
        <x-filament::input.wrapper>
            <x-filament::input.label for="password" :value="'Password'" />
            <x-filament::input.text wire:model.lazy="password" id="password" type="password" />
            <x-filament::input.error for="password" />
        </x-filament::input.wrapper>
        <x-filament::button type="submit" class="w-full">เข้าสู่ระบบ</x-filament::button>
    </x-filament-panels::form>
</x-filament::layouts.auth>
