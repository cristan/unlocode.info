<?php

class RemarksConverter {
    private $changeMap = array(
        '@Coo' => "Coordinates changed or added",
        '@Fun' => "Functions changed",
        '@Sta' => "Status changed",
        '@Sub' => "Subdivision code added or changed",
        '@Nam' => "Location name changed",
        '@Spe' => "Spelling of name corrected",
    );

    public function convertRemarks($remarks) {
        $result = $remarks;

        foreach ($this->changeMap as $key => $value) {
            $result = str_replace($key, "<abbr title='$value'>$key</abbr>", $result);
        }

        // cf US BIO, US ENC, US JOY
        // Test with https://unlocode.info/USBGM
        $patternGeneral = "/(?<![A-Z])(([A-Z0-9]{2})\s?([A-Z0-9]{3}))(?![A-Z])/";
        $replacementGeneral = "<a href='https://unlocode.info/$2$3'>$1</a>";
        $result = preg_replace($patternGeneral, $replacementGeneral, $result);

        // Not really needed because the thing above, but sometimes people put Cf without a space like https://unlocode.info/BRCNF
        $replacementCf = "$1<a href='https://unlocode.info/$3$4'>$2</a>";
        $patternCf = "/([cC][fF]\s)(([A-Z0-9]{2})\s?([A-Z0-9]{3}))/";
        $result = preg_replace($patternCf, $replacementCf, $result);

        // // Remark: Use CN XNN (CNXNT) (also an example to test that it's to be removed next issue)
        // $patternUse = "/([uU]se\s)(([A-Z0-9]{2})\s?([A-Z0-9]{3}))/";
        // $result = preg_replace($patternUse, $replacement, $result);

        // Test with https://unlocode.info/USBSO
        // US BSO Apt, US TRI Apt
        // $patternAirport = "/(([A-Z0-9]{2})\s?([A-Z0-9]{3}))( Apt)/";
        // $replacement = "<a href='https://unlocode.info/$2$3'>$1</a>$4";
        // $result = preg_replace($patternAirport, $replacement, $result);

        // Red herring: https://unlocode.info/BEABK (has Collective entry reqd by EUROSTAT)
        return $result;
    }
}
?>