<?php
$imagePath = 'C:\\Users\\haser\\.gemini\\antigravity\\brain\\6fe9fd2f-8538-44d9-85ba-434fba9d58ca\\media__1781648941178.png';
$outputDir = 'C:\\Users\\haser\\.gemini\\antigravity\\scratch\\corner-flag-arena\\assets';
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}
$outputPath = $outputDir . '\\logo.png';

if (!file_exists($imagePath)) {
    die("Error: Source image not found at $imagePath\n");
}

$img = imagecreatefrompng($imagePath);
if (!$img) {
    die("Error: Failed to open image.\n");
}

$width = imagesx($img);
$height = imagesy($img);
echo "Image dimensions: {$width}x{$height}\n";

// Coordinates for Jersey logo (chest, top-right)
// Est: Left = 54%, Top = 15.5%, Width = 20%, Height = 12.5% of overall dimensions.
$left = (int)($width * 0.54);
$top = (int)($height * 0.155);
$w = (int)($width * 0.20);
$h = (int)($height * 0.125);

echo "Cropping box: x={$left}, y={$top}, w={$w}, h={$h}\n";

$cropped = imagecrop($img, ['x' => $left, 'y' => $top, 'width' => $w, 'height' => $h]);
if ($cropped !== false) {
    imagepng($cropped, $outputPath);
    imagedestroy($cropped);
    echo "Cropped logo saved successfully to $outputPath\n";
} else {
    echo "Error: Crop failed.\n";
}

imagedestroy($img);
?>
