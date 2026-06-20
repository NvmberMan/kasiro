<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Toko — Custom') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('tenants.store.custom') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Branding fields --}}
                    @include('tenants.partials.branding-fields')

                    {{-- Layout --}}
                    <div class="mt-6">
                        <x-input-label :value="__('Layout')" />
                        <div class="mt-2 grid grid-cols-3 gap-3">
                            @foreach ($layouts as $key => $layout)
                                <label class="cursor-pointer">
                                    <input type="radio" name="layout" value="{{ $key }}"
                                        class="sr-only peer"
                                        {{ old('layout', $defaults['layout']) === $key ? 'checked' : '' }}>
                                    <span class="block rounded-lg border-2 border-gray-200 p-3 text-center text-sm font-medium
                                                 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-gray-400 transition">
                                        {{ $layout['label'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('layout')" class="mt-2" />
                    </div>

                    {{-- Theme --}}
                    <div class="mt-4">
                        <x-input-label :value="__('Tema')" />
                        <div class="mt-2 grid grid-cols-3 gap-3">
                            @foreach ($themes as $key => $theme)
                                <label class="cursor-pointer">
                                    <input type="radio" name="theme" value="{{ $key }}"
                                        class="sr-only peer"
                                        {{ old('theme', $defaults['theme']) === $key ? 'checked' : '' }}>
                                    <span class="block rounded-lg border-2 border-gray-200 p-3 text-center text-sm font-medium
                                                 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-gray-400 transition">
                                        {{ $theme['label'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('theme')" class="mt-2" />
                    </div>

                    {{-- Color palette --}}
                    <div class="mt-4">
                        <x-input-label :value="__('Palet Warna')" />
                        <div class="mt-2 grid grid-cols-5 gap-3">
                            @foreach ($palettes as $key => $vars)
                                <label class="cursor-pointer" title="{{ ucfirst($key) }}">
                                    <input type="radio" name="color_palette" value="{{ $key }}"
                                        class="sr-only peer"
                                        {{ old('color_palette', $defaults['color_palette']) === $key ? 'checked' : '' }}>
                                    <span class="block h-10 rounded-lg border-2 border-gray-200
                                                 peer-checked:border-gray-900 hover:border-gray-400 transition"
                                          style="background-color: {{ $vars['--brand-primary'] }};">
                                    </span>
                                    <span class="mt-1 block text-center text-xs text-gray-600">{{ ucfirst($key) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('color_palette')" class="mt-2" />
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-4">
                        <a href="{{ route('tenants.choose') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            {{ __('Kembali') }}
                        </a>
                        <x-primary-button>{{ __('Buat Toko') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
