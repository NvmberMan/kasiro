<x-tenant-page>
<div x-data="posApp()" @keydown.escape.window="cartOpen = false"
     class="flex flex-col lg:flex-row gap-4 lg:h-full lg:overflow-hidden p-4 sm:p-6">

    {{-- Product grid --}}
    <div class="flex-1 lg:overflow-y-auto">

        @if (session('status') === 'checkout-success')
        <div class="mb-3 p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            Transaksi berhasil dicatat.
        </div>
        @endif

        @error('cart')
        <div class="mb-3 p-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg">
            {{ $message }}
        </div>
        @enderror

        {{-- Search + sort --}}
        <div class="flex flex-wrap gap-2 mb-4">
            <input type="search" x-model="search" placeholder="Cari produk..."
                   class="flex-1 min-w-[180px] border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select x-model="sort" @change="sortProducts()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="name:asc">Nama A-Z</option>
                <option value="name:desc">Nama Z-A</option>
                <option value="price:asc">Harga Terendah</option>
                <option value="price:desc">Harga Tertinggi</option>
                <option value="stock:desc">Stok Terbanyak</option>
            </select>
        </div>

        {{-- Category filter --}}
        @if ($categories->isNotEmpty())
        <div class="flex gap-2 flex-wrap mb-4">
            <button @click="filterCategory = null"
                    :class="filterCategory === null ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border'"
                    class="px-3 py-1 rounded-full text-xs font-medium transition">
                Semua
            </button>
            @foreach ($categories as $cat)
            <button @click="filterCategory = {{ $cat->id }}"
                    :class="filterCategory === {{ $cat->id }} ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 border'"
                    class="px-3 py-1 rounded-full text-xs font-medium transition">
                {{ $cat->name }}
            </button>
            @endforeach
        </div>
        @endif

        {{-- Products --}}
        @if ($products->isEmpty())
            <p class="text-gray-400 text-center py-12">Belum ada produk aktif.</p>
        @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3" x-ref="grid">
            @foreach ($products as $product)
            <button
                data-name="{{ mb_strtolower($product->name) }}"
                data-price="{{ $product->price }}"
                data-stock="{{ $product->stock }}"
                x-show="(filterCategory === null || filterCategory === {{ $product->category_id ?? 'null' }}) && nameMatches('{{ addslashes(mb_strtolower($product->name)) }}')"
                @click="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }}, {{ $product->stock }})"
                :disabled="{{ $product->stock }} === 0"
                class="text-left bg-white rounded-xl border-2 border-transparent hover:border-indigo-400 transition disabled:opacity-40 disabled:cursor-not-allowed shadow-sm overflow-hidden">
                @if ($product->image_path)
                    <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}"
                         class="w-full h-24 object-cover">
                @else
                    <div class="w-full h-24 bg-gray-100 flex items-center justify-center text-gray-300 text-xs">Foto</div>
                @endif
                <div class="p-3">
                <p class="font-medium text-sm text-gray-800 leading-tight mb-1 line-clamp-2">{{ $product->name }}</p>
                <p class="text-xs text-gray-500 mb-2">{{ $product->category?->name ?? '' }}</p>
                <p class="font-bold text-indigo-600 text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                <p class="text-xs mt-1 {{ $product->stock === 0 ? 'text-red-500' : 'text-gray-400' }}">
                    Stok: {{ $product->stock }}
                </p>
                </div>
            </button>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Mobile cart trigger (floating) --}}
    <button type="button" @click="cartOpen = true"
            class="lg:hidden fixed bottom-5 right-5 z-30 flex items-center gap-2 rounded-full bg-indigo-600 text-white shadow-lg px-5 py-3 active:scale-95 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <span class="text-sm font-semibold" x-text="'Rp ' + total.toLocaleString('id')"></span>
        <span x-show="cartCount > 0" class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 rounded-full bg-white text-indigo-700 text-xs font-bold" x-text="cartCount"></span>
    </button>

    {{-- Drawer overlay (mobile) --}}
    <div x-show="cartOpen" x-transition.opacity @click="cartOpen = false"
         class="lg:hidden fixed inset-0 bg-black/40 z-40" style="display:none"></div>

    {{-- Cart: right column on desktop, slide-in drawer on mobile --}}
    <div class="flex-shrink-0 flex flex-col bg-white border border-gray-200 overflow-hidden
                fixed inset-y-0 right-0 z-50 w-full max-w-sm shadow-2xl transition-transform duration-300
                lg:static lg:inset-auto lg:z-auto lg:w-80 lg:max-w-none lg:rounded-xl lg:shadow-sm lg:translate-x-0"
         :class="cartOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'">
        <div class="px-4 py-3 border-b font-semibold text-gray-700 flex items-center justify-between">
            <span>Keranjang</span>
            <button type="button" @click="cartOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600" aria-label="Tutup keranjang">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto divide-y">
            <template x-if="Object.keys(cart).length === 0">
                <p class="text-center text-gray-400 text-sm py-8">Keranjang kosong</p>
            </template>
            <template x-for="item in cartItems" :key="item.id">
                <div class="px-4 py-3 flex items-start gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate" x-text="item.name"></p>
                        <p class="text-xs text-indigo-600 mt-0.5" x-text="'Rp ' + item.price.toLocaleString('id')"></p>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button @click="decrement(item.id)"
                                class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 text-xs font-bold">−</button>
                        <span class="w-6 text-center text-sm font-medium" x-text="item.qty"></span>
                        <button @click="increment(item.id)"
                                class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 text-xs font-bold">+</button>
                    </div>
                </div>
            </template>
        </div>

        <div class="border-t px-4 py-4 space-y-3">
            <div class="flex justify-between text-sm font-semibold">
                <span>Total</span>
                <span class="text-indigo-700" x-text="'Rp ' + total.toLocaleString('id')"></span>
            </div>

            <div>
                <label class="text-xs text-gray-500">Uang Bayar</label>
                <input type="number" x-model="paid" @input="calcChange()"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mt-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                       min="0" step="1000" placeholder="0">
            </div>

            <div class="flex justify-between text-sm" x-show="paid > 0">
                <span class="text-gray-500">Kembalian</span>
                <span :class="change < 0 ? 'text-red-600 font-bold' : 'text-gray-700'" x-text="'Rp ' + Math.max(0, change).toLocaleString('id')"></span>
            </div>

            <form method="POST"
                  action="{{ route('tenant.pos.checkout', ['subdomain' => $tenant->subdomain]) }}"
                  @submit.prevent="submitCheckout($el)">
                @csrf
                <input type="hidden" name="cart" x-ref="cartInput">
                <input type="hidden" name="paid" x-ref="paidInput">
                <button type="submit"
                        :disabled="Object.keys(cart).length === 0 || paid < total"
                        class="w-full py-2.5 bg-indigo-600 text-white font-semibold text-sm rounded-lg hover:bg-indigo-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Bayar
                </button>
            </form>

            <button @click="clearCart()" x-show="Object.keys(cart).length > 0"
                    class="w-full py-1.5 text-xs text-gray-400 hover:text-red-500 transition">
                Kosongkan Keranjang
            </button>
        </div>
    </div>
