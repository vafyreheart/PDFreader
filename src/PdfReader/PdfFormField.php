<?php
namespace PdfReader;
/**
 * PdfFormfield.class.php represents PDF form fields of any type, and
 * extracts the key and value from the field.
 *
 * PHP version 5.1
 *
 * @category  File_Formats
 * @package   File_PDFreader
 * @author    John M. Stokes <jstokes@heartofthefyre.us>
 * @copyright 2010, 2011 John M. Stokes
 * @license   http://www.opensource.org/licenses/bsd-license.html BSD Style License
 * @link      http://heartofthefyre.us/PDFreader/index.php
 */

/**
 * I include one class per file, so the file description is the class's description.
 *
 * @category  File_Formats
 * @package   File_PDFreader
 * @author    John M. Stokes <jstokes@heartofthefyre.us>
 * @copyright 2010, 2011 John M. Stokes
 * @license   http://www.opensource.org/licenses/bsd-license.html BSD Style License
 * @version   Release: 0.1.6
 * @link      http://heartofthefyre.us/PDFreader/index.php
 */
class PdfFormfield extends PdfBase
{
    /*************
    * PROPERTIES *
    **************/

    //PDF Page Attributes
    protected $FT;
    protected $Parent;
    protected $Kids;
    protected $T;
    protected $TU;
    protected $TM;
    protected $Ff;
    protected $V;
    protected $DV;
    protected $AA;
    protected $DA;
    protected $Q = 0;
    protected $DS;
    protected $RV;
    protected $AP;
    protected $Opt;

    //PHP Attributes
    public $reference;
    public $ReadOnly = false; //Bit position 32 (1-indexed)
    public $Required = false; //Bit position 31
    public $NoExport = false; //Bit position 30
    public $CommitOnSelChange = false; //Bit position 27
    public $RichText = false; //Bit position 26, RadiosInUnison for radio buttons
    public $Comb = false; //Bit position 25
    public $DoNotScroll = false; //Bit position 24
    public $DoNotSpellCheck = false; //Bit position 23
    public $MultiSelect = false; //Bit position 22
    public $FileSelect = false; //Bit position 21
    public $Sort = false; //Bit position 20
    public $Edit = false; //Bit position 19
    public $Combo = false; //Bit position 18
    public $Pushbutton = false; //Bit position 17
    public $Radio = false; //Bit position 16
    public $NoToggleToOff; //Bit position 15
    public $Password = false; //Bit position 14
    public $Multiline = false; //Bit position 13
    protected $children;
    protected $value;

    /**********
    * METHODS *
    ***********/

