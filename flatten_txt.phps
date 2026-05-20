<?php

$inputFile  = __DIR__ . '/FoundGUIDs.txt';
$outputFile = __DIR__ . '/FoundGUIDs.txt';

$lines = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$data = [];

/**
 * Zerlegt "A or B or C" in Array
 */
function splitValues($value) {
    $parts = explode(' or ', $value);
    return array_map('trim', $parts);
}

/**
 * Prüft Priorität:
 * IID_ > CLSID_ > normal
 */
function getPriority($value) {
    if (str_starts_with($value, 'IID_')) return 3;
    if (str_starts_with($value, 'CLSID_')) return 2;
    return 1;
}

/**
 * Basisname ohne Prefix
 */
function normalizeBase($value) {
    return preg_replace('/^(IID_|CLSID_)/', '', $value);
}

// 1) Einlesen + erstes Flattening
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;

    [$key, $value] = array_map('trim', explode('=', $line, 2));

	$key = strtoupper($key);

    $values = splitValues($value);

    if (!isset($data[$key])) {
        $data[$key] = [];
    }

    foreach ($values as $v) {
        if ($v !== '') {
            $data[$key][] = $v;
        }
    }
}

$output = [];

ksort($data);

// 2) Verarbeitung pro Key
foreach ($data as $key => $values) {

    // unique roh
    $values = array_values(array_unique($values));

    // Konfliktlösung über Basisnamen
    $map = []; // base => bestValue

    foreach ($values as $v) {
		if ($v == '?') continue;
		if ($v == '(Error)') continue;

        $base = normalizeBase($v);

        if (!isset($map[$base])) {
            $map[$base] = $v;
            continue;
        }

        $existing = $map[$base];

        // Priorität vergleichen
        if (getPriority($v) > getPriority($existing)) {
            $map[$base] = $v;
        }
    }

    $final = array_values($map);

    // final unique nochmal sauber
    $final = array_values(array_unique($final));

    if (count($final) === 0) {
       	$output[] = $key . '=?';
	} else if (count($final) === 1) {
		if ($final[0] == '') {
        	$output[] = $key . '=?';
		} else {
        	$output[] = $key . '=' . $final[0];
		}
    } else {
        $output[] = $key . '=' . implode(' or ', $final);
    }
}

// 3) Schreiben
file_put_contents($outputFile, implode(PHP_EOL, $output));

echo "Done.\n";
