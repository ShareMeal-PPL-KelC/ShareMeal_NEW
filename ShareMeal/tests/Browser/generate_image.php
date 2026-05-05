<?php
$img = imagecreatetruecolor(1, 1);
imagepng($img, __DIR__ . '/dummy.png');
echo "Done";
