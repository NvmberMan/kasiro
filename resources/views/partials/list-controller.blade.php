{{--
    Kontroler daftar sisi-klien yang dapat dipakai ulang: memfilter (pencarian +
    satu filter kategori/peran) dan mengurutkan node yang sudah dirender server,
    tanpa request ulang ke server. Setiap item cukup punya atribut data-*:
      data-name   : teks untuk pencarian & sort nama
      data-search : (opsional) teks tambahan yang ikut dicari (mis. SKU)
      data-filter : nilai untuk filter tunggal (id kategori / nilai peran)
      data-<key>  : nilai untuk sort lain (mis. data-price, data-stock)
    Kontainer item diberi x-ref="list".
--}}
<script>
function listController(opts = {}) {
    return {
        search: '',
        sort: opts.defaultSort || '',
        filter: '',
        visibleCount: 0,

        init() {
            this.apply();
        },

        apply() {
            const list = this.$refs.list;
            if (!list) return;

            const items = Array.from(list.children);
            const q = this.search.trim().toLowerCase();
            let visible = 0;

            items.forEach((el) => {
                const hay = ((el.dataset.name || '') + ' ' + (el.dataset.search || '')).toLowerCase();
                const okSearch = !q || hay.includes(q);
                const okFilter = !this.filter || (el.dataset.filter || '') === this.filter;
                const show = okSearch && okFilter;
                el.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            this.visibleCount = visible;

            if (this.sort) {
                const [key, dir] = this.sort.split(':');
                const mult = dir === 'desc' ? -1 : 1;
                items.sort((a, b) => {
                    const av = a.dataset[key] ?? '';
                    const bv = b.dataset[key] ?? '';
                    const an = parseFloat(av);
                    const bn = parseFloat(bv);
                    const cmp = (!isNaN(an) && !isNaN(bn))
                        ? an - bn
                        : String(av).localeCompare(String(bv), 'id');
                    return cmp * mult;
                }).forEach((el) => list.appendChild(el));
            }
        },
    };
}
</script>
