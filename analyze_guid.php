<?php

function guidToCppDefine(string $uuid, string $name = 'IID_...'): string
{
    // lowercase + {} und - entfernen
    $clean = strtolower($uuid);
    $clean = str_replace(['{', '}', '-'], '', $clean);

    if (strlen($clean) !== 32) {
        return "Ungültige GUID";
    }

    // GUID Teile
    $data1 = substr($clean, 0, 8);
    $data2 = substr($clean, 8, 4);
    $data3 = substr($clean, 12, 4);

    // letzte 8 Bytes
    $tail = substr($clean, 16);

    $bytes = [];
    for ($i = 0; $i < strlen($tail); $i += 2) {
        $bytes[] = '0x' . strtoupper(substr($tail, $i, 2));
    }

    return sprintf(
        'DEFINE_GUID(%s, 0x%sL, 0x%s, 0x%s, %s)',
        $name,
        strtoupper($data1),
        strtoupper($data2),
        strtoupper($data3),
        implode(', ', $bytes)
    );
}






function analyzeGuid(string $uuid)
{
    // lowercase + {} und - entfernen
    $clean = strtolower($uuid);
    $clean = str_replace(['{', '}', '-'], '', $clean);

    if (strlen($clean) !== 32) {
        return "Ungültige GUID";
    }

    // GUID wieder normal formatiert
    $guid = sprintf(
        '%s-%s-%s-%s-%s',
        substr($clean, 0, 8),
        substr($clean, 8, 4),
        substr($clean, 12, 4),
        substr($clean, 16, 4),
        substr($clean, 20, 12)
    );

    $guidUpper = strtoupper($guid);




	// ------------------------------------------------------------
	// OLE GUID
	// xxxxxxxx-yyyy-zzzz-c000-000000000046
	// ------------------------------------------------------------
	if (preg_match('/^([0-9a-f]{8})-([0-9a-f]{4})-([0-9a-f]{4})-c000-000000000046$/i', $guid, $m)) {
		echo sprintf("%-32s %s\n", "Microsoft Type:", "OLE GUID (xxxxxxxx-yyyy-zzzz-c000-000000000046)");
		echo sprintf("%-32s DEFINE_OLEGUID(<name>, 0x%sL, 0x%s, 0x%s)\n", "Win32 API Definition:",
			strtoupper($m[1]),
			strtoupper($m[2]),
			strtoupper($m[3])
		);
	}

	// ------------------------------------------------------------
	// DAO GUID
	// xxxxxxxx-0000-0010-8000-00aa006d2ea4
	// ------------------------------------------------------------
	if (preg_match('/^([0-9a-f]{8})-0000-0010-8000-00aa006d2ea4$/i', $guid, $m)) {
		echo sprintf("%-32s %s\n", "Microsoft Type:", "DAO GUID (xxxxxxxx-0000-0010-8000-00aa006d2ea4)");
		echo sprintf("%-32s DEFINE_DAOGUID(<name>, 0x%sL)\n", "Win32 API Definition:",
			strtoupper($m[1])
		);
	}

	// ------------------------------------------------------------
	// MediaSubtype
	// xxxxxxxx-0000-0010-8000-00aa00389b71
	// ------------------------------------------------------------
	if (preg_match('/^([0-9a-f]{8})-0000-0010-8000-00aa00389b71$/i', $guid, $m)) {
		$value = strtoupper($m[1]);
		$bytes = [
			hexdec(substr($value, 6, 2)),
			hexdec(substr($value, 4, 2)),
			hexdec(substr($value, 2, 2)),
			hexdec(substr($value, 0, 2)),
		];
		$hasZero = in_array(0, $bytes, true);
		echo sprintf("%-32s %s\n", "Microsoft Type:", "MediaSubtype GUID (xxxxxxxx-0000-0010-8000-00aa00389b71)");
		if (!$hasZero) {
			$fourcc =
				chr($bytes[0]) .
				chr($bytes[1]) .
				chr($bytes[2]) .
				chr($bytes[3]);
			echo sprintf("%-32s %s\n", "Subtype FourCC:", $fourcc);
			echo sprintf("%-32s %s\n", "Win32 API Definition:", guidToCppDefine($guid, 'WMMEDIASUBTYPE_'.$fourcc));
		} else {
			$magic =
				  ($bytes[3] << 24)
				| ($bytes[2] << 16)
				| ($bytes[1] << 8)
				|  $bytes[0];
			echo sprintf("%-32s %u (0x%X)\n", "Subtype Number:", $magic, $magic);
			echo sprintf("%-32s %s\n", "Win32 API Definition:", guidToCppDefine($guid, 'WMMEDIASUBTYPE_...'));
		}
	}




}


// ------------------------------------------------------------
// Beispiele
// ------------------------------------------------------------

echo analyzeGuid('{00000000-0000-0000-C000-000000000046}') . PHP_EOL;

echo analyzeGuid('00000010-0000-0010-8000-00AA006D2EA4') . PHP_EOL;

echo analyzeGuid('32595559-0000-0010-8000-00AA00389B71') . PHP_EOL;

echo analyzeGuid('00000055-0000-0010-8000-00AA00389B71') . PHP_EOL;

