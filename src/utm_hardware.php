<?php

/**
 * Task #3: This PHP script scans the access log of a nginx server
 * and identifies the machine (computer architecture) and cpu types
 * of the clients. An overview with the numbers of the licence serial 
 * numbers active on these categories will be provided.
 */

include 'includes/extract.inc.php';

$logFilePath = '../log/updatev12-access-pseudonymized.log'; //Path of the logfile
# Paths of the result files
$machineFilePath = "../data/result_utm_hardware_machine.txt";
$cpuFilePath = "../data/result_utm_hardware_cpu.txt";
$memFilePath = "../data/result_utm_hardware_mem.txt";
$diskRootFilePath = "../data/result_utm_hardware_disk_root.txt";
$diskDataFilePath = "../data/result_utm_hardware_disk_data.txt";

$errorFilePath = "../data/error_hardware.log"; //Log of the errors for evaluation purposes
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

        # The script will only proceed, when the specs string was found
        if (isset($specsString)) {

            # Function call of specsExtract() included in extract.inc.php to obtain the array of the specs JSON object
            $specsJson = specsExtract($specsString);

            # The script will only proceed, when the specs string was decoded
            if (!isset($specsJson["Error"])) {

                # Accessing the value for the mac address
                $macNumber = $specsJson["JSON"]["mac"];

                # The script will only proceed, when the mac address is valid
                if (filter_var($macNumber, FILTER_VALIDATE_MAC)) {

                    /* There are licence serial numbers active on more than one device violating the licence agrement.
                    To include the hardware used of these UTMs, despite the violation, the combination of serial number 
                    and mac address can identity this clients aswell*/
                    $serialNumber = $serialNumber . $macNumber;

                    # Removing the unit from the RAM value string
                    $memNumber = str_replace("kB", "", $specsJson["JSON"]["mem"]);
                    # Classifying the RAM value into different capacity levels, ranging from 1GB to 64GB and above
                    if ($memNumber > 64 * 1024 * 1024)
                        $ramSize = "> 64";
                    else {
                        for ($i = 63; $i >= 0; $i--) {
                            if ($memNumber > $i * 1024 * 1024) {
                                $ramSize = $i + 1;
                                break;
                            }
                        }
                    }
                    # Classifying the disk data value into different capacity levels, ranging from 1GB to 1TB and above
                    if ($specsJson["JSON"]["disk_data"] > 1024 * 1024 * 1024)
                        $diskDataSize = "> 1 TB";
                    else {
                        for ($i = 9; $i >= 0; $i--) {
                            if ($specsJson["JSON"]["disk_data"] > pow(2, $i) * 1024 * 1024) {
                                $diskDataSize = pow(2, $i) . "-" . pow(2, $i + 1);
                                break;
                            }
                        }
                    }
                    # Storing the serial numbers and their hardware values in associative arrays
                    $arrMachine[$serialNumber] = $specsJson["JSON"]["machine"];
                    $arrCpu[$serialNumber] = $specsJson["JSON"]["cpu"];
                    $arrDiskRoot[$serialNumber] = $specsJson["JSON"]["disk_root"];
                    $arrMem[$serialNumber] = $ramSize;
                    $arrDiskData[$serialNumber] = $diskDataSize;
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

# Opening different textfiles to store the hardware values separately
$fileMachine = fopen("../data/result_utm_hardware_machine.txt", "w");
$fileCpu = fopen("../data/result_utm_hardware_cpu.txt", "w");
$fileMem = fopen("../data/result_utm_hardware_mem.txt", "w");
$fileDiskRoot = fopen("../data/result_utm_hardware_diskRoot.txt", "w");
$fileDiskData = fopen("../data/result_utm_hardware_diskData.txt", "w");

# Counting identical hardware values 
$arrCountMachine = array_count_values($arrMachine);
$arrCountCpu = array_count_values($arrCpu);
$arrCountMem = array_count_values($arrMem);
$arrCountDiskRoot = array_count_values($arrDiskRoot);
$arrCountDiskData = array_count_values($arrDiskData);

# Sorting (descending) the arrays according to the values (hardware values)
arsort($arrCountMachine);
arsort($arrCountCpu);
arsort($arrCountDiskData);

# Sorting (descending) the arrays according to the keys (capacity level)
ksort($arrCountMem);
ksort($arrCountDiskRoot);

#  Writing the results for serial number and machine value into the textfile
foreach ($arrCountMachine as $machineKey => $licenceCount) {
    fwrite($fileMachine, "Architektur: $machineKey,  Anzahl Lizenzen: $licenceCount \n");
}
fclose($fileMachine);
#  Writing the results for serial number and cpu value into the textfile
foreach ($arrCountCpu as $cpukey => $licenceCount) {
    fwrite($fileCpu, "CPU: $cpukey,  Anzahl Lizenzen: $licenceCount \n");
}
fclose($fileCpu);
#  Writing the results for serial number and menory value into the textfile
foreach ($arrCountMem as $memKey => $licenceCount) {
    fwrite($fileMem, "Arbeitsspeichergrösse: $memKey GB,  Anzahl Lizenzen: $licenceCount \n");
}
fclose($fileMem);
#  Writing the results for serial number and root data value into the textfile
foreach ($arrCountDiskRoot as $diskRootKey => $licenceCount) {
    fwrite($fileDiskRoot, "Speichergrösse Systemlaufwerk: $diskRootKey kB,  Anzahl Lizenzen: $licenceCount \n");
}
fclose($fileDiskRoot);
#  Writing the results for serial number and disk data value into the textfile
foreach ($arrCountDiskData as $diskDataKey => $licenceCount) {
    fwrite($fileDiskData, "Speichergrösse Datenlaufwerk: $diskDataKey GB,  Anzahl Lizenzen: $licenceCount \n");
}
fclose($fileDiskData);

// Writing the errors into a text file
$errorLog = fopen($errorFilePath, "w");
foreach ($arrError as $line => $err) {
    fwrite($errorLog, "Zeile: $line, Fehler: $err\n");
}
fclose($errorLog);

echo "Es wurden $lineCount Zeilen verarbeitet.\n\n";
echo "Die Ergebnisse wurden gespeichert in: \n$machineFilePath\n$cpuFilePath\n$memFilePath\n$diskRootFilePath\n$diskDataFilePath\n\n";
echo "Die Fehler wurden gespeichert in: \n$errorFilePath";


