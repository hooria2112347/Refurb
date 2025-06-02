<?php
// download_images.php

$imageData = [
    ['url' => 'https://images.unsplash.com/photo-1581091012184-5c1d7b6f5c4e', 'filename' => 'iron_scrap.jpg'],
    ['url' => 'https://images.unsplash.com/photo-1607082349250-0f3c5f3f3f3f', 'filename' => 'copper_wire.jpg'],
    ['url' => 'https://images.unsplash.com/photo-1607082349250-0f3c5f3f3f3f', 'filename' => 'aluminum_cans.jpg'],
    ['url' => 'https://images.unsplash.com/photo-1607082349250-0f3c5f3f3f3f', 'filename' => 'stainless_steel_pipes.jpg'],
    ['url' => 'https://images.unsplash.com/photo-1607082349250-0f3c5f3f3f3f', 'filename' => 'lead_battery.jpg'],
];

$imagesDir = __DIR__ . '/public/images/';

if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
    echo "Created images directory: $imagesDir\n";
}

foreach ($imageData as $image) {
    $imageContent = file_get_contents($image['url']);
    if ($imageContent !== false) {
        file_put_contents($imagesDir . $image['filename'], $imageContent);
        echo "Downloaded and saved: " . $image['filename'] . "\n";
    } else {
        echo "Failed to download: " . $image['filename'] . "\n";
    }
}
?>
