<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$directories = ['public', 'src', 'tests', 'tools'];
$files = [];

foreach (['.php-cs-fixer.dist.php'] as $rootFile) {
    $path = $root . DIRECTORY_SEPARATOR . $rootFile;
    if (is_file($path)) {
        $files[] = $path;
    }
}

foreach ($directories as $directory) {
    $path = $root . DIRECTORY_SEPARATOR . $directory;
    if (!is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);
$failed = [];

foreach ($files as $file) {
    $output = [];
    $exitCode = 0;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        $failed[$file] = implode(PHP_EOL, $output);
    }
}

if ($failed !== []) {
    foreach ($failed as $file => $message) {
        fwrite(STDERR, "{$file}" . PHP_EOL . "{$message}" . PHP_EOL);
    }
    exit(1);
}

echo 'PHP syntax lint passed (' . count($files) . ' files).' . PHP_EOL;
