<?php

/** Task #1: This PHP script scans the access log of an nginx server
 *  for the licence serial numbers of those 10 UTMs, which are 
 *  accessing the server most often. 
 */

# exception handling, to prevent a fatal error, when reading the file
try {
  # path of the file
  $fileUrl = 'new 1.tt';
  //$fileUrl = 'updatev12-access-pseudonymized.log';

  # opening the file in ready only mode
  $logFile = fopen($fileUrl, "r");

  if (!$logFile) {
    # exception is thrown, if fopen fails
    throw new Exception("Fehler: Datei kann nicht geöffnet werden!");
  }
} catch (Exception $e) {
  # termination of the script
  die($e->getMessage());
}

$array = [];

# reading the input stream until the end of file
while (!feof($logFile)) {
  $line = fgets($logFile);

  # creating an array of each string of the current line, with whitespace as the delimiter
  $values = explode(" ", string: $line);

  # unset variable for the serial number
  $serialNumber = null;

  # checking if the line contains a serial number value, then adding it to an array
  foreach ($values as $value) {
    # 
    if (str_starts_with($value, "serial="))
      $serialNumber = substr($value, 7);
  }
  # 
  if (isset($serialNumber))
    array_key_exists($serialNumber, $array) ?
      $array[$serialNumber] += 1
      :
      $array[$serialNumber] = 1;
}

fclose($logFile);
/* output of all identified serial numbers (key) and the number 
of times (value) the tied UTM tried to access the server */
print_r($array);
