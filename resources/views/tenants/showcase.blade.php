<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Galeri Showcase') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <p class="text-gray-600 mb-8 text-center">
                Lihat contoh tampilan toko. Klik <strong>Pakai Ini</strong> untuk membuat toko dengan template tersebut.
            </p>

            @if ($templates->isEmpty())
                <p class="text-center text-gray-500">Belum ada template di showcase.</p>
            @else
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($templates as $template)
                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden flex flex-col">
                            {{-- Preview image placeholder --}}
                            <div class="h-36 bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-400 text-sm">
                                @if ($template->preview_image)
                                    <img src="{{ asset('storage/'.$template->preview_image) }}"
                                         alt="{{ $template->name }}" class="h-full w-full object-cover">
                                @else
                                    Pratinjau
                                @endif
                            </div>

                            <div class="p-4 flex-1 flex flex-col">
                                <h3 class="font-semibold text-gray-900">{{ $template->name }}</h3>
                                @if ($template->description)
                                    <p class="mt-1 text-xs text-gray-500 flex-1">{{ $template->description }}</p>
                                @endif

                                {{-- Quick-create form --}}
                                <form method="POST" action="{{ route('tenants.store.showcase') }}"
                                      enctype="multipart/form-data" class="mt-4 space-y-3">
                                    @csrf
                                    <input type="hidden" name="template_id" value="{{ $template->id }}">

                                    <div>
                                        <x-text-input name="name" type="text" class="block w-full text-sm"
                                            placeholder="Nama toko kamu" required />
                                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                    </div>

                                    <div class="flex rounded-md shadow-sm">
                                        <x-text-input name="subdomain" type="text"
                                            class="block w-full rounded-r-none text-sm"
                                            placeholder="subdomain" required />
                                        <span class="inline-flex items-center rounded-r-md border border-l-0
                                                     border-gray-300 bg-gray-50 px-2 text-gray-500 text-xs">
                                            .{{ config('tenancy.central_domain') }}
                                        </span>
                                    </div>
                                    <x-input-error :messages="$errors->get('subdomain')" class="mt-1" />

                                    <x-primary-button class="w-full justify-center">
                                        {{ __('Pakai Ini') }}
                                    </x-primary-button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
