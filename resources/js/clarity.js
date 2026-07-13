import Clarity from '@microsoft/clarity';

/**
 * Microsoft Clarity — session recording & heatmap.
 *
 * Semua nilai datang dari meta tag yang dirender partials/clarity.blade.php.
 * Kalau meta `clarity-project-id` tidak ada (Clarity dimatikan atau ID belum
 * diisi di .env), fungsi ini tidak melakukan apa pun.
 */
function meta(name) {
    return document.querySelector(`meta[name="${name}"]`)?.content || null;
}

export function initClarity() {
    const projectId = meta('clarity-project-id');
    if (!projectId) return;

    Clarity.init(projectId);

    // Hanya ID yang dikirim, dan Clarity meng-hash-nya di sisi klien sebelum
    // keluar. friendlyName sengaja TIDAK diisi: argumen itu tidak di-hash, jadi
    // nama asli user akan tampil apa adanya di dashboard.
    const userId = meta('clarity-user-id');
    if (userId) Clarity.identify(userId);

    // Tag tenant supaya rekaman bisa difilter per toko di dashboard Clarity.
    const tenant = meta('clarity-tenant');
    if (tenant) Clarity.setTag('tenant', tenant);
}
