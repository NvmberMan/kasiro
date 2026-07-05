{{-- Reliable mobile shell height.

     In-app browsers (LINE, IG, etc.) and some mobile browsers report `100dvh`/`100vh`
     inconsistently: after a toolbar toggle or a form-control focus the value can get
     stuck, leaving the fixed app shell shorter than the visible viewport (a gap below
     the bottom nav). Measuring `window.innerHeight` in JS and updating it on every
     resize tracks the real visual viewport across all of these browsers. --}}
<script>
    (function () {
        var root = document.documentElement;
        function setAppHeight() {
            root.style.setProperty('--app-h', window.innerHeight + 'px');
        }
        setAppHeight();
        window.addEventListener('resize', setAppHeight);
        window.addEventListener('orientationchange', setAppHeight);
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', setAppHeight);
        }
    })();
</script>
