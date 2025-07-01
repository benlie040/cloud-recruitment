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
 * of the specs field and converts it to an array.
 * @param string $specsString
 * @return array|null
 */
function specsExtract(string $specsString): array|null
{
    # If any of the decoding steps fails, null will be returned as default value
    $result = null;
    # Decoding of the String from Base64 format
    $decodedData = base64_decode($specsString);
    if ($decodedData) {
        # Uncompressing the Base64 decoded data
        $unzippedData = gzdecode($decodedData);
        if ($unzippedData) {
            # Conversion of the uncompressed JSON string into an associative array
            $metadataArray = json_decode($unzippedData, true);
            if ($metadataArray)
                # Accessing the desired value from the associative array
                $result = $metadataArray;
        }
    }
    return $result;
}