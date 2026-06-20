<x-tenant-page>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Pengaturan Toko</h1>

        @if (session('status') === 'settings-updated')
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-sm text-green-700">
                Pengaturan berhasil disimpan.
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('tenant.settings.update', ['subdomain' => $tenant->subdomain]) }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Identitas --}}
            <div class="bg-white rounded-xl shadow-sm border p-6 space-y-4">
                <h2 class="font-semibold text-gray-700">Identitas Toko</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subdomain</label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="subdomain" value="{{ old('subdomain', $tenant->subdomain) }}" required
                            class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                        <span class="text-sm text-gray-400">.kasiro.com</span>
                    </div>
                    <p class="mt-1 text-xs text-amber-600">Mengubah subdomain akan mengubah URL toko kamu.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
                    @if ($tenant->logo_path)
                        <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="Logo"
                            class="h-16 w-16 rounded-lg object-cover mb-2 border" />
                    @endif
                    <input type="file" name="logo" accept="image/*"
                        class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    <p class="mt-1 text-xs text-gray-400">Maks. 2MB. Kosongkan jika tidak ingin mengganti.</p>
                </div>
            </div>

            {{-- Layout --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="font-semibold text-gray-700 mb-3">Layout</h2>
                <div class="grid grid-cols-3 gap-3">
                    @foreach ($layouts as $l)
                        <label class="cursor-pointer">
                            <input type="radio" name="layout" value="{{ $l }}" class="sr-only peer"
                                @checked(old('layout', $tenant->layout()) === $l) />
                            <div class="border-2 rounded-lg p-3 text-center text-sm font-medium transition
                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700
                                border-gray-200 hover:border-gray-300 text-gray-600">
                                {{ ucfirst($l) }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Color Palette --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="font-semibold text-gray-700 mb-3">Warna Brand</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($palettes as $key => $vars)
                        <label class="cursor-pointer">
                            <input type="radio" name="color_palette" value="{{ $key }}" class="sr-only peer"
                                @checked(old('color_palette', $tenant->colorPalette()) === $key) />
                            <div class="border-2 rounded-lg p-3 transition peer-checked:border-indigo-500 border-gray-200 hover:border-gray-300">
                                <div class="flex gap-1.5 mb-2">
                                    <span class="h-5 w-5 rounded-full" style="background:{{ $vars['--brand-primary'] }}"></span>
                                    <span class="h-5 w-5 rounded-full" style="background:{{ $vars['--brand-accent'] }}"></span>
                                    <span class="h-5 w-5 rounded-full border" style="background:{{ $vars['--brand-bg'] }}"></span>
                                </div>
                                <p class="text-xs font-medium text-gray-600">{{ ucfirst($key) }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Theme --}}
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h2 class="font-semibold text-gray-700 mb-3">Tema</h2>
                <div class="flex gap-3">
                    @foreach ($themes as $t)
                        <label class="cursor-pointer flex-1">
                            <input type="radio" name="theme" value="{{ $t }}" class="sr-only peer"
                                @checked(old('theme', $tenant->theme_config['theme'] ?? 'light') === $t) />
                            <div class="border-2 rounded-lg py-2 text-center text-sm font-medium transition
                                peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700
                                border-gray-200 hover:border-gray-300 text-gray-600">
                                {{ ucfirst($t) }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                class="w-full py-2.5 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition">
                Simpan Pengaturan
            </button>
        </form>
    </div>
</x-tenant-page>
