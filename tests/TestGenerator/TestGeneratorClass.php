<?php
/**
 * TestGeneratorClass.php automatically generates a testing template for use with PEAR's PHPUnit suite.
 *
 * PHP version 5.1
 *
 * @author    John M. Stokes <jstokes@heartofthefyre.us>
 * @copyright 2011 John M. Stokes
 * @license   http://www.opensource.org/licenses/bsd-license.html BSD Style License
 */

class TestGenerator {

	/**************
	 * PROPERTIES *
	 **************/

	protected $filepath; //string. The path to the file we're parsing, MINUS the filename
	protected $filename; //string. The file we're parsing, MINUS .php
	protected $fh; //resource. A File Handle to the file we're parsing
	protected $testfile = ''; //string. The test suite
	protected $classname; //string. The name of the class we're testing
	protected $methodname; //string. The name of the method we're currently parsing
	protected $parameters; //array. The parameters required by the method we're currently parsing

	/**********
	 * METHODS *
	 ***********/

	/********************
	 * PUBLIC INTERFACE *
	 ********************/

	/**
	 * __construct creates a file handle to the PHP file for which we're creating tests
	 *
	 * @param string $filepath - the PHP file for which to generate tests
	 *
	 * @return N/A
	 */
	public function __construct($filepath) {
		if (empty($filepath))
			Throw new Exception('Error: You must include a filepath.');

		$path = pathinfo($filepath);

		if ($path['extension'] != 'php')
			Throw new Exception('Error: This doesn\'t appear to be a PHP file.');

		if (!$this->fh = fopen($filepath, 'r'))
			Throw new Exception('Error: Unable to open file. Please check your file path and try again.');

		$this->filepath = $path['dirname'].'/';
		$filenameArray = explode('.', basename($filepath));
		$this->filename = $filenameArray[0];

		$this->testfile = "<?php\nrequire_once 'PHPUnit/Framework.php';";
		$this->testfile .= "\n\nclass {$this->filename}_test extends PHPUnit_Framework_TestCase {";

		$this->createTests();
	}//End __construct


	/************************
	 * FILE PARSING METHODS *
	 ************************/

	protected function createTests() {
		rewind($this->fh);

		while (!feof($this->fh)) {
			$line = fgets($this->fh);

			if (strpos($line, 'class') !== false && strpos($line, '{') !== false)
				$this->addClass($line);

			if (strpos($line, 'function') !== false)
				$this->addMethod($line);

			if (strpos($line, 'Exception') !== false)
				$this->addException($line);
		}//End while !feof

		//Close out the test file
		$this->testfile .= '} //End '.$this->filename.'_test class'."\n?>";

		//Write the test file to disk
		$fh = fopen($this->filepath.'test_'.$this->filename.'.class.php', 'w');
		fwrite($fh, $this->testfile);
		fclose($fh);

		return;
	}//End createTests


	/**
	 * addClass creates a variable and constructor for the class we're testing
	 *
	 * @param string $line - the line in the file that contains the class declaration
	 *
	 * @return N/A - operates directly on the $this->testfile property
	 */
	protected function addClass($line) {
		$classname = substr($line, strpos($line, 'class')+6); //Chop off everything before the class name

		$chopPosition = strlen($classname);
		for ($i=0; $i<strlen($classname); $i++) {
			$char = $classname[$i];

			/*
			 * Find the space that ends the class name, and remember
			 * that string position
			 */
			if (ord($char) == 0x20) {
				$chopPosition = $i;
				break;
			}
		}
		$classname = substr($classname, 0, $chopPosition);
		$this->classname = $classname;

		$this->testfile .= '

	/*************
	 * PROPERTIES *
	 **************/

	 protected $'.$classname.'; //object. An instance of the class we\'re testing.

	/***********
	 * METHODS *
	 ***********/

	 /**
	  * __construct instantiates the class we\'re testing
	  *
	  * @return N/A
	  */
	 public function __construct() {
		$this->'.$classname.' = new '.$classname.'();
	 }//End __construct()
	 ';

		return;
	}//End addClass


	/**
	 * addMethod creates a test method which tests the object method passed in
	 *
	 * @param string $line - the file line containing the method declaration
	 *
	 * @return N/A - operates directly on the $this->testfile property
	 */
	protected function addMethod($line) {
		$method = substr($line, strpos($line, 'function')+9); //Chop off everything before the method name
		$methodname = 'NOT_FOUND';

		/*
		 * Walk through the method declaration to extract its name and
		 * variable parameters
		 */
		$parameters = array();
		$varStart = $varLength = 0;
		for ($i=0; $i<strlen($method); $i++) {

			$char = $method[$i];

			switch ($char) {
				case '(': //we've reached the end of the method name
					$methodname = substr($method, 0, $i);
					$this->methodname = $methodname;
					break;
				case '$': //start of a variable declaration
					$varStart = $i;
					$varLength = 1;
					break;
				case ',': //end of a variable declaration
				case '=':
					if ($varStart > 0)
						$parameters[] = trim(substr($method, $varStart, (int)$varLength));
					$varStart = 0;
					break;
				case ')':
					if ($varStart > 0)
						$parameters[] = trim(substr($method, $varStart, (int)$varLength));
					$varStart = 0;
					break;
				default:
					++$varLength;
					break;
			}//Close switch
		}//Close for $i
		$this->parameters = $parameters;


		if ($methodname == '__construct' || $methodname == '__destruct')
			return;

		//Create the output
		$this->testfile .= '
		public function test_'.$methodname.'() {';

		foreach ($parameters as $var) {
			$this->testFile .= "
			\${$var} = '';";
		}//End foreach $var

		$this->testfile .= '

			$result = $this->'.$this->classname.'->'.$methodname.'(';
		foreach ($parameters as $var) {
			if (empty($var))
				continue;
			$this->testfile .= $var.',';
		}//End foreach $var
		$this->testfile = substr($this->testfile, 0, -1); //Strip trailing comma
		$this->testfile .= ')
			$this->assertEquals(\'\', $result);
			//$this->assertRegExp($pattern, $result);
			//$this->assertTrue(is_array($row));
			//$this->assertArrayHasKey(\'\', $row);
		}//End test_'.$methodname."\n";

		return;
	}//End addMethods


	/**
	 * addException creates a test method which tests errors returned by the current method
	 *
	 * @param string $line - the file line containing the thrown Exception
	 *
	 * @return N/A - operates directly on the $this->testfile property
	 */
	protected function addException($line) {
		//Create the output
		$this->testfile .= '
		public function test_exception_'.$this->methodname.'() {
			$this->setExpectedException(\'Exception\');
		';

		foreach ($this->parameters as $var) {
			$this->testFile .= "
			//Give variables dummy values
			\${$var} = '';";
		}//End foreach $var

		$this->testfile .= '

			$result = $this->'.$this->classname.'->'.$this->methodname.'(';
		foreach ($this->parameters as $var) {
			if (empty($var))
				continue;
			$this->testfile .= $var.',';
		}//End foreach $var
		$this->testfile = substr($this->testfile, 0, -1); //Strip trailing comma

		$this->testfile .= ')
		}//End test_exception_'.$this->methodname."\n";

		return;
	}//End addException

}//End TestGenerator class
?>