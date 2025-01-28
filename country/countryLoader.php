<?php

include '../database.php';

class CountryLoader
{
    public function loadCountry($countryCode)
    {
        $rows = $this->loadEntriesInCountry($countryCode);
        $mapped = $this->map($rows, $countryCode);
        $this->addRegionToEntriesWithDuplicateName($mapped, $countryCode, $rows);

        // TODO: do something with the items who are there twice with different spelling?

        return $mapped;
    }

    private function loadEntriesInCountry($countryCode)
    {
        $connection = setupDb();

        $sql = 'SELECT location, name, subdivision FROM `CodeList` where country = ?';
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('s', $countryCode);
        $stmt->execute();

        $resultSet = $stmt->get_result();

        return $resultSet->fetch_all(MYSQLI_ASSOC);
    }

    private function map($unmappedRows, $countryCode)
    {
        $convertFunc = function ($entry) use ($countryCode) {
            $converted = new stdClass();
            $converted->unlocode = $countryCode.$entry['location'];
            $converted->name = $entry['name'];
            $converted->subdivision = $entry['subdivision'];

            return $converted;
        };

        return array_map($convertFunc, $unmappedRows);
    }

    private function addRegionToEntriesWithDuplicateName($mapped, $countryCode, $unmappedRows)
    {
        $nameLookup = [];
        foreach ($unmappedRows as $unmappedEntry) {
            $name = $unmappedEntry['name'];
            $unlocode = $countryCode.$unmappedEntry['location'];
            $nameLookup[$name][] = $unlocode;
        }

        foreach ($mapped as $mappedEntry) {
            $unlocode = $mappedEntry->unlocode;
            $name = $mappedEntry->name;

            if (isset($nameLookup[$name]) && count($nameLookup[$name]) > 1) {
                $mappedEntry->name .= ', '.$mappedEntry->subdivision;
            }
        }
    }
}
