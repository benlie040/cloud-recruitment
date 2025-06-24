<?php

/** Task #2: This PHP script scans the access log of a nginx server
 *  for the licence serial numbers that are installed on more than
 *  one device and returns a list with the 10 licences, which violate
 *  this rule the most. 
 */

include 'includes/extract.inc.php';

$filePath = '../log/updatev12-access-pseudonymized.log'; //Path of the logfile

# Exception handling, to prevent a fatal error, when reading the file
try {
    # Opening the logfile in ready only mode
    $logFile = fopen($filePath, "r");

    if (!$logFile) {
        # Exception is thrown, if fopen fails
        throw new Exception("Fehler: Datei kann nicht geöffnet werden!");
    }
} catch (Exception $e) {
    die($e->getMessage()); // Termination of the script
}
# The array will store serial numbers as keys and arrays containing their mac addresses as values
$utmArray = [];

while (!feof($logFile)) {
    $line = fgets($logFile);

    # Function call of stringExtract() included in extract.inc.php to obtain the serial number value
    $serialNumber = stringExtract($line, "serial=");

    # The script will only proceed, when a serial number was found
    if (isset($serialNumber)) {

        # Function call of stringExtract() included in extract.inc.php to obtain the specs value
        $specsString = stringExtract($line, "specs=");

        # The script will only proceed, when a specs string was found
        if (isset($specsString)) {

            # Function call of specsExtract() included in extract.inc.php to obtain the mac number
            $macNumber = specsExtract($specsString, "mac");

            # The script will only proceed, when the mac number is valid
            if (filter_var($macNumber, FILTER_VALIDATE_MAC)) {

                # Check if utmArray contains the serial number as a key
                if (array_key_exists($serialNumber, $utmArray)) {

                    /* If the utmArray contains the serial number already as a key, 
                     another ckeck follows, if the associated mac number is not already 
                     an element of the associated array. */
                    if (!in_array($macNumber, $utmArray[$serialNumber]))

                        /* If the mac number is new, it will be added to the array (value).
                        Since the serial number is not a new key and one more mac number 
                        is pushed to a list of already recorded mac numbers, the number of 
                        license violations can be tracked with help of the array (value). */
                        array_push($utmArray[$serialNumber], $macNumber);

                } else {
                    /* In this branch utmArray adds the serial number as a new key and the 
                    the associated mac number as the value.*/
                    $utmArray[$serialNumber] = array($macNumber);
                }
            }
        }
    }
}
fclose($logFile);

/* sorting (descending) the utmArray array according to the values, in 
this case the number of the array elements the values contain */
arsort($utmArray);

# slicing the array to obtain only the first 10 elements
$utmArray = (array_slice($utmArray, 0, 10));

$file = fopen("../data/result_utm_licence_violation.txt", "w");

/* Writing the result into a text file. With sizeof the number of the mac addresses 
 the values contain will be displayed. This is the number -1 of license violations
 for each serial number */
foreach ($utmArray as $serNr => $macAddr) {
    fwrite($file, "Seriennummer: $serNr, 
    Anzahl registrierter Mac Adressen:" . sizeof($macAddr) . " \n");
}
fclose($file);