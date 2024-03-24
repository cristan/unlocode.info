<?php
include('../database.php');
include('../countryList.php');
include('coordinatesConverter.php');
include('statusConverter.php');
include('functionCodeConverter.php');
include('remarksConverter.php');

class DetailsLoader {
    
    private function loadLocation($country, $location, $connection) {
        $sql = "SELECT * FROM `CodeList` where country = ? and location = ?";
        $stmt = $connection->prepare($sql); 
        $stmt->bind_param("ss", $country, $location);
        $stmt->execute();
        $result = $stmt->get_result();
        $location = $result->fetch_assoc();

        if (!$location) {
            return $location;
        }
    
        $names = array();
        $names[] = $location['name'];
        while ($row = $result->fetch_assoc()) {
            $names[] = $row['name'];
        }
    
        $location['names'] = $names;
    
        return $location;
    }

    private function loadOtherLocationsWithIATA($country, $location, $iata, $connection) {
        // TODO: use PDO instead to have the same element multiple times
        $sql = "SELECT distinct country, location, subdivision, coordinates FROM `CodeList` WHERE not (country = ? and location = ?) and ((location = ? and function like '%4%') OR IATA = ?);";
        $stmt = $connection->prepare($sql); 
        $stmt->bind_param("ssss", $country, $location, $iata, $iata);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function getOtherIATAs($country, $location, $subdivision, $subdivisionName, $iata, $decimalCoordinates, $connection) {
        $fromDB = $this->loadOtherLocationsWithIATA($country, $location, $iata, $connection);
        $otherIATAs = array();
        $coordinatesConverter = new CoordinatesConverter();
        foreach ($fromDB as $dbEntry) {
            $otherIATA = new stdClass();

            $countryCode = $dbEntry['country'];
            $otherIATA->unlocode = $countryCode . $dbEntry['location'];
            $entryCoordinates = $dbEntry['coordinates'];
            $otherIATA->warning = null;
            $entrySubdivision = $dbEntry['subdivision'];
            if ($countryCode != $country) {
                $otherIATA->warning = "Note: this entry is in another country. It is not possible for these to actually share an IATA.";
            } else if ($entrySubdivision && $subdivision && $subdivision != $entrySubdivision) {
                $otherIATA->warning = "Note: this entry is in another ". strtolower($subdivisionName) .". It is not possible for these to actually share an IATA.";
            } else if ($decimalCoordinates && $entryCoordinates) {
                $entryDecimalCoordinates = $coordinatesConverter->convertCoordinates($entryCoordinates);
                $distanceMeters = $this->vincentyGreatCircleDistance($decimalCoordinates->latitude, $decimalCoordinates->longitude, $entryDecimalCoordinates->latitude, $entryDecimalCoordinates->longitude);
                $distanceKm = round($distanceMeters / 1000);
                if ($distanceKm > 1000) {
                    $otherIATA->warning = "Note: this entry is $distanceKm km away. It is not possible for these to actually share an IATA.";
                }
            }
            $otherIATAs[] = $otherIATA;
        }
        return $otherIATAs;
    }
    
    private function loadRegion($country, $code, $connection) {
        $sql = "SELECT * FROM `subdivision` where countryCode = ? and code = ? limit 1";
        $stmt = $connection->prepare($sql); 
        $stmt->bind_param("ss", $country, $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $region = $result->fetch_assoc();
        return $region;
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Vincenty formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public static function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
    
        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
        pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
    
        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }

    private function enrich($country, $location, $connection, $locationFromDb) {
        $name = $locationFromDb['name'];
        $subdivision = $locationFromDb['subdivision'];
        
        global $countries;
        $countryName = $countries[$country] ?? $country;
        
        $toReturn = new stdClass();
        $unlocode = $country . $location;
        if ($subdivision) {
            $toReturn->title = "$unlocode: $name - $subdivision - $countryName";
            $toReturn->header = "<a href='/country/$country'>$country</a>$location: $name - $subdivision";
        } else {
            $toReturn->title = "$unlocode: $name - $countryName";
            $toReturn->header = "<a href='/country/$country'>$country</a>$location: $name";
        }
        
        $toReturn->names = $locationFromDb['names'];
        $ch = $locationFromDb['ch'];
        $toReturn->entryToBeRemoved = $ch == 'x' || $ch == 'X';
        
        $toReturn->country = $countryName;
        $toReturn->countryCode = $country;

        $statusCodeConverter = new StatusCodeConverter();
        $toReturn->status = $statusCodeConverter->convert($locationFromDb['status']);

        // Subdivision
        $toReturn->subdivision = $subdivision;
        if ($subdivision) {
            $region = $this->loadRegion($country, $subdivision, $connection);
            $toReturn->regionType = $region['type'] ?? "Region";
            $toReturn->regionName = $region['name'] ?? null;
        }
        $toReturn->description = "Details for UNLOCODE $unlocode: $name in ". ($toReturn->regionName ?? $subdivision) .", $toReturn->country. Discover functions, coordinates and more.";
        
        // Coordinates
        $coordinates = $locationFromDb['coordinates'];
        $toReturn->coordinates = $coordinates;
        $toReturn->decimalCoordinates = null;
        if ($coordinates) {
            $coordinatesConverter = new CoordinatesConverter();
            $toReturn->decimalCoordinates = $coordinatesConverter->convertCoordinates($coordinates);
        }

        // Functions
        $iataOverride = $locationFromDb['IATA'];
        $toReturn->IATA = $iataOverride;
        $toReturn->possibleIATA = null;
        $function = $locationFromDb['function'];
        if (empty($function)) {
            $toReturn->functions = null;
        } else {
            $functionCodeConverter = new FunctionCodeConverter();
            $toReturn->functions = $functionCodeConverter->convertFunctionCodesToArray($function);

            // Whenever an entry has the airport function, the IATA is the location part of the unlocode (unless there's a data error or the airport has no IATA)
            if (!$iataOverride && $functionCodeConverter->hasAirportFunction($function)) {
                $toReturn->possibleIATA = $location;
            }
        }
        
        $iata = $iataOverride != "" ? $iataOverride : $toReturn->possibleIATA;
        $otherLocationsWithSameIata = null;
        if ($iata) {
            $otherLocationsWithSameIata = $this->getOtherIATAs($country, $location, $subdivision, $toReturn->regionType ?? "Region", $iata, $toReturn->decimalCoordinates, $connection);
        }
        $toReturn->otherLocationsWithSameIata = $otherLocationsWithSameIata;

        // Remarks
        $remarks = $locationFromDb['remarks'];
        $toReturn->remarks = null;
        if ($remarks) {
            $remarksConverter = new RemarksConverter();
            $toReturn->remarks = $remarksConverter->convertRemarks($remarks);
        }

        // Date
        $date = $locationFromDb['date'];
        $year = substr($date, 0, 2);
        $toReturn->month = substr($date, 2, 2);
        // The oldest unlocode in the database is from 1980.
        if ($year >= 80) {
            $year = "19$year";
        } else {
            $year = "20$year";
        }
        $toReturn->year = $year;

        return $toReturn;
    }

    public function loadDetails($unlocode) {
        $country = substr($unlocode, 0, 2);
        $location = substr($unlocode, 2, 3);
        $connection = setupDb();
        $locationFromDb = $this->loadLocation($country, $location, $connection);
        if (!$locationFromDb) {
            return null;
        }

        return $this->enrich($country, $location, $connection, $locationFromDb);
    }
}

?>