import './bootstrap';

import Alpine from 'alpinejs';

// ─── Top page-loading bar ───────────────────────────────────────────────────
(function () {
    const bar = document.createElement('div');
    Object.assign(bar.style, {
        position: 'fixed', top: '0', left: '0', height: '3px', width: '0',
        zIndex: '99999', pointerEvents: 'none', opacity: '0',
        transition: 'opacity .2s',
    });

    document.addEventListener('DOMContentLoaded', () => {
        // Tenant pages define --brand-primary; studio/landing do not
        const isTenant = !!getComputedStyle(document.documentElement)
            .getPropertyValue('--brand-primary').trim();

        if (isTenant) {
            // White bar + dark underline — visible on any brand-colored header
            bar.style.background = 'rgba(255,255,255,0.95)';
            bar.style.boxShadow = '0 1px 0 rgba(0,0,0,0.18), 0 0 8px 1px rgba(255,255,255,0.45)';
        } else {
            // Blue-600 (#2563eb) bar + white glow — matches palette used in studio & landing
            bar.style.background = '#2563eb';
            bar.style.boxShadow = '0 0 8px 3px rgba(255,255,255,0.7), 0 1px 2px rgba(0,0,0,0.08)';
        }

        document.body.prepend(bar);
    });

    function start() {
        bar.style.transition = 'width 0s, opacity .15s';
        bar.style.width = '0%';
        bar.style.opacity = '1';
        bar.getBoundingClientRect(); // force reflow
        // Fast start, then decelerates — page unloads before reaching 100%
        bar.style.transition = 'width 6s cubic-bezier(.08,.6,.25,1), opacity .15s';
        bar.style.width = '100%';
    }

    // Snap the bar back to hidden. start() intentionally never completes (the
    // page unloads first), so the bar is left visible at ~full width; on a
    // back/forward bfcache restore that stale bar would otherwise reappear.
    function reset() {
        bar.style.transition = 'none';
        bar.style.opacity = '0';
        bar.style.width = '0%';
    }

    // Fires on every show, including bfcache restores (event.persisted) that skip
    // DOMContentLoaded — so returning to a page never shows the previous nav's bar.
    window.addEventListener('pageshow', reset);

    // Links that cause a real navigation
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') ||
            a.target === '_blank' || a.hasAttribute('download') ||
            e.ctrlKey || e.metaKey || e.shiftKey) return;
        start();
    }, true);

    // Native form submissions (Alpine's @submit.prevent sets defaultPrevented before bubbling).
    // Forms that render their own progress UI opt out with data-no-progress, so the
    // top bar doesn't stack on top of a full-screen loading overlay (e.g. "Buat Toko").
    document.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;
        if (e.target instanceof HTMLElement && e.target.hasAttribute('data-no-progress')) return;
        start();
    });
})();
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Chart.defaults.font.family =
    getComputedStyle(document.documentElement).getPropertyValue('--brand-font').trim() ||
    "'Wix Madefor Text', system-ui, sans-serif";
Chart.defaults.color = '#94a3b8';

/**
 * Resolve the active brand colour (falls back to the app blue) and build an
 * rgba() string for translucent fills.
 */
function brandColor() {
    const c = getComputedStyle(document.documentElement).getPropertyValue('--brand-primary').trim();
    return c || '#2563eb';
}

function withAlpha(hex, alpha) {
    const m = hex.replace('#', '');
    if (m.length !== 6) return hex;
    const r = parseInt(m.slice(0, 2), 16);
    const g = parseInt(m.slice(2, 4), 16);
    const b = parseInt(m.slice(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function buildChart(canvas) {
    if (canvas.__chartInit) return;
    canvas.__chartInit = true;

    const type = canvas.dataset.chart || 'line';
    const horizontal = canvas.dataset.horizontal === '1';
    const fill = canvas.dataset.fill === '1';
    const format = canvas.dataset.format || 'number';
    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const values = JSON.parse(canvas.dataset.values || '[]');
    const color = brandColor();

    const fmt = (v) =>
        format === 'currency'
            ? 'Rp ' + Number(v).toLocaleString('id-ID')
            : Number(v).toLocaleString('id-ID');

    const valueAxis = horizontal ? 'x' : 'y';

    new Chart(canvas, {
        type,
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: color,
                backgroundColor: type === 'line' ? withAlpha(color, 0.14) : color,
                fill,
                tension: 0.38,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 4,
                pointBackgroundColor: color,
                borderRadius: type === 'bar' ? 6 : 0,
                maxBarThickness: 26,
            }],
        },
        options: {
            indexAxis: horizontal ? 'y' : 'x',
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 900, easing: 'easeOutQuart' },
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: { label: (ctx) => fmt(ctx.parsed[valueAxis]) },
                },
            },
            scales: {
                x: {
                    grid: { display: horizontal },
                    border: { display: false },
                    ticks: { autoSkip: true, maxRotation: 0, maxTicksLimit: 7, font: { size: 10 } },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(15,23,42,0.06)' },
                    border: { display: false },
                    ticks: {
                        font: { size: 10 },
                        callback: (v) =>
                            format === 'currency' && !horizontal && Number(v) >= 1000
                                ? (Number(v) / 1000) + 'rb'
                                : v,
                    },
                },
            },
        },
    });
}

function initCharts(root = document) {
    root.querySelectorAll('canvas[data-chart]').forEach(buildChart);
}

window.initCharts = initCharts;

/**
 * Forms marked `data-loading` show a spinner on their submit button while the
 * request is in flight (native validation gates the submit event, so the
 * spinner only appears on a real submission).
 */
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-loading')) return;
    const btn = form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
    if (!btn || btn.dataset.loading) return;
    btn.dataset.loading = '1';
    btn.disabled = true;
    btn.innerHTML =
        '<span class="inline-flex items-center justify-center gap-2">' +
        '<svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>' +
        'Memproses…</span>';
}, true);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initCharts());
} else {
    initCharts();
}

Alpine.start();
