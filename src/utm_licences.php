<?php

/** 
 * Task #1: This PHP script scans the access log of an nginx server
 * for the licence serial numbers of those 10 UTMs, which are 
 * accessing the server most often. 
 */

include 'includes/extract.inc.php';

# exception handling, to prevent a fatal error, when reading the file
try {
  $filePath = '../log/updatev12-access-pseudonymized.log'; //path of the logfile

  # opening the logfile in ready only mode
  $logFile = fopen($filePath, "r");

  if (!$logFile) {
    # exception is thrown, if fopen fails
    throw new Exception("Fehler: Datei kann nicht geöffnet werden!");
  }
} catch (Exception $e) {
  die($e->getMessage()); // termination of the script
}
# array for storing the key/value pairs of serial number & number of accesses
$arraySN = [];

while (!feof($logFile)) {
  $line = fgets($logFile);

  # creating an array of each string of the current line, with whitespace as the delimiter
  $values = explode(" ", string: $line);

  # unset variable for the serial number at the start of each loop
  $serialNumber = null;

  # function call of stringExtract() included in extract.inc.php
  $serialNumber = stringExtract($line, "serial=");

  # if serialNumber is set, a serial number was found
  if (isset($serialNumber))
    array_key_exists($serialNumber, $arraySN) ?
      /* if the serial number was already added to the associative array, 
      only the number of accesses will be counted up by 1*/
      $arraySN[$serialNumber] += 1
      :
      $arraySN[$serialNumber] = 1; //new serial number will be added with the first access
}

fclose($logFile);

# sorting (descending) the array according to the values
arsort($arraySN);

# slicing the array to obtain only the first 10 elements
$arrayTop10 = (array_slice($arraySN, 0, 10));

$file = fopen("../data/result_utm_licences.txt", "w");

# writing the result into a text file
foreach ($arrayTop10 as $serNr => $accessNr) {
  fwrite($file, "Seriennummer: $serNr, Zugriffe: $accessNr \n");
}
echo "result_utm_licences.txt wurde erstellt in ../data/";
fclose($file);
