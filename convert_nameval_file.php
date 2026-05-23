<?php

$inputFile = __DIR__ . "/convert_nameval_file_input.txt";
$outputFile = __DIR__ . "/FoundGUIDs.txt";

$lines = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$output = [];

foreach ($lines as $line) {

    // Erwartetes Format:
    // NAME:XYZ VALUE:1234-5678-....

    if (preg_match('/NAME:(.+?)\s+VALUE:([a-f0-9\-]+)/i', trim($line), $matches)) {

        $name = trim($matches[1]);
        $guid = trim($matches[2]);

        // GUID in Uppercase + geschweifte Klammern
        $guid = '{' . strtoupper($guid) . '}';

        // Ausgabeformat
        $output[] = $guid . '=' . $name;
    }
}

file_put_contents(
    $outputFile,
    implode(PHP_EOL, $output) . PHP_EOL,
    FILE_APPEND
);

// Optional: Datei zurücksetzen / Beispielinhalt einfügen
file_put_contents(
    $inputFile,
    "NAME:IUnknown VALUE:00000000-0000-0000-c000-000000000046\n"
);

include __DIR__ . '/flatten_txt.phps';

