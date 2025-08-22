<?php

error_reporting(E_ALL);

if (PHP_SAPI !== 'cli') {
    die("This script must be ran from the command line,");
}

$globals = array(
    'countriesFolders' => array(
        __DIR__ . '/../countries',
        __DIR__ . '/../countries/nordic',
    ),
    'outputFilename' => '0_all_logos_mosaic.md',
    'cols' => 6,
    'flags' => array(
        'albania' => '🇦🇱',
        'argentina' => '🇦🇷',
        'australia' => '🇦🇺',
        'austria' => '🇦🇹',
        'azerbaijan' => '🇦🇿',
        'belgium' => '🇧🇪',
        'brazil' => '🇧🇷',
        'bulgaria' => '🇧🇬',
        'canada' => '🇨🇦',
        'caribbean' => '🏝️',
        'chile' => '🇨🇱',
        'costa-rica' => '🇨🇷',
        'croatia' => '🇭🇷',
        'czech-republic' => '🇨🇿',
        'denmark' => '🇩🇰',
        'finland' => '🇫🇮',
        'france' => '🇫🇷',
        'germany' => '🇩🇪',
        'greece' => '🇬🇷',
        'hong-kong' => '🇭🇰',
        'hungary' => '🇭🇺',
        'iceland' => '🇮🇸',
        'india' => '🇮🇳',
        'indonesia' => '🇮🇩',
        'international' => '🗺️',
        'israel' => '🇮🇱',
        'italy' => '🇮🇹',
        'jamaica' => '🇯🇲',
        'lebanon' => '🇱🇧',
        'lithuania' => '🇱🇹',
        'luxembourg' => '🇱🇺',
        'malaysia' => '🇲🇾',
        'malta' => '🇲🇹',
        'mexico' => '🇲🇽',
        'netherlands' => '🇳🇱',
        'new-zealand' => '🇳🇿',
        'nordic' => '🏔️',
        'norway' => '🇳🇴',
        'philippines' => '🇵🇭',
        'poland' => '🇵🇱',
        'portugal' => '🇵🇹',
        'romania' => '🇷🇴',
        'russia' => '🇷🇺',
        'serbia' => '🇷🇸',
        'singapore' => '🇸🇬',
        'slovakia' => '🇸🇰',
        'slovenia' => '🇸🇮',
        'south-africa' => '🇿🇦',
        'spain' => '🇪🇸',
        'sweden' => '🇸🇪',
        'switzerland' => '🇨🇭',
        'turkey' => '🇹🇷',
        'ukraine' => '🇺🇦',
        'united-arab-emirates' => '🇦🇪',
        'united-kingdom' => '🇬🇧',
        'united-states' => '🇺🇸',
        'world-africa' => '🌍',
        'world-asia' => '🌏',
        'world-europe' => '🌍',
        'world-latin-america' => '🌎',
        'world-middle-east' => '🌍',
    ),
);

function listAllFiles($dir)
{
    $array = array_diff(scandir($dir), array('.', '..'));

    foreach ($array as &$item) {
        $item = $dir . DIRECTORY_SEPARATOR . $item;
    }
    unset($item);
    foreach ($array as $item) {
        if (is_dir($item)) {
            $array = array_merge($array, listAllFiles($item));
        }
    }
    return $array;
}

function organizeContent($files, $source)
{
    $output = array();

    foreach ($files as $file) {
        $simplifiedPath = str_replace($source . DIRECTORY_SEPARATOR, '', $file);
        $chunkedPath = explode('/', $simplifiedPath);
        $country = array_shift($chunkedPath);
        if (!empty($country)) {
            $filename = array_pop($chunkedPath);
            if (!empty($filename) && preg_match('/\.(png)/i', $filename)) {
                $output[$country][preg_replace('/\.(png)/i', '', $filename)] = join('/', array_merge($chunkedPath, [$filename]));
            }
        }
    }

    foreach ($output as &$countryArray) {
        ksort($countryArray);
    }

    return $output;
}

function createMDFiles($input, $destination)
{
    global $globals;
    foreach ($input as $country => $files) {
        $outputFile = $destination . DIRECTORY_SEPARATOR . $country . DIRECTORY_SEPARATOR . $globals['outputFilename'];
        $depthForSpace = count(explode('/', preg_replace('/.+\/countries/', '', $destination))) - 1;
        echo $outputFile . "\n";

        $outputContent = "";

        $outputContent .= sprintf("# %s %s\n", ucwords(str_replace('-', ' ', $country)), $globals['flags'][$country]);
        $outputContent .= "\n";

        $table = "";
        $matrix = array();
        $list = "";
        $i = 0;
        foreach ($files as $fileKey => $file) {
            $matrix[intdiv($i, $globals['cols'])][] = $fileKey;
            $list .= "[$fileKey]:$file\n";
            $i++;
        }

        for ($i = 0; $i < $globals['cols']; $i++) {
            $table .= "| ![" . (($matrix[0][$i]) ?? "space") . "] ";
            if ($i === $globals['cols'] - 1) {
                $table .= "|\n";
            }
        }

        for ($i = 0; $i < $globals['cols']; $i++) {
            $table .= "|:---:";
            if ($i === $globals['cols'] - 1) {
                $table .= "|\n";
            }
        }

        for ($j = 1; $j < count($matrix); $j++) {
            for ($i = 0; $i < $globals['cols']; $i++) {
                $table .= "| ![" . (($matrix[$j][$i]) ?? "space") . "] ";
                if ($i === $globals['cols'] - 1) {
                    $table .= "|\n";
                }
            }
        }

        for ($i = 0; $i < $globals['cols']; $i++) {
            $table .= "| ![space]";
            if ($i === $globals['cols'] - 1) {
                $table .= "|\n";
            }
        }

        $extraLevels = "";
        for ($i = 0; $i < $depthForSpace; $i++) {
            $extraLevels .= "../";
        }

        $outputContent .= "$table\n";
        $outputContent .= "\n";
        $outputContent .= "$list\n";
        $outputContent .= "[space]:$extraLevels../../misc/space-1500.png\n";
        $outputContent .= "\n";

        //echo $outputContent;

        file_put_contents($outputFile, $outputContent);
    }
}

function generateAllLogosMosaic()
{
    global $globals;

    foreach ($globals['countriesFolders'] as $folder) {
        $files = listAllFiles($folder);
        $files = organizeContent($files, $folder);
        createMDFiles($files, $folder);
    }
}

generateAllLogosMosaic();