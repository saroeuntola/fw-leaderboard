<?php
// =====================
// CACHE CONFIG
// =====================
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0755, true);

function apiCache(string $cacheFile, int $ttl, callable $callback)
{
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
    return json_decode(file_get_contents($cacheFile), true);
    }

    $data=$callback();
    file_put_contents($cacheFile, json_encode($data));
    return $data;
    }