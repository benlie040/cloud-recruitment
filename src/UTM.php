<?php
/**
 * The class UTM_1 is the blueprint for a UTM with just the serial number as property.
 */
class UTM_1
{
    protected $serialNumber = NULL;
    # The constructor calls stringExtract to initialize the serial number property
    public function __construct($line)
    {
        $this->serialNumber = $this->stringExtract($line, "serial=");
    }

    /**
     * The function extracts the value of the wanted field from the line.
     * @param string $line
     * @param string $fieldName
     * @return string|null
     */
    public function stringExtract($line, $fieldName)
    {
        $value = null;
        # Creating an array of each string of the line, with whitespace as the delimiter
        $fields = explode(" ", string: $line);

        foreach ($fields as $field) {
            # if the line contains the fieldName, the value will be extracted
            if (str_starts_with($field, $fieldName))
                $value = substr($field, strlen($fieldName));
        }
        return $value;
    }
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }
}

/**
 * The class UTM_2 is the blueprint for a UTM with the hardware 
 * values as properties and additionaly an property for error. 
 * It inherits serial number property from the class UTM_1.
 */
class UTM_2 extends UTM_1
{
    protected $macAddress = NULL;
    protected $machine = NULL;
    protected $cpu = NULL;
    protected $mem = NULL;
    protected $disk_root = NULL;
    protected $disk_data = NULL;
    protected $error = NULL;
    public function __construct($line)
    {
        # Call of the parent constructor to initialize the serial number property
        parent::__construct($line);

        /* Function call of stringExtract() to obtain the specs value
        In some lines the fieldname is sepcs instead of specs. Since this is a systematical error 
        the sepcs value will be evaluated too as a temporary solution. */
        $specsString = $this->stringExtract($line, "specs=") ?? $this->stringExtract($line, "sepcs=");
        # The script will only proceed, when a specs string was found.
        if (isset($specsString)) {

            # Function call of specsExtract() to obtain the array of the specs JSON object
            $specsJson = $this->specsExtract($specsString);

            # The script will only proceed, when the specs string was decoded
            if (!isset($specsJson["Error"])) {

                # Accessing the value for the mac address
                $macAddress = $specsJson["JSON"]["mac"];

                # The script will only proceed, when the mac address is valid
                if (filter_var($macAddress, FILTER_VALIDATE_MAC)) {

                    $this->macAddress = $macAddress;
                    $this->machine = $specsJson["JSON"]["machine"];
                    $this->cpu = $specsJson["JSON"]["cpu"];
                    $this->mem = $specsJson["JSON"]["mem"];
                    $this->disk_root = $specsJson["JSON"]["disk_root"];
                    $this->disk_data = $specsJson["JSON"]["disk_data"];
                } else {
                    # In case of an invalid mac address, the error property will be set
                    $this->error = "no valid mac address";
                }
            } else {
                # In case of invalid base64, gzip, JSON specsExtract will return the error message.
                $this->error = $specsJson["Error"];
            }
        } else {
            # In case no specs string was found, the error property will be set
            $this->error = "missing specs field";
        }
    }

    /**
     * The function decodes the gzip and base64 encoded JSON string 
     * of the specs field and converts it to an array. The decoded
     * data will be returned as the array element with the key "JSON". 
     * The element with the key "Error" contains, if an error occurs,
     * the error message.
     * @param string $specsString
     * @return array
     */
    public function specsExtract(string $specsString): array
    {
        # If any of the decoding steps fails, null will be returned as default value
        $result = null;
        # Decoding of the String from Base64 format
        $decodedData = base64_decode($specsString, true);
        if ($decodedData) {
            # Uncompressing the Base64 decoded data, suppressing warnings if decodedData is corrupted
            $unzippedData = @gzdecode($decodedData);
            if ($unzippedData) {
                # Conversion of the uncompressed JSON string into an associative array
                $metadataArray = json_decode($unzippedData, true);
                if ($metadataArray)
                    # JSON array as return value
                    $result["JSON"] = $metadataArray;
                else {
                    # Error message for invalid JSON
                    $errCode = json_last_error();
                    $errMessage = match ($errCode) {
                        0 => 'JSON_ERROR_NONE',
                        1 => 'JSON_ERROR_DEPTH',
                        2 => 'JSON_ERROR_STATE_MISMATCH',
                        3 => 'JSON_ERROR_CTRL_CHAR',
                        4 => 'JSON_ERROR_SYNTAX',
                        5 => 'JSON_ERROR_UTF8',
                        6 => 'JSON_ERROR_RECURSION',
                        7 => 'JSON_ERROR_INF_OR_NAN',
                        8 => 'JSON_ERROR_UNSUPPORTED_TYPE',
                        9 => 'JSON_ERROR_INVALID_PROPERTY_NAME',
                        10 => 'JSON_ERROR_UTF16',
                    };
                    $result["Error"] = $errMessage;
                }
            } else {
                $result["Error"] = "gzip data error";
            }
        } else {
            $result["Error"] = "base64 data error";
        }
        return $result;
    }
    public function getMacAddress()
    {
        return $this->macAddress;
    }
    public function getMachine()
    {
        return $this->machine;
    }
    public function getCPU()
    {
        return $this->cpu;
    }
    public function getMem()
    {
        return $this->mem;
    }
    public function getDisk_root()
    {
        return $this->disk_root;
    }
    public function getDisk_data()
    {
        return $this->disk_data;
    }
    public function getError()
    {
        return $this->error;
    }

}



