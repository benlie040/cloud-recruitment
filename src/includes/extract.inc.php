<?php

function stringExtract(string $line, string $fieldName): string|null
{
    $value = null;

    # creating an array of each string of the current line, with whitespace as the delimiter
    $fields = explode(" ", string: $line);

    foreach ($fields as $field) {
        # if the current line contains the fieldName, the value will be extracted
        if (str_starts_with($field, $fieldName))
            $value = substr($field, strlen($fieldName));
    }
    return $value;
}

function specsExtract(string $specsString, string $fieldName): string|false
{
    # If any of the decoding steps fails, false will be returned as default value
    $result = false;
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
                $result = $metadataArray[$fieldName];
        }
    }
    return $result;
}