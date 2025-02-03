<?php

class StatusCodeConverter
{
    private $statusCodesMap = [
        'AA' => 'Approved by competent national government agency',
        'AC' => 'Approved by Customs Authority',
        'AF' => 'Approved by national facilitation body',
        'AI' => 'Code adopted by international organisation (IATA, ECLAC, EUROSTAT, etc.)',
        'AM' => 'Approved by the UN/LOCODE Maintenance Agency',
        'AQ' => 'Entry approved, functions not verified',
        'AS' => 'Approved by national standardisation body',
        'RL' => 'Recognised location',
        'RN' => 'Request from credible national sources for locations in their own country',
        'RQ' => 'Request under consideration',
        'RR' => 'Request rejected',
        'QQ' => 'Original entry not verified since date indicated',
        'UR' => "Entry included on user's request; not officially approved",
        'XX' => 'Entry that will be removed from the next issue of UN/LOCODE',
    ];

    public function convert($statusCode)
    {
        // There are a very limited amount of entries who don't have a status. Return null to avoid the warning "Undefined array key"
        if (! $statusCode) {
            return null;
        }

        return $this->statusCodesMap[$statusCode];
    }
}
