<?php
$path = __DIR__ . '/resources/lang/ar.json';
$cachePath = __DIR__ . '/resources/lang/.ar_translate_cache.json';

$data = json_decode(file_get_contents($path), true);
if (!is_array($data)) { fwrite(STDERR, "Invalid ar.json\n"); exit(1); }

$cache = [];
if (file_exists($cachePath)) {
    $tmp = json_decode(file_get_contents($cachePath), true);
    if (is_array($tmp)) { $cache = $tmp; }
}

function shouldTranslate($text) {
    if (!is_string($text) || $text === '') return false;
    if (!preg_match('/[A-Za-z]/', $text)) return false;
    if (preg_match('/^\s*https?:\/\//i', $text)) return false;
    if (preg_match('/^[#\/\\\-_:\s\d\*\(\)\[\]\{\}\.%+,]+$/', $text)) return false;
    return true;
}

function translateText($text) {
    $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=ar&dt=t&q=' . rawurlencode($text);
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15,
            'header' => "User-Agent: Mozilla/5.0\r\n",
        ],
    ]);
    $res = @file_get_contents($url, false, $ctx);
    if ($res === false) return null;
    $json = json_decode($res, true);
    if (!is_array($json) || !isset($json[0]) || !is_array($json[0])) return null;
    $out = '';
    foreach ($json[0] as $part) {
        if (is_array($part) && isset($part[0])) $out .= $part[0];
    }
    $out = trim($out);
    return $out === '' ? null : $out;
}

$processed = 0;
$translated = 0;
$failed = 0;

foreach ($data as $k => $v) {
    if (!shouldTranslate($v)) continue;
    $processed++;

    if (isset($cache[$v])) {
        $data[$k] = $cache[$v];
        $translated++;
        continue;
    }

    $t = translateText($v);
    if ($t === null) {
        $failed++;
        continue;
    }

    $cache[$v] = $t;
    $data[$k] = $t;
    $translated++;

    if (($translated % 50) === 0) {
        file_put_contents($cachePath, json_encode($cache, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    usleep(120000);
}

file_put_contents($cachePath, json_encode($cache, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);

echo "processed={$processed}, translated={$translated}, failed={$failed}\n";
?>
