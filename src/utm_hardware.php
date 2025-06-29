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

            # Function call of specsExtract() included in extract.inc.php to obtain the hardware values
            $machine = specsExtract($specsString, "machine");
            $cpu = specsExtract($specsString, "cpu");
            $mem = specsExtract($specsString, "mem");
            $diskRoot = specsExtract($specsString, "disk_root");
            $diskData = specsExtract($specsString, "disk_data");

            # Removing the unit from the RAM value string
            $memNumber = str_replace("kB", "", $mem);
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
            if ($diskData > 1024 * 1024 * 1024)
                $diskDataSize = "> 1 TB";
            else {
                for ($i = 9; $i >= 0; $i--) {
                    if ($diskData > pow(2, $i) * 1024 * 1024) {
                        $diskDataSize = pow(2, $i) . "-" . pow(2, $i + 1);
                        break;
                    }
                }
            }
            # Storing the serial numbers and their hardware values in associative arrays
            $arrMachine[$serialNumber] = $machine;
            $arrCpu[$serialNumber] = $cpu;
            $arrDiskRoot[$serialNumber] = $diskRoot;
            $arrMem[$serialNumber] = $ramSize;
            $arrDiskData[$serialNumber] = $diskDataSize;
        }
    }
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

#  Writing the results for serial number and cpu value into the textfile
foreach ($arrCountCpu as $cpukey => $licenceCount) {
    fwrite($fileCpu, "CPU: $cpukey,  Anzahl Lizenzen: $licenceCount \n");
}

#  Writing the results for serial number and menory value into the textfile
foreach ($arrCountMem as $memKey => $licenceCount) {
    fwrite($fileMem, "Arbeitsspeichergrösse: $memKey GB,  Anzahl Lizenzen: $licenceCount \n");
}

#  Writing the results for serial number and root data value into the textfile
foreach ($arrCountDiskRoot as $diskRootKey => $licenceCount) {
    fwrite($fileDiskRoot, "Speichergrösse Systemlaufwerk: $diskRootKey kB,  Anzahl Lizenzen: $licenceCount \n");
}

#  Writing the results for serial number and disk data value into the textfile
foreach ($arrCountDiskData as $diskDataKey => $licenceCount) {
    fwrite($fileDiskData, "Speichergrösse Datenlaufwerk: $diskDataKey GB,  Anzahl Lizenzen: $licenceCount \n");
}

echo "result_utm_hardware_machine.txt wurde erstellt in ../data/\n";
echo "result_utm_hardware_cpu.txt wurde erstellt in ../data/\n";
echo "result_utm_hardware_mem.txt wurde erstellt in ../data/\n";
echo "result_utm_hardware_diskRoot.txt wurde erstellt in ../data/\n";
echo "result_utm_hardware_diskData.txt wurde erstellt in ../data/";

fclose($fileMachine);
fclose($fileCpu);
fclose($fileMem);
fclose($fileDiskRoot);
fclose($fileDiskData);
