<?php
/**
 * TestGenerator.php automatically generates a testing template for use with PEAR's PHPUnit suite.
 *
 * PHP version 5.1
 *
 * @author    John M. Stokes <jstokes@heartofthefyre.us>
 * @copyright 2011 John M. Stokes
 * @license   http://www.opensource.org/licenses/bsd-license.html BSD Style License
 */

require_once 'TestGeneratorClass.php';

$path = '/var/www/html/vrw/';
$files = array($path.'ENG_TBA/GoogleEarth/KMLgenerator.class.php'); //The filepaths we're testing

foreach ($files as $file) {
	try {
		echo 'Creating test for '.basename($file)."\n";
		$test = new TestGenerator($file);
	}
	catch (Exception $e) {
		echo $e->getMessage()."\n";
	}
}//End foreach $file
echo "Done\n";
?>