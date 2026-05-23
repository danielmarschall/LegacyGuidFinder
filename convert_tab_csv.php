<?php

/*
This script reads a text file containing media subtype names and GUIDs, converts each GUID to uppercase with curly braces, and writes the result in the format:

{GUID} = NAME

The converted entries are saved to a new output file.
*/

$inputFile = "input.txt";
$outputFile = "output.txt";

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

file_put_contents($outputFile, implode(PHP_EOL, $output));

echo "Fertig.\n";

