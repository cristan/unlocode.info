<?php

class CoordinatesConverter {
    private $coordinatesRegex = '/^(\d{2})(\d{2})([NS])\s+(\d{3})(\d{2})([EW])$/';

    public function convertToDecimal($input) {
        // Extract latitude and longitude parts
        preg_match($this->coordinatesRegex, $input, $matches);
    
        // Check if the input format is valid
        if (!$matches) {
            throw new Exception("Invalid coordinate format $input");
        }

        // Extract degrees, minutes, and direction
        $latDegrees = intval($matches[1]);
        $latMinutes = $matches[2];
        $latDirection = $matches[3];
        $lonDegrees = intval($matches[4]);
        $lonMinutes = $matches[5];
        $lonDirection = $matches[6];

        // Calculate decimal coordinates with proper sign for direction
        $decimalLat = ($latDirection === 'S' ? "-" : "") . number_format(($latDegrees + ($latMinutes / 60)), 5, '.', '');
        $decimalLon = ($lonDirection === 'W' ? "-" : "") . number_format(($lonDegrees + ($lonMinutes / 60)), 5, '.', '');

        // Return the result as an object
        $coordinates = new stdClass();
        $coordinates->latitude = $decimalLat;
        $coordinates->longitude = $decimalLon;
        return $coordinates;
    }

    public function convertToDegrees($input) {
        preg_match($this->coordinatesRegex, $input, $matches);
    
        // Check if the input format is valid
        if (!$matches) {
            throw new Exception("Invalid coordinate format $input");
        }

        // Extract degrees, minutes, and direction
        $latDegrees = intval($matches[1]);
        $latMinutes = $matches[2];
        $latDirection = $matches[3];
        $lonDegrees = intval($matches[4]);
        $lonMinutes = $matches[5];
        $lonDirection = $matches[6];

        // Add decimals when needed
        $latDegrees = sprintf("%02d", $latDegrees);
        $latMinutes = sprintf("%02d", $latMinutes);
        $lonDegrees = sprintf("%03d", $lonDegrees);
        $lonMinutes = sprintf("%02d", $lonMinutes);

        $coordinates = new stdClass();
        $coordinates->latitude = $latDegrees . '°' . $latMinutes . "'" . $latDirection;
        $coordinates->longitude = $lonDegrees . '°' . $lonMinutes . "'" . $lonDirection;
        return $coordinates;
    }
}
?>