<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>PDF Reader Form Example</title>
	<style type="text/css">
		.errorMsg {color: #FF0000; font-weight: bold; font-size: 18px; text-align: center;}
	</style>
</head>
<body>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '2048M');
set_time_limit(3600);

require_once '../vendor/autoload.php';
//require_once 'buskirk/File/PDFreader.class.php';

try {
	$PDF = new PdfReader\PdfReader();

	if ($_GET['page'] == 1)
		$PDF->open('TestPDFs/MAIDefinitionOfTerms.pdf');
	else if ($_GET['page'] == 2)
		$PDF->open('buskirk/Buskirk11122.pdf');
	else if ($_GET['page'] == 3)
		$PDF->open('TestPDFs/KrushnaSample.pdf');
	else if ($_GET['page'] == 4)
		$PDF->open('TestPDFs/sumrad.pdf');
	else if ($_GET['page'] == 5)
		$PDF->open('TestPDFs/11-learning-research-english-next.pdf');
	else if ($_GET['page'] == 6)
		$PDF->open('TestPDFs/NationalBisonRange.pdf');
	else if ($_GET['page'] == 7)
		$PDF->open('TestPDFs/DIRECT_DISH_09142010.pdf');
	else if ($_GET['page'] == 8)
		$PDF->open('TestPDFs/encrypted.pdf');
	else
		$PDF->open('TestPDFs/pdf_that_does_not_work.pdf');
		
//	$textValues = $PDF->readTextByPage(67);
	if (isset($_GET['form']))
		$formValues = $PDF->readForm();
	else
		$textValues = $PDF->readText();
}
catch (PdfException $e) {
	echo '<p class="errorMsg">'.$e.'</p>';
}

echo "<h2>Text</h2>
<p>\n";
if (isset($textValues)) {
	foreach ($textValues as $text) {
	    echo "$text<br />\n";
	}
}
else
	echo "No text found\n";
echo '</p>';

echo "<h2>Form fields</h2>
<p>\n";
if (isset($formValues)) {
	foreach ($formValues as $key=>$value) {
	    echo "<strong>$key:</strong> $value<br />\n";
	}
}
else
	echo "No forms found";
echo '</p>';
?>
</body>
</html>