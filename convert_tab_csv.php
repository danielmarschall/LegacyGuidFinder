<?php

$inputFile = __DIR__ . "/convert_tab_csv_input.txt";
$outputFile = __DIR__ . "/FoundGUIDs.txt";

$lines = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$output = [];

foreach ($lines as $line) {
    // Aufteilen nach beliebigen Leerzeichen/Tabs
    [$name, $guid] = preg_split('/\s+/', trim($line));

    // GUID in Uppercase + geschweifte Klammern
    $guid = '{' . strtoupper($guid) . '}';

    // Ausgabeformat
    $output[] = $guid . '=' . $name;
}

file_put_contents($outputFile, implode(PHP_EOL, $output) . PHP_EOL, FILE_APPEND);
file_put_contents($inputFile, "IUnknown        00000000-0000-0000-c000-000000000046\n");

include __DIR__ . '/flatten_txt.phps';
