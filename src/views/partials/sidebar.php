<?php
// src/views/partials/sidebar.php

$navMain = [
    ['url' => '/',                   'icon' => '🏠', 'label' => 'Úvod'],
    ['url' => '/contact#google-address', 'icon' => '📍', 'label' => 'Adresa'],
    ['url' => '/news',               'icon' => '📰', 'label' => 'Novinky'],
    ['url' => '/#hours',                   'icon' => '🕒', 'label' => 'Ordinační hodiny'],
    ['url' => '/#vacations',                   'icon' => '🌴', 'label' => 'Dovolená / Zástup'],
    ['url' => '/information',        'icon' => 'ℹ️', 'label' => 'Informace pro rodiče'],
    ['url' => '/information/cenik',  'icon' => '💶', 'label' => 'Ceník'],
    ['url' => '/ambulance',          'icon' => '🏥', 'label' => 'Kontakty na odborné ambulance'],
    ['url' => '/about',              'icon' => '👨‍⚕️', 'label' => 'O mně'],
    ['url' => '/tips',               'icon' => '💡', 'label' => 'Praktické rady'],
    ['url' => '/videa',              'icon' => '📹', 'label' => 'Videa']

];

$navAmbulance = [
    ['url' => '/ambulance',           'icon' => '📞', 'label' => 'Kontakty'],
    ['url' => '/ambulance/psycholog', 'icon' => '🧠', 'label' => 'Psycholog'],
    ['url' => '/ambulance/psychiatr', 'icon' => '🧬', 'label' => 'Psychiatrie'],
    ['url' => '/ambulance/kozni',     'icon' => '🧴', 'label' => 'Kožní'],
    ['url' => '/ambulance/alergologie', 'icon' => '🌿', 'label' => 'Alergologie'],
    ['url' => '/ambulance/neurologie', 'icon' => '⚡', 'label' => 'Neurologie'],
    ['url' => '/ambulance/rehabilitace', 'icon' => '🏃‍♂️', 'label' => 'Rehabilitace'],
    ['url' => '/ambulance/ortopedie', 'icon' => '🦴', 'label' => 'Ortopedie'],
    ['url' => '/ambulance/orl',       'icon' => '👂', 'label' => 'ORL'],
    ['url' => '/ambulance/tbc',       'icon' => '💉', 'label' => 'TBC očkování'],
    ['url' => '/ambulance/rtg-uz',    'icon' => '🔊', 'label' => 'RTG - UZ'],
    ['url' => '/ambulance/chirurgy',  'icon' => '✂️', 'label' => 'Chirurgie'],
];
?>

<!-- 📱 Mobilní floating tlačítko pro sidebar -->
<button
    id="sidebarFab"
    type="button"
    class="lg:hidden fixed bottom-6 right-6 z-[70] bg-indigo-600 hover:bg-indigo-700 text-white rounded-full px-4 h-12 flex items-center gap-2 shadow-xl transition cursor-pointer"
    aria-label="Otevřít navigaci">
    <!-- Hamburger ikona -->
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
        <path d="M4 7h16a1 1 0 0 0 0-2H4a1 1 0 1 0 0 2Zm0 6h16a1 1 0 0 0 0-2H4a1 1 0 0 0 0 2Zm0 6h16a1 1 0 0 0 0-2H4a1 1 0 0 0 0 2Z" />
    </svg>
    <span class="text-xs font-semibold tracking-wide uppercase">
        Menu
    </span>
</button>

<!-- 📱 Mobilní overlay -->
<div
    id="sidebarOverlay"
    class="lg:hidden fixed inset-0 bg-black/40 backdrop-blur-sm hidden z-40 cursor-pointer"></div>

