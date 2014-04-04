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

require_once 'PDFreader/PDFreader/File/PDFreader.class.php';

echo '<p>Start: '.date('Y-m-d H:i:s')."</p>\n";

try {
	$PDF = new PDFreader();
	$PDF->open('TestPDFs/DIRECT_DISH.pdf');
	
	$textValues = $PDF->readText();
	$formValues = parseForm($textValues);
	foreach ($formValues as $key=>$value) {
		echo "<b>$key</b> $value<br />\n";
	}
	
//	for ($page=1; $page<=120; $page++) {
//		echo "<h1>Page $page</h1>";
//		$textValues = $PDF->readTextByPage($page);
//		$formValues = parseForm($textValues);
//		foreach ($formValues as $key=>$value) {
//			echo "<b>$key</b> $value<br />\n";
//		}
//	}

}
catch (PdfException $e) {
	echo '<p class="errorMsg">'.$e.'</p>';
}

echo '<p>End: '.date('Y-m-d H:i:s')."</p>\n";

function parseForm($textArray) {
	$text = '';
	foreach ($textArray as $t) {
		$text .= $t;
	}
	echo "<p>$text</p>\n";
	
	$keys = array(
		'Company:',
		'IV Retest Enforced:',
		'Tech ID:',
		'Name:',
		'Account #:',
		'Service Region:',
		'AddressLine1:',
		'AddressLine2:',
		'City,State,Zip:',
		'Order Type:',
		'Earliest Start:',
		'Sub Type:',
		'Planned Start:',
		'SR Sub Area:',
		'Due:',
		'Primary Phone Number:',
		'Order Class:',
		'Dwelling:',
		'Secondary PhoneNumber:',
		'Duration:',
		'OMS Order ID:',
		'MAS Programming:',
		'Status:',
		'Property ID:',
		'Priority:',
		'Activity #:',
		'40Ft Ladder',
		'Partner:'
	);
	
	$keys = array(
		'Company',
		'IV Retest Enforced',
		'Tech ID',
		'Name',
		'Account',
		'Service Region',
		'AddressLine1',
		'AddressLine2',
		'City,State,Zip',
		'Order Type',
		'Earliest Start',
		'Sub Type',
		'Planned Start',
		'SR Sub Area',
		'Due',
		'Primary Phone Number',
		'Order Class',
		'Dwelling',
		'Secondary Phone Number',
		'Duration',
		'OMS Order ID',
		'MAS Programming',
		'Status',
		'Property ID',
		'Priority',
		'Activity #',
		'40Ft Ladder',
		'Partner',
		'Account Type',
		'Comments',
		'Resolutions/ Tech Driving',
		'SWM Flag',
		'Mode',
		'Compatibility',
		'Product Line Items',
		'AccessCardAC TypeCategoryTechAction Taken',
		'Tech Instructions',
		'I hereby acknowledge'
	);
	
	
	
	$t = array();
	for ($i=0; $i<count($keys)-1; $i++) {
		$k = $keys[$i];
		$k1 = $keys[$i+1];
		
		$value = substr($text, strpos($text, $k)+strlen($k));
		$value = substr($value, 0, strpos($value, $k1));
		
		if ($k == 'Company')
			$value = str_replace('Customer Information', '', $value);
		else if ($k == 'City,State,Zip:') {
			$acsz = extractAddress($value);
			$t['AddressLine1'] = $acsz['address'];
			$t['City'] = $acsz['city'];
			$t['State'] = $acsz['state'];
			$t['Zip'] = $acsz['zip'];
		}
		
		$t[$k] = $value;
	}//Close for
	
	return $t;
}//End parseForm

function extractAddress($address) {
	$matches = array();
	$StatePattern = '/, ([A-Z]{2})/';
	$ZipPattern = '/([0-9]{5})/';
	$endings = array('BLVD','CIR','CT','DR','LN','RD','ST');
	
	preg_match($StatePattern, $address, $matches);
	$state = $matches[1];
	
	preg_match($ZipPattern, $address, $matches);
	$zip = $matches[1];
	
	$address = substr($address, 0, strrpos($address, ','));
	foreach ($endings as $ending) {
		if (strpos($address, $ending) === false)
			continue;
	
		$position = strpos($address, $ending)+strlen($ending);
		$street = substr($address, 0, $position);
		$city = substr($address, $position);
	}//Close foreach $ending
	
	$acsz = array('address'=>$street, 'city'=>$city, 'state'=>$state, 'zip'=>$zip);

	return $acsz;
}//End extractAddress
?>
</body>
</html>