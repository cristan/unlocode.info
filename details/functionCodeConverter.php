<?php

class FunctionCodeConverter
{
    private $functionMap = [
        '0' => 'Not officially functional',

        // Original: Maritime transport (sea port or maritime port)
        '1' => 'Maritime transport (sea port or maritime port)',
        '2' => 'Rail transport',
        '3' => 'Road transport',

        // Original: Air transport (airport) or space transport (spaceport)
        '4' => 'Air transport',

        // Original: "International Mail Processing Centre (IMPC) recognized by the Universal Postal Union (UPU)"
        '5' => 'Mail Processing Centre (IMPC)',
        '6' => 'Multimodal transport facility',

        // Original: Fixed Transport Installation (oil pipeline terminal, electric power lines, ropeway terminals, etc.)
        '7' => 'Fixed Transport Functions (e.g. Oil platform)',

        // Original: Inland water transport (river ports, and lake ports)
        '8' => 'Inland water transport',
        'A' => 'Special Economic Zone (SEZ)',
        'B' => 'Border crossing',
    ];

    public function convertFunctionCodesToArray($codes)
    {
        $result = [];
        foreach (str_split($codes) as $code) {
            if ($code == '-') {
                // Nothing, ignore
            } elseif (isset($this->functionMap[$code])) {
                $result[] = $this->functionMap[$code];
            } else {
                $result[] = 'Unknown Function Code '.$code;
            }
        }

        return $result;
    }

    public function hasAirportFunction($codes)
    {
        return in_array('4', str_split($codes));
    }
}
