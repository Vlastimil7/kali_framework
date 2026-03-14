<?php
// src/Helpers/ContactAttachmentUpload.php

namespace Helpers;

class ContactAttachmentUpload
{
    public const MAX_FILES = 5;
    public const MAX_FILE_BYTES = 8_000_000;   // 8 MB / file
    public const MAX_TOTAL_BYTES = 15_000_000; // 15 MB total

    private array $allowedMimes = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/jpg',
        'application/msword',
        'application/zip',
        
    ];

    public function saveTmp(array $files, string $tmpDir): array
    {
        // $files = $_FILES['attachments']
        $saved = [];
        $errors = [];

        if (empty($files) || empty($files['name']) || !is_array($files['name'])) {
            return ['success' => true, 'files' => [], 'errors' => []];
        }

        $count = count($files['name']);
        if ($count > self::MAX_FILES) {
            return ['success' => false, 'files' => [], 'errors' => ["Maximálně " . self::MAX_FILES . " souborů."]];
        }

        if (!is_dir($tmpDir) && !@mkdir($tmpDir, 0775, true) && !is_dir($tmpDir)) {
            return ['success' => false, 'files' => [], 'errors' => ['Nelze vytvořit složku pro přílohy.']];
        }

        $total = 0;

        for ($i = 0; $i < $count; $i++) {
            $orig = (string)($files['name'][$i] ?? '');
            $tmp  = (string)($files['tmp_name'][$i] ?? '');
            $err  = (int)($files['error'][$i] ?? UPLOAD_ERR_NO_FILE);
            $size = (int)($files['size'][$i] ?? 0);

            if ($err === UPLOAD_ERR_NO_FILE) continue;

            if ($err !== UPLOAD_ERR_OK) {
                $errors[] = "Soubor {$orig}: chyba uploadu ({$err}).";
                continue;
            }

            if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
                $errors[] = "Soubor {$orig}: neplatná velikost (max " . self::MAX_FILE_BYTES . " B).";
                continue;
            }

            $total += $size;
            if ($total > self::MAX_TOTAL_BYTES) {
                $errors[] = "Překročen celkový limit příloh (max " . self::MAX_TOTAL_BYTES . " B).";
                break;
            }

            $mime = $this->detectMime($tmp);
            if (!in_array($mime, $this->allowedMimes, true)) {
                $errors[] = "Soubor {$orig}: nepovolený typ ({$mime}).";
                continue;
            }

            $safeOrig = $this->safeFilename($orig);
            $stored = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '_' . $safeOrig;
            $dest = rtrim($tmpDir, "\\/") . DIRECTORY_SEPARATOR . $stored;

            if (!@move_uploaded_file($tmp, $dest)) {
                $errors[] = "Soubor {$orig}: nepodařilo se uložit.";
                continue;
            }

            $saved[] = [
                'path' => $dest,
                'name' => $safeOrig, // jak se zobrazí v emailu
                'mime' => $mime,
                'size' => $size,
            ];
        }

        return [
            'success' => count($errors) === 0,
            'files'   => $saved,
            'errors'  => $errors,
        ];
    }

    public function cleanup(array $savedFiles): void
    {
        foreach ($savedFiles as $f) {
            $p = $f['path'] ?? null;
            if ($p && is_file($p)) @unlink($p);
        }
    }

    private function detectMime(string $path): string
    {
        if (!is_file($path)) return 'application/octet-stream';
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $fi ? finfo_file($fi, $path) : null;
        if ($fi) finfo_close($fi);
        return $mime ?: 'application/octet-stream';
    }

    private function safeFilename(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('~[^\pL0-9._-]+~u', '_', $name);
        return mb_substr($name, 0, 150);
    }

    public function ensureTmpDir(string $dir): void
    {
        if ($dir === '') {
            throw new \InvalidArgumentException('Tmp dir path is empty');
        }

        if (is_dir($dir)) {
            return;
        }

        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create tmp dir: {$dir}");
        }
    }
}
