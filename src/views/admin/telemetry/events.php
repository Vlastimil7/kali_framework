<?php

/** @var array $rows */
/** @var array $eventNames */
/** @var array $filters */

$qsBase = function (array $overrides = []) use ($app, $range, $filters) {
    $q = array_merge([
        'app' => $app,
        'range' => $range,
        'event' => $filters['event'] ?? '',
        'path' => $filters['path'] ?? '',
        'sid' => $filters['sid'] ?? '',
    ], $overrides);
    // odstranit prázdné
    foreach ($q as $k => $v) {
        if ($v === '' || $v === null) unset($q[$k]);
    }
    return http_build_query($q);
};

$totalPages = (int)ceil(($total ?? 0) / max(1, (int)$limit));
?>

<div class="bg-black rounded-xl border">
    <div class="max-w-7xl mx-auto px-4 py-8">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
            <div>
                <div class="text-sm text-white">Admin / Telemetry</div>
                <h1 class="text-2xl font-bold text-white mt-1">Historie</h1>
                <p class="text-sm text-white mt-1">Výpis eventů (<?= htmlspecialchars($range) ?>).</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="<?= BASE_URL ?>/admin/telemetry?<?= $qsBase([]) ?>"
                    class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">Dashboard</a>
                <a href="<?= BASE_URL ?>/admin/telemetry/online?app=<?= urlencode($app) ?>"
                    class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">Online</a>
            </div>
        </div>

        <form method="get" class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Range</label>
                    <select name="range" class="w-full px-3 py-2 rounded-lg border-2 border-gray-200 text-sm">
                        <option value="24h" <?= $range === '24h' ? 'selected' : '' ?>>24h</option>
                        <option value="7d" <?= $range === '7d' ? 'selected' : '' ?>>7d</option>
                        <option value="30d" <?= $range === '30d' ? 'selected' : '' ?>>30d</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Event</label>
                    <select name="event" class="w-full px-3 py-2 rounded-lg border-2 border-gray-200 text-sm">
                        <option value="">(all)</option>
                        <?php foreach (($eventNames ?? []) as $en): ?>
                            <option value="<?= htmlspecialchars($en) ?>" <?= (($filters['event'] ?? '') === $en) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($en) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Page path</label>
                    <input name="path" value="<?= htmlspecialchars((string)($filters['path'] ?? '')) ?>"
                        class="w-full px-3 py-2 rounded-lg border-2 border-gray-200 text-sm font-mono"
                        placeholder="/contact" />
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Session ID</label>
                    <input name="sid" value="<?= htmlspecialchars((string)($filters['sid'] ?? '')) ?>"
                        class="w-full px-3 py-2 rounded-lg border-2 border-gray-200 text-sm font-mono"
                        placeholder="uuid..." />
                </div>
            </div>

            <div class="flex items-center gap-2 mt-4">
                <input type="hidden" name="app" value="<?= htmlspecialchars($app) ?>">
                <button class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold">Filtrovat</button>

                <a href="<?= BASE_URL ?>/admin/telemetry/events?app=<?= urlencode($app) ?>&range=<?= urlencode($range) ?>"
                    class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">
                    Reset
                </a>

                <div class="ml-auto text-sm text-gray-500">
                    Celkem: <span class="font-semibold text-gray-900"><?= (int)$total ?></span>
                </div>
            </div>
        </form>

        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left px-4 py-3 font-semibold">IP</th>
                            <th class="text-left px-4 py-3 font-semibold">Time</th>
                            <th class="text-left px-4 py-3 font-semibold">Event</th>
                            <th class="text-left px-4 py-3 font-semibold">Page</th>
                            <th class="text-left px-4 py-3 font-semibold">Session</th>
                            <th class="text-left px-4 py-3 font-semibold">Data</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($rows)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-gray-500">Nic nenalezeno.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rows as $r): ?>
                                <?php
                                $jsonPreview = mb_strimwidth((string)($r['event_data_json'] ?? ''), 0, 140, '…');
                                ?>
                                <tr class="hover:bg-gray-50 align-top">
                                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap"><?= htmlspecialchars($r['client_ip'] ?? '-') ?></td>
                                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap"><?= htmlspecialchars($r['received_at'] ?? '-') ?></td>

                                    <td class="px-4 py-3">
                                        <span class="inline-flex font-mono text-xs px-2 py-1 rounded-lg bg-gray-50 border-2 border-gray-200 text-gray-700">
                                            <?= htmlspecialchars($r['event_name'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 font-mono text-xs text-gray-800"><?= htmlspecialchars($r['page_path'] ?? '-') ?></td>

                                    <td class="px-4 py-3">
                                        <a class="inline-flex font-mono text-xs px-2 py-1 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-gray-700"
                                            href="<?= BASE_URL ?>/admin/telemetry/session/<?= urlencode($r['session_id'] ?? '') ?>?app=<?= urlencode($app) ?>">
                                            <?= htmlspecialchars($r['session_id'] ?? '-') ?>
                                        </a>
                                    </td>

                                    <td class="px-4 py-3 text-gray-700">
                                        <details>
                                            <summary class="cursor-pointer text-sm font-semibold text-gray-800">JSON</summary>
                                            <pre class="mt-2 p-3 rounded-lg bg-gray-50 border-2 border-gray-200 text-xs overflow-x-auto"><?= htmlspecialchars((string)($r['event_data_json'] ?? '')) ?></pre>
                                        </details>
                                        <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($jsonPreview) ?></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if (($totalPages ?? 1) > 1): ?>
            <div class="flex items-center justify-between mt-4 text-sm">
                <div class="text-gray-500">
                    Strana <?= (int)$page ?> / <?= (int)$totalPages ?>
                </div>

                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                        <a class="px-3 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 font-semibold"
                            href="<?= BASE_URL ?>/admin/telemetry/events?<?= $qsBase(['page' => $page - 1]) ?>">← Předchozí</a>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                        <a class="px-3 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 font-semibold"
                            href="<?= BASE_URL ?>/admin/telemetry/events?<?= $qsBase(['page' => $page + 1]) ?>">Další →</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-xs text-slate-500">
            <a href="<?= BASE_URL ?>/admin/dashboard" class="hover:text-white">← Zpět na dashboard</a>
        </div>

    </div>
</div>