</div>

<script>
function posApp() {
    return {
        cart: {},
        cartOpen: false,
        filterCategory: null,
        search: '',
        sort: 'name:asc',
        paid: 0,

        init() {
            this.sortProducts();
        },

        nameMatches(name) {
            return this.search === '' || name.includes(this.search.toLowerCase().trim());
        },

        sortProducts() {
            const grid = this.$refs.grid;
            if (!grid) return;
            const [key, dir] = this.sort.split(':');
            const mult = dir === 'desc' ? -1 : 1;
            Array.from(grid.children).sort((a, b) => {
                const av = a.dataset[key] ?? '';
                const bv = b.dataset[key] ?? '';
                const an = parseFloat(av);
                const bn = parseFloat(bv);
                const cmp = (!isNaN(an) && !isNaN(bn))
                    ? an - bn
                    : String(av).localeCompare(String(bv), 'id');
                return cmp * mult;
            }).forEach((el) => grid.appendChild(el));
        },

        get cartItems() {
            return Object.values(this.cart);
        },

        get total() {
            return this.cartItems.reduce((sum, i) => sum + i.price * i.qty, 0);
        },

        get cartCount() {
            return this.cartItems.reduce((sum, i) => sum + i.qty, 0);
        },

        get change() {
            return Number(this.paid) - this.total;
        },

        addToCart(id, name, price, stock) {
            if (this.cart[id]) {
                if (this.cart[id].qty < stock) this.cart[id].qty++;
            } else {
                this.cart[id] = { id, name, price, qty: 1, stock };
            }
        },

        increment(id) {
            if (this.cart[id] && this.cart[id].qty < this.cart[id].stock) {
                this.cart[id].qty++;
            }
        },

        decrement(id) {
            if (!this.cart[id]) return;
            if (this.cart[id].qty > 1) {
                this.cart[id].qty--;
            } else {
                delete this.cart[id];
            }
        },

        clearCart() {
            this.cart = {};
            this.paid = 0;
        },

        calcChange() {},

        submitCheckout(form) {
            const items = this.cartItems.map(i => ({ product_id: i.id, qty: i.qty }));
            this.$refs.cartInput.value = JSON.stringify(items);
            this.$refs.paidInput.value = this.paid;
            form.submit();
        },
    };
}
</script>
</x-tenant-page>
