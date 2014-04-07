<?php
use PdfReader\PdfBase;

require_once(__DIR__ . '/../../vendor/autoload.php'); 
// require_once(__DIR__ . '/TestConfig.php');

class PdfBaseConcrete extends PdfBase
{
	public function extractArray($arrayString) {
		$this->iterations = 0;
		return parent::extractArray($arrayString);
	}
	
	public function extractDictionary($dictionaryString) {
		$this->iterations = 0;
		return parent::extractDictionary($dictionaryString);
	}
	
	public function extractString($string) {
		return parent::extractString($string);
	}

	
}

class PdfBaseTest extends PHPUnit_Framework_TestCase
{
	public function testExtractArray_IdArray() {
		// /ID [<2A0E03C0A0A3C0918938E0CF646A2678><2A0E03C0A0A3C0918938E0CF646A2678>]		
		$array = '[<2A0E03C0A0A3C0918938E0CF646A2678><2A0E03C0A0A3C0918938E0CF646A2678>]';
		$base = new PdfBaseConcrete();
		$result = $base->extractArray($array);
		$this->assertEquals(2, count($result));
		$this->assertEquals(9223372036854775807, $result[0]);
		$this->assertEquals(9223372036854775807, $result[1]);
	}
	
	/*
	public function testExtractDictionary_EncryptFilter() {
		$ditionaryString = '<< /Filter /Standard /V 1 /R 2 /O (SomeOwner) /U (SomeUser) /P -44 >>';
		$base = new PdfBaseConcrete();
		$result = $base->extractDictionary($ditionaryString);
		var_dump($result);
		
	}
	*/
	
	public function testExtractString_WithEscapes_UnescapseString() {
		$string = '(A\rB)';
		$base = new PdfBaseConcrete();
		$result = $base->extractString($string);
		$this->assertEquals("A\rB", $result);
	}

}

?>
