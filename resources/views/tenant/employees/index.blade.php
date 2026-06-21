<x-tenant-page>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Manajemen Karyawan</h1>
        </div>

        @if (session('invitation_link'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm font-medium text-green-800 mb-2">Undangan berhasil dibuat. Bagikan link berikut:</p>
                <code class="block text-xs bg-white border border-green-300 rounded px-3 py-2 break-all">
                    {{ session('invitation_link') }}
                </code>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Invite form --}}
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4">Undang Karyawan Baru</h2>
            <form method="POST" action="{{ route('tenant.invitations.store', ['subdomain' => $tenant->subdomain]) }}" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="email" name="email" placeholder="Email karyawan" required
                    value="{{ old('email') }}"
                    class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                <select name="role" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="cashier" @selected(old('role','cashier')==='cashier')>Kasir</option>
                    <option value="manager" @selected(old('role')==='manager')>Manager</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                    Kirim Undangan
                </button>
            </form>
        </div>

        {{-- Members list --}}
        <div class="bg-white rounded-xl shadow-sm border mb-6">
            <div class="px-6 py-4 border-b">
                <h2 class="font-semibold">Anggota Aktif</h2>
            </div>
            <ul class="divide-y">
                @forelse ($members as $member)
                    @php $role = $member->pivot->role->value; $status = $member->pivot->status; @endphp
                    <li class="px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm">{{ $member->name }}</p>
                            <p class="text-xs text-gray-500">{{ $member->email }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span @class([
                                'px-2 py-0.5 text-xs rounded-full font-medium',
                                'bg-purple-100 text-purple-700' => $role === 'owner',
                                'bg-blue-100 text-blue-700'   => $role === 'manager',
                                'bg-gray-100 text-gray-700'   => $role === 'cashier',
                                'opacity-50'                   => $status === 'revoked',
                            ])>{{ ucfirst($role) }}</span>

                            @if ($status === 'revoked')
                                <span class="text-xs text-red-500">Dicabut</span>
                            @elseif ($role !== 'owner')
                                <form method="POST" action="{{ route('tenant.employees.update', ['subdomain' => $tenant->subdomain, 'user' => $member->id]) }}">
                                    @csrf @method('PATCH')
                                    <select name="role" onchange="this.form.submit()" class="text-xs border rounded px-2 py-1">
                                        <option value="cashier" @selected($role==='cashier')>Kasir</option>
                                        <option value="manager" @selected($role==='manager')>Manager</option>
                                    </select>
                                </form>
                                <form method="POST" action="{{ route('tenant.employees.destroy', ['subdomain' => $tenant->subdomain, 'user' => $member->id]) }}"
                                    data-confirm="Akses {{ $member->name }} ke toko ini akan dicabut."
                                    data-confirm-title="Cabut Akses?"
                                    data-confirm-action="Ya, Cabut"
                                    data-confirm-type="danger">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-600 hover:underline">Cabut</button>
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-8 text-center text-sm text-gray-400">Belum ada anggota.</li>
                @endforelse
            </ul>
        </div>

        {{-- Pending invitations --}}
        @if ($pendingInvitations->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border">
                <div class="px-6 py-4 border-b">
                    <h2 class="font-semibold">Undangan Tertunda</h2>
                </div>
                <ul class="divide-y">
                    @foreach ($pendingInvitations as $inv)
                        <li class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium">{{ $inv->email }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ ucfirst($inv->role) }} · kedaluwarsa {{ $inv->expires_at->diffForHumans() }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('tenant.invitations.destroy', ['subdomain' => $tenant->subdomain, 'invitation' => $inv->id]) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">Batalkan</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-tenant-page>
