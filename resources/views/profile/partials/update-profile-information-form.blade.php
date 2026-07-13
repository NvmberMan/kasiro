<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    {{-- Avatar Upload --}}
    <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data"
          x-data="avatarUpload()" class="mt-6">
        @csrf

        <div class="flex items-center gap-5">
            {{-- Current / Preview --}}
            <div class="relative shrink-0">
                <img data-clarity-mask="true" x-bind:src="preview" alt="Avatar"
                     class="h-20 w-20 rounded-full object-cover ring-2 ring-slate-200">
                <label for="avatar-input"
                       class="absolute bottom-0 right-0 flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-slate-700 text-white hover:bg-slate-900 transition">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-1.414.94l-3.414.94.94-3.414A4 4 0 019 13z"/>
                    </svg>
                </label>
                <input id="avatar-input" name="avatar" type="file" accept="image/*" class="hidden"
                       x-on:change="onFileChange($event)">
            </div>

            <div>
                <p class="text-sm font-medium text-slate-700">Foto Profil</p>
                <p class="mt-0.5 text-xs text-slate-500">JPG, PNG, atau WebP · maks 2 MB</p>
                <button type="submit" x-show="changed" x-cloak
                        class="mt-2 rounded-md bg-slate-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-900 transition">
                    Simpan Foto
                </button>

                @if (session('status') === 'avatar-updated')
                    <p x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)"
                       class="mt-2 text-xs font-medium text-green-600">Foto diperbarui.</p>
                @endif
            </div>
        </div>

        <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
    </form>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
function avatarUpload() {
    return {
        preview: @js($user->avatar) || null,
        changed: false,
        onFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.preview = URL.createObjectURL(file);
            this.changed = true;
        },
    }
}
</script>