<!-- 📱 Mobilní off-canvas sidebar -->
<div
    id="sidebarMobile"
    class="lg:hidden fixed top-0 left-0 w-72 max-w-[80%] h-full bg-[#e5e9ff] border-blue-200 backdrop-blur  shadow-xl transform -translate-x-full transition-transform duration-300 z-[60] overflow-y-auto">
    <!-- Horní lišta -->
    <div class="flex items-center justify-between px-4 py-3 ">
        <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
            <!--      Logo -->
            <a href="<?= locale_url() ?>" class="block">
                <img src="<?= config('app.base_url', '') ?>/assets/images/logo/mido_barbershop_logo.png"
                    alt="Logo"
                    class="h-20 w-auto  rounded-[100px] ">
            </a>

        </div>
        <button
            id="sidebarCloseMobile"
            type="button"
            class="text-xs font-medium text-slate-800 hover:text-slate-800 cursor-pointer">
            Zavřít ✕
        </button>
    </div>

    <!-- Obsah menu -->
    <div class="p-4 text-sm">
        <p class="px-1 pb-2 text-[0.7rem] font-extrabold tracking-wide text-slate-800 uppercase">
            Menu
        </p>


        <nav class="space-y-1 mb-4">
            <?php foreach ($navMain as $item): ?>
                <a href="<?= locale_url($item['url']) ?>"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 font-medium text-slate-800  cursor-pointer">
                    <span><?= $item['icon'] ?></span>
                    <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <p class="px-1 pb-1 text-[0.7rem] font-extrabold tracking-wide text-slate-800 uppercase">
            Kontakty na odborné ambulance
        </p>
        <nav class="space-y-1">
            <?php foreach ($navAmbulance as $item): ?>
                <a href="<?= locale_url($item['url']) ?>"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 font-medium text-slate-800 cursor-pointer">
                    <span><?= $item['icon'] ?></span>
                    <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <p class="px-1 pb-1 pt-5 text-[0.7rem] font-extrabold tracking-wide text-slate-800 uppercase">
            Sociální sítě
        </p>
        <nav class="space-y-1">
            <a href="https://www.facebook.com/profile.php?id=61584071354442"
                target="_blank" rel="noopener noreferrer"
                class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 font-medium text-slate-800 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 fill-current"
                    viewBox="0 0 24 24">
                    <path d="M22 12.06C22 6.51 17.52 2 12 2S2 6.51 2 12.06C2 17.08 5.66 21.22 10.44 22v-6.9H7.9v-3.03h2.54V9.74c0-2.5 1.48-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 3.03h-2.34V22C18.34 21.22 22 17.08 22 12.06Z" />
                </svg>
                <span class="text-sm">Navštivte nás na Facebooku</span>
            </a>
        </nav>
    </div>
</div>

<!-- 💻 Desktop sidebar -->
<aside
    class="hidden lg:block w-60 shrink-0 sticky top-20 self-start bg-[#d9deff] border-blue-200 backdrop-blur  rounded-2xl p-4 text-sm shadow-sm">
    <!--      Logo -->
    <a href="<?= locale_url() ?>" class="block pb-5">
        <img src="<?= config('app.base_url', '') ?>/assets/images/logo/mido_barbershop_logo.png"
            alt="Logo"
            class="h-18 w-auto  rounded-[50px] ">
    </a>
    <p class="px-1 pb-2 text-[0.7rem] font-extrabold tracking-wide text-slate-800 uppercase">
        Menu
    </p>

    <nav class="space-y-1 mb-4">
        <?php foreach ($navMain as $item): ?>
            <a href="<?= locale_url($item['url']) ?>"
                class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 font-medium text-slate-800 cursor-pointer">
                <span><?= $item['icon'] ?></span>
                <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <p class="px-1 pb-1 text-[0.7rem] font-extrabold tracking-wide text-slate-800 uppercase">
        Kontakty na odborné ambulance
    </p>
    <nav class="space-y-1">
        <?php foreach ($navAmbulance as $item): ?>
            <a href="<?= locale_url($item['url']) ?>"
                class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 font-medium text-slate-800 cursor-pointer">
                <span><?= $item['icon'] ?></span>
                <span><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <p class="px-1 pb-1 pt-5 text-[0.7rem] font-extrabold tracking-wide text-slate-800 uppercase">
        Sociální sítě
    </p>
    <nav class="space-y-1">
        <a href="https://www.facebook.com/profile.php?id=61584071354442"
            target="_blank" rel="noopener noreferrer"
            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-indigo-50 hover:text-indigo-700 font-medium text-slate-800 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 fill-current"
                viewBox="0 0 24 24">
                <path d="M22 12.06C22 6.51 17.52 2 12 2S2 6.51 2 12.06C2 17.08 5.66 21.22 10.44 22v-6.9H7.9v-3.03h2.54V9.74c0-2.5 1.48-3.88 3.77-3.88 1.09 0 2.23.2 2.23.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 3.03h-2.34V22C18.34 21.22 22 17.08 22 12.06Z" />
            </svg>
            <span class="text-sm">Navštivte nás na Facebooku</span>
        </a>
    </nav>
</aside>

<!-- 🔌 JS pro mobilní floating sidebar -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebarMobile');
        const overlay = document.getElementById('sidebarOverlay');
        const fab = document.getElementById('sidebarFab');
        const closeBtn = document.getElementById('sidebarCloseMobile');

        if (!sidebar || !overlay || !fab || !closeBtn) return;

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            overlay.classList.remove('hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            overlay.classList.add('hidden');
        }

        function toggleSidebar() {
            const isOpen = sidebar.classList.contains('translate-x-0');
            if (isOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        }

        fab.addEventListener('click', toggleSidebar);
        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    });
</script>
