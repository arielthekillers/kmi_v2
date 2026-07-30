<aside class="w-full md:w-56 flex-shrink-0">
    <nav id="settings-nav" class="flex md:flex-col gap-1 overflow-x-auto md:overflow-visible pb-2 md:pb-0 scroll-smooth">
        <?php 
        $active_tab = $active_settings_tab ?? 'general'; 
        
        $inactiveClass = "whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg text-gray-600 hover:bg-gray-100 transition-colors";
        $activeClass = "whitespace-nowrap flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg bg-indigo-50 text-indigo-700 font-semibold";
        $inactiveIconClass = "text-gray-400";
        $activeIconClass = "text-indigo-500";
        ?>

        <!-- General -->
        <a href="<?= url('/settings/general') ?>"
           class="<?= $active_tab === 'general' ? $activeClass : $inactiveClass ?>">
            <svg class="w-4 h-4 <?= $active_tab === 'general' ? $activeIconClass : $inactiveIconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            General
        </a>
        
        <!-- Integration Category -->
        <div class="mt-4 mb-2 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden md:block">Integration</div>
        <a href="<?= url('/settings/whatsapp') ?>"
           class="<?= $active_tab === 'whatsapp' ? $activeClass : $inactiveClass ?>">
            <i class="ri-whatsapp-line text-lg <?= $active_tab === 'whatsapp' ? $activeIconClass : $inactiveIconClass ?>"></i>
            WhatsApp API
        </a>

        <!-- TV Showcase Category -->
        <div class="mt-4 mb-2 px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest hidden md:block">TV Showcase</div>
        
        <!-- Note: the parent TV Showcase link is used in some pages -->
        <?php if ($active_tab === 'tvshowcase' || $active_tab === 'quotes'): ?>
            <a href="<?= url('/settings/tvshowcase') ?>"
               class="<?= $active_tab === 'tvshowcase' ? $activeClass : $inactiveClass ?>">
                <svg class="w-4 h-4 <?= $active_tab === 'tvshowcase' ? $activeIconClass : $inactiveIconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                TV Showcase
            </a>
            <a href="<?= url('/settings/quotes') ?>"
               class="<?= $active_tab === 'quotes' ? $activeClass : $inactiveClass ?>">
                <svg class="w-4 h-4 <?= $active_tab === 'quotes' ? $activeIconClass : $inactiveIconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Motto & Quotes
            </a>
        <?php else: ?>
            <a href="<?= url('/settings/tv/layout') ?>"
               class="<?= $active_tab === 'tv_layout' ? $activeClass : $inactiveClass ?>">
                <svg class="w-4 h-4 <?= $active_tab === 'tv_layout' ? $activeIconClass : $inactiveIconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Mode Tampilan
            </a>
            <a href="<?= url('/settings/tv/bgm') ?>"
               class="<?= $active_tab === 'tv_bgm' ? $activeClass : $inactiveClass ?>">
                <svg class="w-4 h-4 <?= $active_tab === 'tv_bgm' ? $activeIconClass : $inactiveIconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                Background Music
            </a>
            <a href="<?= url('/settings/tv/hours') ?>"
               class="<?= $active_tab === 'tv_hours' ? $activeClass : $inactiveClass ?>">
                <svg class="w-4 h-4 <?= $active_tab === 'tv_hours' ? $activeIconClass : $inactiveIconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Jam Pelajaran
            </a>
            <a href="<?= url('/settings/tv/quotes') ?>"
               class="<?= $active_tab === 'tv_quotes' ? $activeClass : $inactiveClass ?>">
                <svg class="w-4 h-4 <?= $active_tab === 'tv_quotes' ? $activeIconClass : $inactiveIconClass ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Quotes
            </a>
        <?php endif; ?>
    </nav>
</aside>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const nav = document.getElementById("settings-nav");
    if (nav) {
        const activeLink = nav.querySelector(".bg-indigo-50");
        if (activeLink) {
            activeLink.scrollIntoView({ behavior: "instant", block: "nearest", inline: "center" });
        }
    }
});
</script>
