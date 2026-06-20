<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Toko — Pakai Template') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('tenants.store.template') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Template selection --}}
                    <div class="mb-6">
                        <x-input-label :value="__('Pilih Template')" />
                        @if ($templates->isEmpty())
                            <p class="mt-2 text-sm text-gray-500">Belum ada template tersedia.</p>
                        @else
                            <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                @foreach ($templates as $template)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="template_id" value="{{ $template->id }}"
                                            class="sr-only peer"
                                            {{ old('template_id') == $template->id ? 'checked' : '' }} required>
                                        <span class="block rounded-xl border-2 border-gray-200 p-4
                                                     peer-checked:border-indigo-500 peer-checked:bg-indigo-50
                                                     hover:border-gray-400 transition">
                                            <span class="block font-semibold text-gray-900">{{ $template->name }}</span>
                                            @if ($template->description)
                                                <span class="mt-1 block text-xs text-gray-500">{{ $template->description }}</span>
                                            @endif
                                            <span class="mt-2 block text-xs text-indigo-600">
                                                {{ ucfirst($template->default_config['layout'] ?? 'modern') }}
                                                · {{ ucfirst($template->default_config['color_palette'] ?? 'default') }}
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('template_id')" class="mt-2" />
                    </div>

                    {{-- Branding fields --}}
                    @include('tenants.partials.branding-fields')

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
