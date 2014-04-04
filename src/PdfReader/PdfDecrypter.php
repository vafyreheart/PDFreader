<?php
namespace PdfReader;
/**
 * PDFdecrypt.class.php does the hard work of decrypting streams
 *
 * PHP version 5.1
 *
 * @category  File_Formats
 * @package   File_PDFreader
 * @author    Cambell Prince <cambell.prince@gmail.com>
 * @copyright 2014 John M. Stokes
 * @license   http://www.opensource.org/licenses/bsd-license.html BSD Style License
 * @link      http://heartofthefyre.us/PDFreader/index.php
 */

/**
 * I include one class per file, so the file description is the class's description.
 *
 * @category  File_Formats
 * @package   File_PDFreader
 * @author    Cambell Prince <cambell.prince@gmail.com>
 * @copyright 2014 John M. Stokes
 * @license   http://www.opensource.org/licenses/bsd-license.html BSD Style License
 * @link      http://heartofthefyre.us/PDFreader/index.php
 */
class PdfDecrypter
{
	private $_filterDictionary;
	
	public function __construct($filterDictionary) {
		$this->_filterDictionary = $filterDictionary;
		
	}
	
	public function key() {
		$padString  = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08";
		$padString .= "\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
		
		$input  = $padString;
		$input .= $this->unpackString($this->_filterDictionary['O']);
		
		$p = (int)$this->_filterDictionary['P'];
		for ($i = 0; $i < 4; $i++) {
			$input .= $p & 0xFF;
			$p = $p >> 8;
		}
		
		
	}
	
	public static function unpackString($s) {
		$l = count($s);
		if ($s[0] == '(' && $s[count($s) - 1] == ')') {
			$s = substr($s, 1, $l - 2);
		}
		return $s;
	}
	
}


?>