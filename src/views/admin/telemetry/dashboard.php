<?php

/** @var array $data */ ?>

<div class="bg-black rounded-xl border">
    <div class="max-w-7xl mx-auto px-4 py-8">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
            <div>
                <div class="text-sm text-white">Admin / Telemetry</div>
                <h1 class="text-2xl font-bold text-white mt-1">Dashboard</h1>
                <p class="text-sm text-white mt-1">Souhrn za období: <?= htmlspecialchars($range) ?> (<?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?>)</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="<?= config('app.base_url', '') ?>/admin/telemetry/online?app=<?= urlencode($app) ?>"
                    class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">Online</a>
                <a href="<?= config('app.base_url', '') ?>/admin/telemetry/events?app=<?= urlencode($app) ?>"
                    class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">Historie</a>
            </div>
        </div>

        <form method="get" class="flex flex-wrap gap-2 mb-6">
            <input type="hidden" name="app" value="<?= htmlspecialchars($app) ?>">
            <select name="range" class="px-3 py-2 rounded-lg border-2 border-gray-200 text-sm text-white bg-black">
                <option value="24h" <?= $range === '24h' ? 'selected' : '' ?>>24h</option>
                <option value="7d" <?= $range === '7d' ? 'selected' : '' ?>>7d</option>
                <option value="30d" <?= $range === '30d' ? 'selected' : '' ?>>30d</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold cursor-pointer">Zobrazit</button>
        </form>

        <!-- KPI -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-5">
                <div class="text-sm text-gray-500">Online (<?= (int)($onlineWindow ?? 20) ?>s)</div>
                <div class="text-3xl font-bold text-gray-900 mt-2"><?= (int)($onlineCount ?? 0) ?></div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-5">
                <div class="text-sm text-gray-500">Events</div>
                <div class="text-3xl font-bold text-gray-900 mt-2"><?= (int)($data['events'] ?? 0) ?></div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-5">
                <div class="text-sm text-gray-500">Sessions (z historie)</div>
                <div class="text-3xl font-bold text-gray-900 mt-2"><?= (int)($data['sessions'] ?? 0) ?></div>
            </div>
        </div>

        <!-- Top Pages -->
        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b-2 border-gray-200 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-gray-900">Top pages</div>
                    <div class="text-sm text-gray-500">Nejčastěji trackované stránky v období.</div>
                </div>
                <a href="<?= config('app.base_url', '') ?>/admin/telemetry/events?app=<?= urlencode($app) ?>&range=<?= urlencode($range) ?>"
                    class="text-sm font-semibold text-gray-800 hover:underline">Otevřít historii →</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left px-4 py-3 font-semibold">Page path</th>
                            <th class="text-right px-4 py-3 font-semibold">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $topPages = $data['topPages'] ?? []; ?>
                        <?php if (empty($topPages)): ?>
                            <tr>
                                <td colspan="2" class="px-4 py-10 text-center text-gray-500">Zatím žádná data.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topPages as $r): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-800"><?= htmlspecialchars($r['page_path'] ?? '-') ?></td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900"><?= (int)($r['c'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Events -->
        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden mt-6">
            <div class="px-5 py-4 border-b-2 border-gray-200">
                <div class="font-semibold text-gray-900">Top events</div>
                <div class="text-sm text-gray-500">Rozložení názvů eventů v období.</div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left px-4 py-3 font-semibold">Event</th>
                            <th class="text-right px-4 py-3 font-semibold">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $topEvents = $data['topEvents'] ?? []; ?>
                        <?php if (empty($topEvents)): ?>
                            <tr>
                                <td colspan="2" class="px-4 py-10 text-center text-gray-500">Zatím žádná data.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topEvents as $r): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a class="font-mono text-xs inline-flex px-2 py-1 rounded-lg bg-gray-50 border-2 border-gray-200 text-gray-700 hover:bg-white"
                                            href="<?= config('app.base_url', '') ?>/admin/telemetry/events?app=<?= urlencode($app) ?>&range=<?= urlencode($range) ?>&event=<?= urlencode($r['event_name'] ?? '') ?>">
                                            <?= htmlspecialchars($r['event_name'] ?? '-') ?>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900"><?= (int)($r['c'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8 text-xs text-slate-500">
            <a href="<?= config('app.base_url', '') ?>/admin/dashboard" class="hover:text-white">← Zpět na dashboard</a>
        </div>

    </div>
</div>