    /**
     * __construct sets up the field attributes and converts some key PDF objects
     * to PHP objects
     *
     * @param resource $fh         - the file handle created by the PDFreader class
     * @param array    $Xrefs      - the XRef table extracted by the PDFreader class
     * @param object   $PdfDecoder - the PdfDecoder from the PDFreader class
     * @param string   $reference  - the PDF reference to this object
     * @param object   $parent     - an optional form field object that is
     *     the parent of this object
     *
     * @return N/A
     */
    public function __construct($fh, $Xrefs, $PdfDecoder, $reference, $parent=null)
    {
        parent::__construct();
        $this->fh = $fh;
        $this->Xrefs = $Xrefs;
        $this->PdfDecoder = $PdfDecoder;

        if (empty($reference)) {
            Throw new PdfException('Field creation error: Invalid field reference');
        }
        if ($this->debugLevel > self::DEBUG_OFF) {
            echo "<strong><u>Creating form field $reference</u></strong><br />\n";
        }

        $fieldObj = $this->extractObject($reference);
        $fieldDictionary = $this->extractDictionary($fieldObj);
        if ($this->debugLevel > self::DEBUG_OFF) {
            echo "Default field name {$fieldDictionary['T']}<br />\n";
        }

        //Set required PDF attributes
        if (!isset($fieldDictionary['FT'])) {
            if (isset($parent)) { //Field type is inheritable
                $this->FT = $parent->getFieldType();
            } else if (!isset($fieldDictionary['Kids'])) { //FT may be set on children
                Throw new PdfException('Field creation error:
                    Field type could not be determined.'
                );
            }
        } else {
            $this->FT = $fieldDictionary['FT'];
        }

        //Set optional PDF attributes
        if (isset($parent)) {
            $this->Parent = $parent;
        }
        if (isset($fieldDictionary['Kids'])) {
            $this->Kids = $fieldDictionary['Kids'];
            $this->createChildren();
        }
        if (isset($fieldDictionary['T'])) {
            $this->T = $fieldDictionary['T'];
        }
        if (isset($fieldDictionary['TU'])) {
            $this->TU = $fieldDictionary['TU'];
        }
        if (isset($fieldDictionary['TM'])) {
            $this->TM = $fieldDictionary['TM'];
        }
        if (isset($fieldDictionary['Ff'])) {
            $this->Ff = $this->extractFieldFlags($fieldDictionary['Ff']);
        }
        if (isset($fieldDictionary['V'])) {
            $this->V = $fieldDictionary['V'];
        }
        if (isset($fieldDictionary['DV'])) {
            $this->DV = $fieldDictionary['DV'];
        }
        if (isset($fieldDictionary['AA'])) {
            $this->AA = $fieldDictionary['AA'];
        }
        if (isset($fieldDictionary['DA'])) {
            $this->DA = $fieldDictionary['DA'];
        }
        if (isset($fieldDictionary['Q'])) {
            $this->Q = $fieldDictionary['Q'];
        }
        if (isset($fieldDictionary['DS'])) {
            $this->DS = $fieldDictionary['DS'];
        }
        if (isset($fieldDictionary['RV'])) {
            $this->RV = $fieldDictionary['RV'];
        }
        if (isset($fieldDictionary['AP'])) {
            $this->AP = $fieldDictionary['AP'];
        }
        if (isset($fieldDictionary['Opt'])) {
            $this->Opt = $fieldDictionary['Opt'];
        }

        //Set PHP attributes
        $this->reference = $reference;
        $this->determineValue();
    }//End __construct


    /********************
     * ACCESSOR METHODS *
     *********************/

    /**
     * getFieldType returns this object's field type
     *
     * @return string $this->FT - the object's field type
     */
    public function getFieldType()
    {
        return $this->FT;
    }//End getFT


    /**
     * getName determines the best name for this field and returns it
     *
     * @return string $name - the most appropriate name
     */
    public function getName()
    {
        if ($this->debugLevel > self::DEBUG_HIDE_EXTRACTION) {
            echo "Entered getName<br />\n";
        }
        
        $name = '';
        if (isset($this->TM)) { //Try mapping name first
            $name = $this->TM;
        } else if (isset($this->T)) { //Partial name is default
            $name = $this->T;
        } else if (isset($this->TU)) { //IF all else fails, try alt name
            $name = $this->TU;
        }

        if (isset($name[0]) && $name[0] == '(' && $name[strlen($name)-1] == ')') {
            $name = substr($name, 1, -1); //Strip ( and )
        }

        //Ensure field name has only legal characters
        $name = preg_replace('/[^\\x20-\\xFF]/', '', $name);

        //If string is hex-encoded, decode it
        if (preg_match('/\\<[0-9A-F]+\\>/i', $name)) {
            $name = $this->PdfDecoder->decodeHexString($name);
        }
        
        //Encode text as UTF-8
        $name = utf8_encode($name);

        return $name;
    }//End getName;


    /**
     * getValue returns the user-entered value, if it exists
     *
     * @return string $this->value - the field's value
     */
    public function getValue()
    {
        return $this->value;
    }//End getValue


    /**
     * getKeyValue returns the field's name (key) and its value
     *
     * @return array $keyValue - a one-item array with the key and value
     */
    public function getKeyValue()
    {
        $key = $this->getName();
        $value = $this->getValue();
        $keyValue = array($key=>$value);
        return $keyValue;
    }//End getKeyValue


    /**
     * hasChildren just indicates if there are child fields derived from this field
     *
     * @return Boolean - whether this field has children
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }//End hasChildren


    /**
     * getChildren returns an array of field objects that are children of this object
     *
     * @return array $this->children - the array of fields
     */
    public function getChildren()
    {
        return $this->children;
    }//End getChildren

    /************************
     * END ACCESSOR METHODS *
     ************************/

