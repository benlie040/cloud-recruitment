<?php

/** 
 * Task #2: This PHP script scans the access log of a nginx server
 * for the licence serial numbers that are installed on more than
 * one device and returns a list with the 10 licences, which violate
 * this rule the most. 
 */

include 'includes/extract.inc.php';

$logFilePath = '../log/updatev12-access-pseudonymized.log'; //Path of the logfile
$violationFilePath = "../data/result_utm_licence_violation.txt"; //Path of the result file
$errorFilePath = "../data/error_violation.log"; //Log of the errors for evaluation purposes
$lineCount = 1; //Counter for the lines

# Exception handling, to prevent a fatal error, when reading the file
try {
    # Opening the logfile in ready only mode
    $logFile = fopen($logFilePath, "r");

    if (!$logFile) {
        # Exception is thrown, if fopen fails
        throw new Exception("Fehler: Datei kann nicht geöffnet werden!");
    }
} catch (Exception $e) {
    die($e->getMessage()); // Termination of the script
}
# The array will store serial numbers as keys and arrays containing their mac addresses as values
$utmArray = [];

# The array will contain in case of errors the line as key and error message as value
$arrError = [];

while (!feof($logFile)) {
    $line = fgets($logFile);

    # Function call of stringExtract() included in extract.inc.php to obtain the serial number value
    $serialNumber = stringExtract($line, "serial=");

    # The script will only proceed, when a serial number was found
    if (isset($serialNumber)) {

        /* Function call of stringExtract() included in extract.inc.php to obtain the specs value
        In some lines the fieldname is sepcs instead of specs. Since this is a systematical error 
        the sepcs value will be evaluated too as a temporary solution. */
        $specsString = stringExtract($line, "specs=") ?? stringExtract($line, "sepcs=");

        # The script will only proceed, when a specs string was found.
        if (isset($specsString)) {

            # Function call of specsExtract() included in extract.inc.php to obtain the array of the specs JSON object
            $specsJson = specsExtract($specsString);

            # The script will only proceed, when the specs string was decoded
            if (!isset($specsJson["Error"])) {

                # Accessing the value for the mac address
                $macNumber = $specsJson["JSON"]["mac"];

                # The script will only proceed, when the mac address is valid
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
                } else {
                    # In case of an invalid mac address, the line and the message will be saved.
                    $arrError[$lineCount] = "no valid mac address";
                }
            } else {
                # In case of invalid base64, gzip, JSON specsExtract will return the error message.
                $arrError[$lineCount] = $specsJson["Error"];
            }
        } else {
            # In case no specs string was found, the line and the message will be saved.
            $arrError[$lineCount] = "missing specs field";
        }
    } else {
        # In case no serial number was found, the line and the message will be saved.
        $arrError[$lineCount] = "missing serial number field";
    }
    $lineCount++;
}
fclose($logFile);

/* Sorting (descending) the utmArray array according to the values, in 
this case the number of the array elements the values contain */
arsort($utmArray);

# Slicing the array to obtain only the first 10 elements
$utmArray = (array_slice($utmArray, 0, 10));

/* Writing the result into a text file. The number of addresses 
 the $macAddr array contains, is the number-1 of license violations
 of the serial number. */
$file = fopen($violationFilePath, "w");
foreach ($utmArray as $serNr => $macAddr) {
    fwrite($file, "Seriennummer: $serNr, Anzahl registrierter Mac Adressen:" . sizeof($macAddr) . " \n");
}
fclose($file);

// Writing the errors into a text file
$errorLog = fopen($errorFilePath, "w");
foreach ($arrError as $line => $err) {
    fwrite($errorLog, "Zeile: $line, Fehler: $err\n");
}
fclose($errorLog);

echo "Es wurden $lineCount Zeilen verarbeitet.\n";
echo "Das Ergebnis wurde gespeichert in: $violationFilePath\n";
echo "Die Fehler wurden gespeichert in: $errorFilePath .";

