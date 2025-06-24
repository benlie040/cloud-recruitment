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