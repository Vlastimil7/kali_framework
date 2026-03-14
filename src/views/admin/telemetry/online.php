<?php

/** @var array $rows */ ?>

<div class="bg-black rounded-xl border">
  <div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
      <div>
        <div class="text-sm text-white">Admin / Telemetry</div>
        <h1 class="text-2xl font-bold text-white mt-1">Online</h1>
        <p class="text-sm text-white mt-1">Aktivní session za posledních <?= (int)$window ?> s.</p>
      </div>

      <div class="flex items-center gap-2">
        <a href="<?= BASE_URL ?>/admin/telemetry" class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/telemetry/events?app=<?= urlencode($app) ?>" class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">Historie</a>
      </div>
    </div>

    <form method="get" class="flex flex-wrap gap-2 mb-4">
      <input type="hidden" name="app" value="<?= htmlspecialchars($app) ?>">
      <select name="window" class="px-3 py-2 rounded-lg border-2 border-gray-200 text-sm text-white bg-black">
        <?php foreach ([10, 20, 30, 60] as $w): ?>
          <option value="<?= $w ?>" <?= ((int)$window === $w) ? 'selected' : '' ?>><?= $w ?>s</option>
        <?php endforeach; ?>
      </select>
      <button class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold cursor-pointer">Filtrovat</button>

      <a href="<?= BASE_URL ?>/admin/telemetry/online?app=<?= urlencode($app) ?>&window=<?= (int)$window ?>"
        class="px-4 py-2 rounded-lg bg-white border-2 border-gray-200 hover:bg-gray-50 text-sm font-semibold">
        Obnovit
      </a>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-700">
            <tr class="border-b-2 border-gray-200">
              <th class="text-left px-4 py-3 font-semibold">Last seen</th>
              <th class="text-left px-4 py-3 font-semibold">Page</th>
              <th class="text-left px-4 py-3 font-semibold">Session</th>
              <th class="text-left px-4 py-3 font-semibold">Device</th>
              <th class="text-left px-4 py-3 font-semibold">UA</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200">
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="5" class="px-4 py-10 text-center text-gray-500">Nikdo online.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
                <?php
                $dh = [];
                if (!empty($r['device_hints_json'])) {
                  $dh = json_decode((string)$r['device_hints_json'], true) ?: [];
                }
                $device = trim(($dh['deviceCategory'] ?? '') . ' ' . ($dh['viewportWidth'] ?? '') . '×' . ($dh['viewportHeight'] ?? ''));
                ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($r['last_seen'] ?? '-') ?></td>
                  <td class="px-4 py-3 font-mono text-xs text-gray-800"><?= htmlspecialchars($r['page_path'] ?? '-') ?></td>
                  <td class="px-4 py-3">
                    <a class="inline-flex font-mono text-xs px-2 py-1 rounded-lg bg-gray-50 border-2 border-gray-200 text-gray-700 hover:bg-white"
                      href="<?= BASE_URL ?>/admin/telemetry/session/<?= urlencode($r['session_id']) ?>?app=<?= urlencode($app) ?>">
                      <?= htmlspecialchars($r['session_id'] ?? '-') ?>
                    </a>
                  </td>
                  <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($device ?: '-') ?></td>
                  <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars(mb_strimwidth((string)($r['user_agent'] ?? ''), 0, 60, '…')) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>

        </table>
      </div>
    </div>

  </div>
</div>