<?php

class CoordinatesConverter {
    public function convertCoordinates($input) {
        $coordinatesRegex = '/^(\d{2})(\d{2})([NS])\s+(\d{3})(\d{2})([EW])$/';
        
        // Extract latitude and longitude parts
        preg_match($coordinatesRegex, $input, $latMatch);
    
        // Check if the input format is valid
        if ($latMatch) {
            // Extract degrees, minutes, and direction
            $latDegrees = intval($latMatch[1]);
            $latMinutes = $latMatch[2];
            $latDirection = $latMatch[3];
            $lonDegrees = intval($latMatch[4]);
            $lonMinutes = $latMatch[5];
            $lonDirection = $latMatch[6];
    
            // Calculate decimal coordinates with proper sign for direction
            $decimalLat = ($latDirection === 'S' ? "-" : "") . number_format(($latDegrees + ($latMinutes / 60)), 5, '.', '');
            $decimalLon = ($lonDirection === 'W' ? "-" : "") . number_format(($lonDegrees + ($lonMinutes / 60)), 5, '.', '');
    
            // Return the result as an object
            $coordinates = new stdClass();
            $coordinates->latitude = $decimalLat;
            $coordinates->longitude = $decimalLon;
            return $coordinates;
        } else {
            throw new Exception("Invalid coordinate format $input");
        }
    }
}
?>