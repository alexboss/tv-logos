<?php

/**
 * PHP script to generate all logos mosaics.
 * Can only be run from CLI.
 * Usage:
 * Open a terminal, access the root of tv-logos repository and run:
 * php utilities/generate-all-logos-mosaics.php
 *
 * Tested with PHP 8.4.5 (cli).
 * ⚠️ Script comes with no warranty, use at your own risk.
 */

error_reporting(E_ALL);

// Script should be run from CLI only.
if (PHP_SAPI !== 'cli') {
    die("This script must be ran from the command line,");
}

// Global $settings.
$settings = array(
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

/**
 * List all files of a directory.
 * @param $dir string
 * @return false|array
 */
function listAllFiles(string $dir): false|array
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

/**
 * Group logos per country, and sort them ASC.
 * @param $logos array List of logos.
 * @param $source string Path to folder.
 * @return array
 */
function organizeContent(array $logos, string $source): array
{
    $output = array();

    foreach ($logos as $file) {
        $simplifiedPath = str_replace($source . DIRECTORY_SEPARATOR, '', $file);
        $chunkedPath = explode('/', $simplifiedPath);
        $country = array_shift($chunkedPath);
        if (!empty($country)) {
            $filename = array_pop($chunkedPath);
            $allowedExtensionsPattern = '/\.(png)/i';
            if (!empty($filename) && preg_match($allowedExtensionsPattern, $filename)) {
                $output[$country][preg_replace($allowedExtensionsPattern, '', $filename)] = join('/', array_merge($chunkedPath, [$filename]));
            }
        }
    }

    foreach ($output as &$countryArray) {
        ksort($countryArray);
    }

    return $output;
}

/** @noinspection RedundantSuppression */
/**
 * Create all MD files.
 * @param $logos array List of logos.
 * @param $source string Path to folder.
 * @return void
 */
function createMDFiles(array $logos, string $source): void
{
    global $settings;

    foreach ($logos as $country => $files) {
        $outputFile = $source . DIRECTORY_SEPARATOR . $country . DIRECTORY_SEPARATOR . $settings['outputFilename'];
        $depthForSpace = count(explode('/', preg_replace('/.+\/countries/', '', $source))) - 1;

        echo "Generating $outputFile\n";

        $outputContent = "";

        /** @noinspection PhpConcatenationWithEmptyStringCanBeInlinedInspection */
        $outputContent .= sprintf("# %s %s\n", ucwords(str_replace('-', ' ', $country)), $settings['flags'][$country]);
        $outputContent .= "\n";

        $table = "";
        $matrix = array();
        $list = "";
        $i = 0;
        foreach ($files as $fileKey => $file) {
            $matrix[intdiv($i, $settings['cols'])][] = $fileKey;
            $list .= "[$fileKey]:$file\n";
            $i++;
        }

        for ($j = 0; $j < count($matrix); $j++) {
            for ($i = 0; $i < $settings['cols']; $i++) {
                $table .= "| ![" . (($matrix[$j][$i]) ?? "space") . "] ";
                if ($i === $settings['cols'] - 1) {
                    $table .= "|\n";
                }
            }

            if ($j === 0) {
                for ($i = 0; $i < $settings['cols']; $i++) {
                    $table .= "|:---:";
                    if ($i === $settings['cols'] - 1) {
                        $table .= "|\n";
                    }
                }
            }

        }

        for ($i = 0; $i < $settings['cols']; $i++) {
            $table .= "| ![space]";
            if ($i === $settings['cols'] - 1) {
                $table .= "|\n";
            }
        }

        $extraLevels = str_repeat("../", $depthForSpace);

        $outputContent .= "$table\n";
        $outputContent .= "\n";
        $outputContent .= "$list\n";
        $outputContent .= "[space]:$extraLevels../../misc/space-1500.png\n";
        $outputContent .= "\n";

        file_put_contents($outputFile, $outputContent);
    }
}

/**
 * Generate all logos mosaics MD files.
 * @return void
 */
function generateAllLogosMosaics(): void
{
    global $settings;

    foreach ($settings['countriesFolders'] as $source) {
        $logos = listAllFiles($source);
        $logos = organizeContent($logos, $source);
        createMDFiles($logos, $source);
    }
}

// Fire !
generateAllLogosMosaics();
