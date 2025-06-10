<x-guest-layout>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Phone -->
        <div>
            <x-input-label for="phone" :value="__('เบอร์โทรศัพท์')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required autofocus />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('รหัสผ่าน')" />
            <x-text-input id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm" name="remember">
                <span class="ml-2 text-sm text-gray-600">{{ __('จดจำฉันไว้') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('เข้าสู่ระบบ') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
