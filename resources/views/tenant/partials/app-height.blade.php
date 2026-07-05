{{-- Reliable mobile shell height.

     In-app browsers (LINE, Instagram, etc.) and some mobile browsers report
     `100dvh`/`100vh`/`100svh` inconsistently: the value can be wrong at first
     paint and get "stuck" after a toolbar toggle or a form-control focus,
     leaving the fixed app shell shorter than the visible viewport (a large gap
     below the bottom nav, seen right after tapping a layout radio).

     Measuring the real visible area in JS and updating it aggressively tracks
     the viewport across these browsers. `visualViewport.height` is the actual
     painted area (falls back to `window.innerHeight`). We deliberately do NOT
     shrink the shell while a field is focused, so the on-screen keyboard does
     not collapse the layout. --}}
<script>
    (function () {
        var root = document.documentElement;

        function visibleHeight() {
            var vv = window.visualViewport;
            return Math.round(vv ? vv.height : window.innerHeight);
        }

        function isTyping() {
            var el = document.activeElement;
            return !!el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.isContentEditable);
        }

        function setAppHeight() {
            if (isTyping()) return; // keep the shell full-height while the keyboard is open
            root.style.setProperty('--app-h', visibleHeight() + 'px');
        }

        setAppHeight();

        ['resize', 'orientationchange', 'pageshow'].forEach(function (evt) {
            window.addEventListener(evt, setAppHeight);
        });

        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', setAppHeight);
            window.visualViewport.addEventListener('scroll', setAppHeight);
        }

        // In-app browsers often finalize their toolbar after first paint without
        // firing any event — re-measure a few times as it settles.
        [60, 150, 300, 600, 1000].forEach(function (delay) {
            setTimeout(setAppHeight, delay);
        });
    })();
</script>
