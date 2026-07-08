<x-tenant-page>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">{{ __('Manajemen Karyawan') }}</h1>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Invite form --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4">{{ __('Undang Karyawan Baru') }}</h2>
            <form method="POST" data-loading action="{{ route('tenant.invitations.store', ['subdomain' => $tenant->subdomain]) }}" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="email" name="email" placeholder="{{ __('Email karyawan') }}" required
                    value="{{ old('email') }}"
                    class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <select name="role" class="min-w-[120px] rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="cashier" @selected(old('role','cashier')==='cashier')>{{ __('Kasir') }}</option>
                    <option value="manager" @selected(old('role')==='manager')>{{ __('Manager') }}</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">{{ __('Kirim Undangan') }}</button>
            </form>
        </div>

        {{-- Members list --}}
        <div class="bg-white rounded-xl shadow-sm border mb-6">
            <div class="px-6 py-4 border-b">
                <h2 class="font-semibold">{{ __('Anggota Aktif') }}</h2>
            </div>
            <ul class="divide-y">
                @forelse ($members as $member)
                    @php $role = $member->pivot->role->value; $status = $member->pivot->status; @endphp
                    <li class="px-6 py-4 flex items-center justify-between gap-3 flex-wrap">
                        <div class="min-w-0">
                            <p class="font-medium text-sm truncate">{{ $member->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $member->email }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span @class([
                                'px-2 py-0.5 text-xs rounded-full font-medium',
                                'bg-purple-100 text-purple-700' => $role === 'owner',
                                'bg-blue-100 text-blue-700'   => $role === 'manager',
                                'bg-gray-100 text-gray-700'   => $role === 'cashier',
                                'opacity-50'                   => $status === 'revoked',
                            ])>{{ ucfirst($role) }}</span>

                            @if ($status === 'revoked')
                                <span class="text-xs text-red-500">{{ __('Dicabut') }}</span>
                            @elseif ($role !== 'owner')
                                <form method="POST" action="{{ route('tenant.employees.update', ['subdomain' => $tenant->subdomain, 'user' => $member->id]) }}">
                                    @csrf @method('PATCH')
                                    <select name="role" onchange="this.form.submit()" class="min-w-[100px] text-xs border rounded px-2 py-1">
                                        <option value="cashier" @selected($role==='cashier')>{{ __('Kasir') }}</option>
                                        <option value="manager" @selected($role==='manager')>{{ __('Manager') }}</option>
                                    </select>
                                </form>
                                <form method="POST" action="{{ route('tenant.employees.destroy', ['subdomain' => $tenant->subdomain, 'user' => $member->id]) }}"
                                    data-confirm="{{ __('Akses :name ke toko ini akan dihapus.', ['name' => $member->name]) }}"
                                    data-confirm-title="{{ __('Hapus Akses?') }}"
                                    data-confirm-action="{{ __('Ya, Hapus') }}"
                                    data-confirm-type="danger">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-600 hover:underline">{{ __('Hapus') }}</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-gray-400">{{ __('Belum ada anggota.') }}</li>
                @endforelse
            </ul>
        </div>

        {{-- Pending invitations --}}
        @if ($pendingInvitations->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h2 class="font-semibold">{{ __('Undangan Tertunda') }}</h2>
                </div>
                <ul class="divide-y">
                    @foreach ($pendingInvitations as $inv)
                        <li class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium">{{ $inv->email }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ ucfirst($inv->role) }} · {{ __('kedaluwarsa') }} {{ $inv->expires_at->diffForHumans() }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('tenant.invitations.destroy', ['subdomain' => $tenant->subdomain, 'invitation' => $inv->id]) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">{{ __('Batalkan') }}</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-tenant-page>
