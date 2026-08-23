<?php

function projectPsr4Symbols(): array
{
    $projectRoot = dirname(__DIR__, 2);
    $roots = [
        'App\\' => 'app',
        'Database\\Factories\\' => 'database/factories',
        'Database\\Seeders\\' => 'database/seeders',
        'Tests\\' => 'tests',
    ];

    $symbols = [];
    $paths = [];
    $phpFiles = [];

    foreach ($roots as $prefix => $directory) {
        $absoluteRoot = $projectRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $directory);

        if (! is_dir($absoluteRoot)) {
            continue;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absoluteRoot));

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($projectRoot) + 1));
            $phpFiles[] = $relativePath;
            $contents = file_get_contents($file->getPathname());

            if (! preg_match('/^\s*namespace\s+([^;]+);/m', $contents, $namespace)
                || ! preg_match('/^\s*(?:(?:abstract|final|readonly)\s+)*(?:class|interface|trait|enum)\s+([A-Za-z_][A-Za-z0-9_]*)/m', $contents, $declaration)) {
                continue;
            }

            $fqcn = trim($namespace[1]).'\\'.$declaration[1];
            $expectedPath = $directory.'/'.str_replace('\\', '/', substr($fqcn, strlen($prefix))).'.php';

            $symbols[strtolower($fqcn)] = $fqcn;
            $paths[$fqcn] = [$relativePath, $expectedPath];
        }
    }

    return [$symbols, $paths, $phpFiles];
}

it('keeps PSR-4 namespaces, class names, and paths in exact case', function () {
    [, $paths] = projectPsr4Symbols();
    $mismatches = [];

    foreach ($paths as $fqcn => [$actual, $expected]) {
        if ($actual !== $expected) {
            $mismatches[] = "$fqcn: $actual (expected $expected)";
        }
    }

    expect($mismatches)->toBe([]);
});

it('uses valid exact-case internal App imports', function () {
    [$symbols, , $phpFiles] = projectPsr4Symbols();
    $projectRoot = dirname(__DIR__, 2);
    $mismatches = [];

    foreach ($phpFiles as $relativePath) {
        $contents = file_get_contents($projectRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
        preg_match_all('/^\s*use\s+(App\\\\[^;]+);/m', $contents, $imports);

        foreach ($imports[1] as $import) {
            $import = preg_replace('/\s+as\s+.*$/i', '', trim($import));

            if (str_contains($import, '{')) {
                continue;
            }

            $canonical = $symbols[strtolower($import)] ?? null;

            if ($canonical === null) {
                $mismatches[] = "$relativePath imports missing $import";
            } elseif ($import !== $canonical) {
                $mismatches[] = "$relativePath imports $import (expected $canonical)";
            }
        }
    }

    expect($mismatches)->toBe([]);
});
