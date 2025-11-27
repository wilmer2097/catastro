<?php
function buscarBom($dir) {
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;
        if (is_dir($path)) {
            buscarBom($path);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $h = fopen($path, 'r');
            $bytes = fread($h, 3);
            fclose($h);
            if ($bytes === "\xEF\xBB\xBF") {
                echo "BOM encontrado en: $path<br>";
            }
        }
    }
}
buscarBom(__DIR__);
