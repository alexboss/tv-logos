<?php

error_reporting(E_ALL);

if (PHP_SAPI !== 'cli') {
    die("This script must be ran from the command line,");
}

$globals = array(
    'countriesFolder' => __DIR__ . '/../countries',
    'outputFilename' => '1_all_logos_mosaic.md',
    'cols' => 6,
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

function organizeContent($files)
{
    $output = array();

    foreach ($files as $file) {
        $simplifiedPath = preg_replace('/.+\/\.\.\/countries\//', '', $file);
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

function createMosaics($input)
{
    global $globals;
    foreach ($input as $country => $files) {
        $outputFile = $globals['countriesFolder'] . DIRECTORY_SEPARATOR . $country . DIRECTORY_SEPARATOR . $globals['outputFilename'];
        echo $outputFile . "\n";

        $outputContent = "";

        $outputContent .= "# $country + emojiflag\n";
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

        $outputContent .= "$table\n";
        $outputContent .= "\n";
        $outputContent .= "$list\n";
        $outputContent .= "[space]:../../misc/space-1500.png\n";
        $outputContent .= "\n";

        echo $outputContent;

        file_put_contents($outputFile, $outputContent);
    }
}


$files = listAllFiles($globals['countriesFolder']);
$files = organizeContent($files);
createMosaics($files);


//print_r($files);
