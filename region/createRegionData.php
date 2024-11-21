<?php

include '../include.php';

// Function to read CSV file and return data as array
function readCSV($file, $skipFirstLine)
{
    $data = [];
    if (($handle = fopen($file, 'r')) !== false) {
        if ($skipFirstLine) {
            fgetcsv($handle);
        }

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $data[] = $row;
        }
        fclose($handle);
    }

    return $data;
}

// Function to write data to CSV file
function writeCSV($file, $data)
{
    $handle = fopen($file, 'w');
    foreach ($data as $row) {
        $quoted_row = array_map(function ($item) {
            return '"'.str_replace('"', '""', $item).'"';
        }, $row);
        fwrite($handle, implode(',', $quoted_row)."\n");
    }
    fclose($handle);
}

// Read both CSV files
$subdivision_codes = readCSV('subdivision-codes.csv', true);
$subdivision_2023 = readCSV('2023-2 SubdivisionCodes.csv', false);

// Loop through each record in the first CSV file
foreach ($subdivision_codes as &$row) {
    // Loop through each record in the second CSV file
    foreach ($subdivision_2023 as $subdiv_2023) {
        // Check if the first two columns match
        if ($row[0] == $subdiv_2023[0] && $row[1] == $subdiv_2023[1]) {
            // Update the subdivision type
            $row[] = $subdiv_2023[3];
            break; // Stop searching once matched
        }
    }
}

// Write updated data back to the first CSV file
writeCSV('subdivision-codes-combined.csv', $subdivision_codes);

echo "Subdivision types added successfully!\n<br/>The result: <a href='subdivision-codes-combined.csv'>subdivision-codes-combined.csv</a>";
