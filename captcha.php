<?php

	header("Content-Type: image/png");
	session_start();

	$_SESSION['captcha'] = substr(md5(rand(10000, 2147483647)), -8);

	$fontList = array
	(
		"arial.ttf", "arialbd.ttf", "arialbi.ttf", "ariali.ttf", "ARIALN.TTF",
		"ARIALNB.TTF", "ARIALNBI.TTF", "ARIALNI.TTF", "ariblk.ttf", "consola.ttf",
		"consolab.ttf", "consolai.ttf", "consolaz.ttf", "micross.ttf", "pala.ttf",
		"palab.ttf", "palabi.ttf", "palai.ttf", "PAPYRUS.TTF", "segoeui.ttf",
		"segoeuib.ttf", "segoeuii.ttf", "segoeuil.ttf", "segoeuiz.ttf", "seguisb.ttf"
	);

	$imageHandle = imagecreatefromjpeg("images/captcha.jpg");
	$imageTextColor = imagecolorallocate($imageHandle, rand(50, 150), rand(50, 150), rand(50, 150));

	imagettftext($imageHandle, 35, 0, 10, 50, $imageTextColor, "ttffonts/".$fontList[array_rand($fontList)], "" . $_SESSION['captcha'] . "");

	imagepng($imageHandle);
	imagedestroy($imageHandle);

?>