<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>PDF Reader Text Example</title>
</head>
<body>
<?php
/**
 * PDFTextExample extracts text strings from a PDF file and prints them out.
 * 
 * This is an example of PDF Reader's text extraction routine. It extracts
 * strings from a server side PDF file (uploading the file and providing the
 * file path to PDF reader is your responsibility), and returns them as an
 * array. It then prints the array, one row per string.
 * 
 * PHP version 5
 * 
 * @category  File_Formats
 * @package   PDF_Reader
 * @author    John M. Stokes <jstokes@heartofthefyre.us>
 * @copyright 2010 John M. Stokes
 * @license   http://www.opensource.org/licenses/bsd-license.html BSD Style License
 * @link      http://heartofthefyre.us/PDFreader/index.php
 */

require_once '../File/PDFreader.class.php';

$PDF = new PDFreader();
try {
    $PDF->open('/path/to/PDF/File/example.pdf');
    $text = $PDF->readText();
}
catch(PdfException $e) {
    echo '<p style="color: #FF0000; font-weight: bold; text-align: center;">';
    echo "$e</p>\n";
}

echo "<h2>Decoded text</h2>
<p>\n";
foreach ($text as $row) {
    echo "$row<br />\n";
}
echo "</p>\n";
?>
</body>
</html>