    /**
     * extractFieldFlags converts the extractFieldFlags int into to a binary string
     * and sets boolean flags based on that string
     *
     * @param int $FieldFlags - the int representing a 32-digit binary number
     *
     * @return N/A - operates directly on object properties
     */
    protected function extractFieldFlags($FieldFlags)
    {
        if ($this->debugLevel > self::DEBUG_HIDE_EXTRACTION) {
            echo "Entered extractFieldFlags<br />\n";
        }

        //Convert $FieldFlags int to a 32-bit string. PDF spec 12.7.3.1
        $zeroes = '';
        for ($i=0; $i<32; $i++) {
            $zeroes .= '0';
        }

        $binaryFieldFlags = decbin($FieldFlags);

        //Pad with leading zeroes to make a 32-digit integer
        $zeroes = substr($zeroes, strlen($binaryFieldFlags));
        $binaryFieldFlags = $zeroes.$binaryFieldFlags;

        //Set PHP Booleans based on FieldFlags
        $this->ReadOnly = $binaryFieldFlags[31] == 1;
        $this->Required = $binaryFieldFlags[30] == 1;
        $this->NoExport = $binaryFieldFlags[29] == 1;
        $this->CommitOnSelChange = $binaryFieldFlags[26] == 1;
        $this->RichText = $binaryFieldFlags[25] == 1;
        $this->Comb = $binaryFieldFlags[24] == 1;
        $this->DoNotScroll = $binaryFieldFlags[23] == 1;
        $this->DoNotSpellCheck = $binaryFieldFlags[22] == 1;
        $this->MultiSelect = $binaryFieldFlags[21] == 1;
        $this->FileSelect = $binaryFieldFlags[20] == 1;
        $this->Sort = $binaryFieldFlags[19] == 1;
        $this->Edit = $binaryFieldFlags[18] == 1;
        $this->Combo = $binaryFieldFlags[17] == 1;
        $this->Pushbutton = $binaryFieldFlags[16] == 1;
        $this->Radio = $binaryFieldFlags[15] == 1;
        $this->NoToggleToOff = $binaryFieldFlags[14] == 1;
        $this->Password = $binaryFieldFlags[13] == 1;
        $this->Multiline = $binaryFieldFlags[12] == 1;
        
        return $binaryFieldFlags;
    }//End extractFieldFlags


    /**
     * createChildren creates form field objects from the $this->Kids array
     *
     * @return N/A - operates directly on the $this->children property
     */
    protected function createChildren()
    {
        if ($this->debugLevel > self::DEBUG_HIDE_EXTRACTION) {
            echo "Entered createChildren<br />\n";
        }

        foreach ($this->Kids as $childRef) {
            $this->children[] = new PdfFormfield(
                $this->fh, $this->Xrefs, $this->PdfDecoder,
                $childRef, $this
            );
        }

        return;
    }//End createChildren


    /**
     * determineValue selects and, if necessary, extracts
     * the best value for this field
     *
     * @return N/A - operates directly on the $this->value property
     */
    protected function determineValue()
    {
        if ($this->debugLevel > self::DEBUG_HIDE_EXTRACTION) {
            echo "Entered determineValue<br />\n";
        }

        if (isset($this->RV)) { //Rich text value trumps normal value
            $this->value = $this->RV;
        } else if (isset($this->V)) { //Normal value
            $this->value = $this->V;
        } else if (isset($this->DV)) { //If no value and a default exists, return default
            $this->value = $this->DV;
        }


        //Clean up the text
        if ($this->value[0] == '(' && $this->value[strlen($this->value)-1] == ')') {
            $this->value = substr($this->value, 1, -1); //Strip ( and )
        } else if ($this->value[0] == '/') {
            $this->value = substr($this->value, 1);     //Strip / from name objects
        }
        $this->value = $this->PdfDecoder->unescapeString($this->value);
    
        
        //Check for Radio Buttons/Checkboxes and map against Opt entry
        if ($this->FT == '/Btn' && isset($this->Opt)) {
            $this->value = $this->Opt[(int)$this->value];
        }
        
        //Encode text as UTF-8 for output
        $this->value = utf8_encode($this->value);

        return;
    }//End determineValue
}//End PdfFormfield class
?>