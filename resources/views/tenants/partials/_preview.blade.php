{{-- Live theme preview (16:9), shared between wizard steps --}}
<p class="mb-4 text-center text-xs font-semibold uppercase tracking-widest text-slate-400">Pratinjau</p>
<div class="preview-16x9 mx-auto w-full max-w-xl overflow-hidden transition-all duration-300"
     :style="`background-color:${bg}; font-family:${font}; ${previewWrapStyle}`">

    {{-- TOPBAR --}}
    <div x-show="activeLayout === 'topbar'"
         style="{{ $currentLayout === 'topbar' ? '' : 'display:none' }}"
         class="flex h-full flex-col">
        <div class="flex flex-shrink-0 items-center gap-1.5 px-3 py-1.5 transition-colors duration-300"
             :style="`background-color:${primary};`">
            <div class="h-4 w-4 flex-shrink-0 transition-all duration-300"
                 :style="`background:rgba(255,255,255,0.5); border-radius:${activeTheme==='retro'?'0':'3px'};`"></div>
            <div class="flex flex-1 items-center gap-1 ml-1">
                <div class="px-1.5 py-0.5 text-[6px] font-bold text-white/90 transition-all duration-300" :style="activeNavStyle">Kasir</div>
                <div class="px-1.5 py-0.5 transition-all duration-300" :style="navItemStyle"><span class="block h-1 w-5 bg-white/50 rounded-full"></span></div>
                <div class="px-1.5 py-0.5 transition-all duration-300" :style="navItemStyle"><span class="block h-1 w-5 bg-white/50 rounded-full"></span></div>
            </div>
            <div class="h-4 w-4 flex-shrink-0 rounded-full bg-white/30"></div>
        </div>
        <div class="flex flex-shrink-0 gap-1 px-2 py-1 transition-colors duration-300" :style="`background-color:${surface};`">
            <span class="px-1.5 py-0.5 text-[6px] font-bold transition-all duration-300" :style="activeChipStyle">Semua</span>
            <span class="px-1.5 py-0.5 text-[6px] transition-all duration-300" :style="`${chipStyle} color:${muted};`">Makanan</span>
            <span class="px-1.5 py-0.5 text-[6px] transition-all duration-300" :style="`${chipStyle} color:${muted};`">Minuman</span>
        </div>
        <div class="flex flex-1 gap-1.5 p-1.5 min-h-0">
            <div class="grid flex-1 grid-cols-3 gap-1 content-start">
                @for ($i = 0; $i < 6; $i++)
                    <div class="flex flex-col gap-0.5 p-1 transition-all duration-300" :style="cardStyle">
                        <div class="h-6 transition-colors duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':activeTheme==='classic'?'2px':'4px'};`"></div>
                        <div class="h-0.5 w-3/4" :style="`background-color:${muted}40; border-radius:${activeTheme==='retro'?'0':'9999px'};`"></div>
                        <div class="h-0.5" :style="`background-color:${primary}80; border-radius:${activeTheme==='retro'?'0':'9999px'};`"></div>
                    </div>
                @endfor
            </div>
            <div class="flex w-16 flex-shrink-0 flex-col gap-1 p-1.5 transition-all duration-300"
                 :style="`background-color:${surface}; border:1px solid ${border}; border-radius:${activeTheme==='retro'?'0':activeTheme==='classic'?'3px':'8px'};`">
                <div class="h-0.5 w-3/5" :style="`background-color:${fg}25; border-radius:9999px;`"></div>
                <div class="flex flex-1 flex-col gap-1">
                    <div class="h-3 transition-all duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                    <div class="h-3 transition-all duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                    <div class="h-3 transition-all duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                </div>
                <div class="flex h-5 items-center justify-center text-[7px] font-bold text-white transition-all duration-300" :style="bayarStyle">Bayar</div>
            </div>
        </div>
    </div>

    {{-- SIDEBAR --}}
    <div x-show="activeLayout === 'sidebar'"
         style="{{ $currentLayout === 'sidebar' ? '' : 'display:none' }}"
         class="flex h-full">
        <div class="flex w-10 flex-shrink-0 flex-col items-center gap-2 py-2 transition-colors duration-300"
             :style="`background-color:${primary};`">
            <div class="h-4 w-4 transition-all duration-300"
                 :style="`background:rgba(255,255,255,0.5); border-radius:${activeTheme==='retro'?'0':'3px'};`"></div>
            <div class="mt-0.5 w-7 px-1 py-0.5 text-center text-[5px] font-bold text-white/90 transition-all duration-300" :style="activeNavStyle">Kasir</div>
            <div class="w-7 py-0.5 transition-all duration-300" :style="navItemStyle"><span class="block h-0.5 w-full bg-white/45 rounded-full"></span></div>
            <div class="w-7 py-0.5 transition-all duration-300" :style="navItemStyle"><span class="block h-0.5 w-full bg-white/45 rounded-full"></span></div>
            <div class="w-7 py-0.5 transition-all duration-300" :style="navItemStyle"><span class="block h-0.5 w-full bg-white/45 rounded-full"></span></div>
        </div>
        <div class="flex flex-1 flex-col min-h-0">
            <div class="flex flex-shrink-0 gap-1 px-2 py-1 transition-colors duration-300" :style="`background-color:${surface};`">
                <span class="px-1.5 py-0.5 text-[6px] font-bold transition-all duration-300" :style="activeChipStyle">Semua</span>
                <span class="px-1.5 py-0.5 text-[6px] transition-all duration-300" :style="`${chipStyle} color:${muted};`">Makanan</span>
                <span class="px-1.5 py-0.5 text-[6px] transition-all duration-300" :style="`${chipStyle} color:${muted};`">Minuman</span>
            </div>
            <div class="flex flex-1 gap-1.5 p-1.5 min-h-0">
                <div class="grid flex-1 grid-cols-3 gap-1 content-start">
                    @for ($i = 0; $i < 6; $i++)
                        <div class="flex flex-col gap-0.5 p-1 transition-all duration-300" :style="cardStyle">
                            <div class="h-6 transition-colors duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':activeTheme==='classic'?'2px':'4px'};`"></div>
                            <div class="h-0.5 w-3/4" :style="`background-color:${muted}40; border-radius:${activeTheme==='retro'?'0':'9999px'};`"></div>
                            <div class="h-0.5" :style="`background-color:${primary}80; border-radius:${activeTheme==='retro'?'0':'9999px'};`"></div>
                        </div>
                    @endfor
                </div>
                <div class="flex w-16 flex-shrink-0 flex-col gap-1 p-1.5 transition-all duration-300"
                     :style="`background-color:${surface}; border:1px solid ${border}; border-radius:${activeTheme==='retro'?'0':activeTheme==='classic'?'3px':'8px'};`">
                    <div class="h-0.5 w-3/5" :style="`background-color:${fg}25; border-radius:9999px;`"></div>
                    <div class="flex flex-1 flex-col gap-1">
                        <div class="h-3 transition-all duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                        <div class="h-3 transition-all duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                        <div class="h-3 transition-all duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                    </div>
                    <div class="flex h-5 items-center justify-center text-[7px] font-bold text-white transition-all duration-300" :style="bayarStyle">Bayar</div>
                </div>
            </div>
        </div>
    </div>

    {{-- BOTTOMBAR --}}
    <div x-show="activeLayout === 'bottombar'"
         style="{{ $currentLayout === 'bottombar' ? '' : 'display:none' }}"
         class="flex h-full flex-col">
        <div class="flex flex-shrink-0 gap-1 px-2 py-1 transition-colors duration-300" :style="`background-color:${surface};`">
            <span class="px-1.5 py-0.5 text-[6px] font-bold transition-all duration-300" :style="activeChipStyle">Semua</span>
            <span class="px-1.5 py-0.5 text-[6px] transition-all duration-300" :style="`${chipStyle} color:${muted};`">Makanan</span>
            <span class="px-1.5 py-0.5 text-[6px] transition-all duration-300" :style="`${chipStyle} color:${muted};`">Minuman</span>
        </div>
        <div class="flex flex-1 gap-1.5 p-1.5 min-h-0">
            <div class="grid flex-1 grid-cols-3 gap-1 content-start">
                @for ($i = 0; $i < 6; $i++)
                    <div class="flex flex-col gap-0.5 p-1 transition-all duration-300" :style="cardStyle">
                        <div class="h-6 transition-colors duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':activeTheme==='classic'?'2px':'4px'};`"></div>
                        <div class="h-0.5 w-3/4" :style="`background-color:${muted}40; border-radius:${activeTheme==='retro'?'0':'9999px'};`"></div>
                        <div class="h-0.5" :style="`background-color:${primary}80; border-radius:${activeTheme==='retro'?'0':'9999px'};`"></div>
                    </div>
                @endfor
            </div>
            <div class="flex w-16 flex-shrink-0 flex-col gap-1 p-1.5 transition-all duration-300"
                 :style="`background-color:${surface}; border:1px solid ${border}; border-radius:${activeTheme==='retro'?'0':activeTheme==='classic'?'3px':'8px'};`">
                <div class="h-0.5 w-3/5" :style="`background-color:${fg}25; border-radius:9999px;`"></div>
                <div class="flex flex-1 flex-col gap-1">
                    <div class="h-3 transition-all duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                    <div class="h-3 transition-all duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                    <div class="h-3 transition-all duration-300" :style="`background-color:${bg}; border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                </div>
                <div class="flex h-5 items-center justify-center text-[7px] font-bold text-white transition-all duration-300" :style="bayarStyle">Bayar</div>
            </div>
        </div>
        <div class="flex flex-shrink-0 items-center justify-around px-4 py-1.5 transition-colors duration-300"
             :style="`background-color:${primary};`">
            <div class="flex flex-col items-center gap-0.5 px-1.5 py-0.5 transition-all duration-300" :style="activeNavStyle">
                <div class="h-2.5 w-2.5" :style="`background:rgba(255,255,255,0.9); border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                <span class="text-[5px] text-white/90 font-bold">Kasir</span>
            </div>
            @for ($i = 0; $i < 3; $i++)
                <div class="flex flex-col items-center gap-0.5 px-1.5 py-0.5 transition-all duration-300" :style="navItemStyle">
                    <div class="h-2.5 w-2.5" :style="`background:rgba(255,255,255,0.45); border-radius:${activeTheme==='retro'?'0':'2px'};`"></div>
                    <span class="h-0.5 w-4 block" :style="`background:rgba(255,255,255,0.35); border-radius:${activeTheme==='retro'?'0':'9999px'};`"></span>
                </div>
            @endfor
        </div>
    </div>
</div>
