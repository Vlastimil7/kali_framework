<?php

/** @var array|null $presence */
/** @var array $timeline */

$dh = [];
if (!empty($presence['device_hints_json'])) {
    $dh = json_decode((string)$presence['device_hints_json'], true) ?: [];
}
$device = trim(($dh['deviceCategory'] ?? '') . ' ' . ($dh['viewportWidth'] ?? '') . '×' . ($dh['viewportHeight'] ?? ''));
?>

<div class="bg-gray-50 rounded-xl border">
    <div class="max-w-7xl mx-auto px-4 py-8">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
            <div>
                <div class="text-sm text-gray-500">Admin / Telemetry</div>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">Session detail</h1>
                <p class="text-sm text-gray-500 mt-1 font-mono"><?= htmlspecialchars($sessionId) ?></p>
            </div>

            <div class="flex items-center gap-2">
                <a href="<?= config('app.base_url', '') ?>/admin/telemetry/online?app=<?= urlencode($app) ?>"
                    class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">Online</a>
                <a href="<?= config('app.base_url', '') ?>/admin/telemetry/events?app=<?= urlencode($app) ?>"
                    class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">Historie</a>
                <a href="<?= config('app.base_url', '') ?>/admin/telemetry?app=<?= urlencode($app) ?>"
                    class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">Dashboard</a>
            </div>
        </div>

        <!-- Presence -->
        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <div class="text-xs text-gray-500">Last seen</div>
                    <div class="font-semibold text-gray-900 mt-1"><?= htmlspecialchars($presence['last_seen'] ?? '-') ?></div>
                </div>

                <div>
                    <div class="text-xs text-gray-500">Last page</div>
                    <div class="font-mono text-xs text-gray-900 mt-1 break-all"><?= htmlspecialchars($presence['page_path'] ?? '-') ?></div>
                </div>

                <div>
                    <div class="text-xs text-gray-500">Device</div>
                    <div class="text-sm text-gray-900 mt-1"><?= htmlspecialchars($device ?: '-') ?></div>
                </div>
            </div>

            <div class="mt-4 text-xs text-gray-500 break-all">
                UA: <?= htmlspecialchars($presence['user_agent'] ?? '-') ?>
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b-2 border-gray-200">
                <div class="font-semibold text-gray-900">Timeline</div>
                <div class="text-sm text-gray-500">Eventy v rámci session (chronologicky).</div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left px-4 py-3 font-semibold">Time</th>
                            <th class="text-left px-4 py-3 font-semibold">Event</th>
                            <th class="text-left px-4 py-3 font-semibold">Page</th>
                            <th class="text-left px-4 py-3 font-semibold">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($timeline)): ?>
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-gray-500">Zatím žádné eventy pro tuto session.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($timeline as $r): ?>
                                <tr class="hover:bg-gray-50 align-top">
                                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap"><?= htmlspecialchars($r['received_at'] ?? '-') ?></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex font-mono text-xs px-2 py-1 rounded-lg bg-gray-50 border-2 border-gray-200 text-gray-700">
                                            <?= htmlspecialchars($r['event_name'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-800"><?= htmlspecialchars($r['page_path'] ?? '-') ?></td>
                                    <td class="px-4 py-3">
                                        <details>
                                            <summary class="cursor-pointer text-sm font-semibold text-gray-800">JSON</summary>
                                            <pre class="mt-2 p-3 rounded-lg bg-gray-50 border-2 border-gray-200 text-xs overflow-x-auto"><?= htmlspecialchars((string)($r['event_data_json'] ?? '')) ?></pre>
                                        </details>
                                    </td>
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