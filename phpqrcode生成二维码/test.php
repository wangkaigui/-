<?php

	include 'phpqrcode.php'; 
	
	$value = "http://www.baidu.com";
	$errorCorrectionLevel = 'L';
	$matrixPointSize = 10;

	$time=date("y-m");
	$com = date("d");
	
	$file_name = date("YmdHis").mt_rand(1000,9999).".png";
	QRcode::png($value, $file_name, $errorCorrectionLevel, $matrixPointSize, 2);
	$logo = "http://bos.bj.baidubce.com/v1/whty/2019/04/29/longchengjiaoyu.png";//准备好的logo图片
	$QR = $file_name;
	if ($logo !== FALSE) {
		$QR = imagecreatefromstring(file_get_contents($QR));
		$logo = imagecreatefromstring(file_get_contents($logo));
		$QR_width = imagesx($QR);//二维码图片宽度
		$QR_height = imagesy($QR);//二维码图片高度
		$logo_width = imagesx($logo);//logo图片宽度
		$logo_height = imagesy($logo);//logo图片高度
		$logo_qr_width = $QR_width / 5;
		$scale = $logo_width/$logo_qr_width;
		$logo_qr_height = $logo_height/$scale;
		$from_width = ($QR_width - $logo_qr_width) / 2;
		//重新组合图片并调整大小
		imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,$logo_qr_height, $logo_width, $logo_height);
	}
	//输出图片
	header("Content-type: image/png");
	imagepng($QR);
	imagepng($QR);
	//echo $file_name;	
	
	