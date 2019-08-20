<?php
require_once('TCPDF/tcpdf.php'); 
//实例化 
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false); 
// 设置文档信息 
$pdf->SetCreator('Helloweba'); 
$pdf->SetAuthor('zzy'); 
$pdf->SetTitle('测试!'); 
$pdf->SetSubject('TCPDF Tutorial'); 
$pdf->SetKeywords('TCPDF, PDF, PHP'); 
// 设置页眉和页脚信息 
#$pdf->SetHeaderData('logo.png', 30, '', '',array(0,64,255), array(0,64,128)); 
$pdf->setFooterData(array(0,64,0), array(0,64,128)); 
// 设置页眉和页脚字体 
$pdf->setHeaderFont(Array('stsongstdlight', '', '10')); 
$pdf->setFooterFont(Array('helvetica', '', '8')); 
// 设置默认等宽字体 
$pdf->SetDefaultMonospacedFont('courier'); 
// 设置间距 
$pdf->SetMargins(15, 27, 15); 
$pdf->SetHeaderMargin(5); 
$pdf->SetFooterMargin(10); 
// 设置分页 
$pdf->SetAutoPageBreak(TRUE, 25); 
// set image scale factor 
$pdf->setImageScale(1.25); 
// set default font subsetting mode 
$pdf->setFontSubsetting(true); 
//设置字体 
$pdf->SetFont('stsongstdlight', '', 14); 
$pdf->AddPage(); 

$pdf->Write(0,'折线图',4, 0, 'L', true, 0, false, false, 0); 
$pdf->Image('example12.png', 10, 40, 200, 80, 'PNG', '', '', true, 150, '', false, false, 1, false, false, false);
//输出PDF 
$pdf->Output('t.pdf', 'I');
