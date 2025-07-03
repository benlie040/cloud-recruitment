<?php

/**
 * The function extracts the value of the wanted field from the line.
 * @param string $line
 * @param string $fieldName
 * @return string|null
 */
function stringExtract(string $line, string $fieldName): string|null
{
    # If the line contains no fieldName, null will be returned as default value
    $value = null;

    # Creating an array of each string of the current line, with whitespace as the delimiter
    $fields = explode(" ", string: $line);

    foreach ($fields as $field) {
        # if the current line contains the fieldName, the value will be extracted
        if (str_starts_with($field, $fieldName))
            $value = substr($field, strlen($fieldName));
    }
    return $value;
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
function specsExtract(string $specsString): array
{
    # If any of the decoding steps fails, null will be returned as default value
    $result = null;
    # Decoding of the String from Base64 format
    $decodedData = base64_decode($specsString);
    if ($decodedData) {
        # Uncompressing the Base64 decoded data, suppressing warnings if decodedData is corrupted
        $unzippedData = @gzdecode($decodedData);
        if ($unzippedData) {
            # Conversion of the uncompressed JSON string into an associative array
            $metadataArray = json_decode($unzippedData, true);
            if ($metadataArray)
                # Accessing the desired value from the associative array
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