<?php

class FunctionCodeConverter {
    private $functionMap = array(
        '0' => "To be specified",
        '1' => "Port",
        '2' => "Rail terminal",
        '3' => "Road terminal",
        '4' => "Airport",
        '5' => "Postal exchange office",
        '6' => "Multimodal Functions (ICDs, etc.)",
        '7' => "Fixed Transport Functions (e.g. Oil platform)",
        '8' => "Inland port",
        'B' => "Border crossing"
    );

    public function convertFunctionCodesToArray($codes) {
        $result = array();
        foreach (str_split($codes) as $code) {
            if ($code == "-") {
                // Nothing, ignore
            }
            else if (isset($this->functionMap[$code])) {
                $result[] = $this->functionMap[$code];
            } else {
                $result[] = "Unknown Function Code ". $code;
            }
        }
        return $result;
    }

    public function hasAirportFunction($codes) {
        return in_array('4', str_split($codes));
    }
}
?>