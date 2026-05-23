<?php

function normalizeHex(string $x): string
{
    $x = trim($x);

    // L/l am Ende entfernen
    $x = preg_replace('/L$/i', '', $x);

    // Hat bereits 0x Prefix?
    if (stripos($x, '0x') === 0) {
        return substr($x, 2);
    }

    // Sonst als Dezimalzahl interpretieren und nach Hex wandeln
    return strtoupper(dechex((int)$x));
}

/**
 * Liest eine Datei aus und extrahiert DEFINE_OLEGUID(...)
 * Beispiele:
 *   DEFINE_OLEGUID(PS_ROUTING_ENTRYID,0x00020383,0,0);
 *
 * Ausgabe:
 *   PS_ROUTING_ENTRYID={00020383-0000-0000-C000-000000000046}
 */

function extractOleGuids(string $filename): array
{
    $content = file_get_contents($filename);

    if ($content === false) {
        throw new RuntimeException("Datei konnte nicht gelesen werden: $filename");
    }

    $result = [];

    // Leerzeichen/Zeilenumbrüche egal
    $pattern = '/DEFINE_OLEGUID\s*\(\s*' .
        '([A-Za-z0-9_]+)\s*,\s*' .   // Name
        '(0x?[0-9A-Fa-f]*)\s*,\s*' .  // XXXXXXXX
        '(0x?[0-9A-Fa-f]*)\s*,\s*' . // YYYY
        '(0x?[0-9A-Fa-f]*)\s*' .     // ZZZZ
        '\)\s*;/';

    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $name = $m[1];

        $part1 = strtoupper(str_pad(normalizeHex($m[2]), 8, '0', STR_PAD_LEFT));
        $part2 = strtoupper(str_pad(normalizeHex($m[3]), 4, '0', STR_PAD_LEFT));
        $part3 = strtoupper(str_pad(normalizeHex($m[4]), 4, '0', STR_PAD_LEFT));

        $guid = sprintf(
            '{%s-%s-%s-C000-000000000046}',
            $part1,
            $part2,
            $part3
        );

        $result[$name] = $guid;
    }

    return $result;
}

function extractDaoGuids(string $filename): array
{
    $content = file_get_contents($filename);

    if ($content === false) {
        throw new RuntimeException("Datei konnte nicht gelesen werden: $filename");
    }

    $result = [];

    // Leerzeichen/Zeilenumbrüche egal
    $pattern = '/DEFINE_DAOGUID\s*\(\s*' .
        '([A-Za-z0-9_]+)\s*,\s*' .   // Name
        '(0x?[0-9A-Fa-f]*)\s*' .
        '\)\s*;/';

    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $name = $m[1];

        $part1 = strtoupper(str_pad(normalizeHex($m[2]), 8, '0', STR_PAD_LEFT));

        $guid = sprintf(
            '{%s-0000-0010-8000-00AA006D2EA4}',
            $part1
        );

        $result[$name] = $guid;
    }

    return $result;
}


// Beispiel
try {
    $guids = extractOleGuids('mapiguid.h');
    $guids = array_merge($guids,extractDaoGuids('mapiguid.h'));

    foreach ($guids as $name => $guid) {
        echo strtoupper($guid) . '=' . $name . PHP_EOL;
    }

} catch (Exception $e) {
    echo 'Fehler: ' . $e->getMessage();
}

