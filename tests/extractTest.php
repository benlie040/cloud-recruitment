<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require 'src/includes/extract.inc.php';
/**
 * Unit tests for the functions of extract.inc.php
 */
final class extractTest extends TestCase
{
    public function test_stringExtract()
    {
        $arrVariables = array($serialNumber = '', $serialNumberLine = '', $specsLine1 = '', $specsString = '', $specsLine2 = '', $specsLine3 = '', $arrJSON = '', $jsonDecode = '', $errBase64Decode = '', $errGzipDecode = '', $errJsonDecode = '');
        $testData = file("data/testData.txt");
        for ($i = 0; $i < 6; $i++) {
            $arrVariables[$i] = trim($testData[$i]);
        }
        # Test for serial number
        $this->assertEquals($arrVariables[0], stringExtract($arrVariables[1], 'serial='));
        # Test for specs string
        $this->assertEquals($arrVariables[3], stringExtract($arrVariables[2], 'specs='));
        # Test for sepcs string
        $this->assertEquals($arrVariables[3], stringExtract($arrVariables[4], 'sepcs='));
        # Test for missing serial number
        $this->assertEquals(NULL, stringExtract($arrVariables[5], 'serial='));
        # Test for missing specs string
        $this->assertEquals(NULL, stringExtract($arrVariables[5], 'specs='));
        # Test for missing sepcs string
        $this->assertEquals(NULL, stringExtract($arrVariables[5], 'sepcs='));
    }
    public function test_specsExtract()
    {
        $arrVariables = array($serialNumber = '', $serialNumberLine = '', $specsLine1 = '', $specsString = '', $specsLine2 = '', $specsLine3 = '', $arrJSON = '', $jsonDecode = '', $errBase64Decode = '', $errGzipDecode = '', $errJsonDecode = '');
        $testData = file("data/testData.txt");
        for ($i = 6; $i < count($testData); $i++) {
            $arrVariables[$i] = trim($testData[$i]);
        }
        # Test for decoded JSON array
        $this->assertEquals(json_decode($arrVariables[6], true), specsExtract($arrVariables[7]));
        # Test for invalid base64 characters
        $this->assertEquals(array("Error" => "base64 data error"), specsExtract($arrVariables[8]));
        # Test for invalid gzip data
        $this->assertEquals(array("Error" => "gzip data error"), specsExtract($arrVariables[9]));
        # Test for invalid JSON format
        $this->assertEquals(array("Error" => "JSON_ERROR_SYNTAX"), specsExtract($arrVariables[10]));
    }
}