<?php

const AIM_IMAGES = 150; // KB

$dir = $argv[1];

$images = scandir($dir);

print_r("Found " . count($images) - 3 . " images to treat in $dir/ - Treshold : " . AIM_IMAGES . "kB\n--------------------------------\n");

$checkpoint = time();

foreach ($images as $image) {
    if ($image != '.' && $image != '..') {
        $parts = explode('.', $image);
        $ext = end($parts);
        
        switch ($ext) {
            case 'svg':
                break;
            default:
                print_r("Treating $image");
                treat($image);
                break;
        }
    }
}

$elapsed = time() - $checkpoint;

print_r("--------------------------------\nDone in {$elapsed}s\n");

function treat($path) {
    global $dir;
    $path = "$dir/$path";
    $size = filesize($path) / 1000; // conversion en KB

    print_r(" : {$size}kB\n");

    $newSize = 0;
    
    if ($size > AIM_IMAGES) { // si l'image est trop volumineuse
        print_r("| Compressing\n");
        
        $width = 0; $height = 0;
        list($width, $height) = getimagesize($path);
        $ratio = sqrt(AIM_IMAGES / $size);
        
        $attempts = 0;
        do { // compression par tatons
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);
            exec("convert $path -resize {$newWidth}x{$newHeight} -quality 85 jpg:$path"); // compression et cast en jpg
            
            $newSize = filesize("$path") / 1000;
            
            if ($newSize > AIM_IMAGES) {
                $ratio *= 0.9;
            } else if ($newSize < AIM_IMAGES * 0.85) {
                $ratio *= 1.1;
            } else {
                break;
            }

            $attempts++;
        } while ($attempts < 5); // limité à 5 éssais pour que ce soit plus rapide

        $finalSize = filesize("$path") / 1000;
        print_r("| {$finalSize}kB\n");
    } else {
        print_r("| Skipping\n");
    }
}

?>