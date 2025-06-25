<?php

/** Task #3: This PHP script scans the access log of a nginx server
 *  and identifies the machine (computer architecture) and cpu types
 *  of the clients. An overview with the numbers of the licence serial 
 *  numbers active on these categories will be provided.
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
$arrMachine = [];
$arrCpu = [];

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

            # Function call of specsExtract() included in extract.inc.php to obtain the machnine and cpu values
            $machine = specsExtract($specsString, "machine");
            $cpu = specsExtract($specsString, "cpu");

            # Storing the machine/cpu values for every serial number in associative arrays
            $arrMachine[$serialNumber] = $machine;
            $arrCpu[$serialNumber] = $cpu;
        }
    }
}
fclose($logFile);

print_r($arrMachine);