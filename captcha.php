<?php
session_start();

// Generar token aleatorio de 5 dígitos omitiendo caracteres ambiguos
$codigo_captcha = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
$_SESSION['captcha_texto'] = $codigo_captcha;

$imagen = imagecreate(120, 40);

// Colores adaptados
$bg_color = imagecolorallocate($imagen, 42, 42, 42); 
$text_color = imagecolorallocate($imagen, 255, 255, 255); 
$line_color = imagecolorallocate($imagen, 80, 80, 80);

// Generar líneas de ruido visual
for($i=0; $i<4; $i++) {
    imageline($imagen, 0, rand(0,40), 120, rand(0,40), $line_color);
}

imagestring($imagen, 5, 35, 12, $codigo_captcha, $text_color);

header("Content-Type: image/png");
imagepng($imagen);
imagedestroy($imagen);
?>