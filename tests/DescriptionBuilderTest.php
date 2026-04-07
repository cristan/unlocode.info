<?php

require_once __DIR__ . '/../details/DescriptionBuilder.php';
require_once __DIR__ . '/../details/functionCodeConverter.php';
require_once __DIR__ . '/../details/coordinatesConverter.php';

use PHPUnit\Framework\TestCase;

class DescriptionBuilderTest extends TestCase
{
    private FunctionCodeConverter $converter;
    private CoordinatesConverter $coordinatesConverter;

    protected function setUp(): void
    {
        $this->converter            = new FunctionCodeConverter();
        $this->coordinatesConverter = new CoordinatesConverter();
    }

    private function functionNames(string $code): array
    {
        return $this->converter->convertFunctionCodesToArray($code);
    }

    private function coordinatesDegrees(string $rawCoords): object
    {
        return $this->coordinatesConverter->convertToDegrees($rawCoords);
    }

    // ── Port ──────────────────────────────────────────────────────────────────

    public function test_port_beats_rail_terminal_road_terminal_and_airport(): void
    {
        $this->assertSame(
            "SEGOT is the UN/LOCODE for Göteborg, a port in Västra Götalands län, Sweden. Coordinates: 57°43'N, 011°58'E.",
            DescriptionBuilder::build('SEGOT', 'Göteborg', $this->functionNames('12345---'), 'Västra Götalands län', 'Sweden', $this->coordinatesDegrees('5743N 01158E'))
        );
    }

    public function test_port_beats_road_terminal_and_airport(): void
    {
        $this->assertSame(
            "AEAUH is the UN/LOCODE for Abu Dhabi, a port in Abū Z̧aby [Abu Dhabi], United Arab Emirates. Coordinates: 24°28'N, 054°22'E.",
            DescriptionBuilder::build('AEAUH', 'Abu Dhabi', $this->functionNames('1-345---'), 'Abū Z̧aby [Abu Dhabi]', 'United Arab Emirates (the)', $this->coordinatesDegrees('2428N 05422E'))
        );
    }

    // ── Border crossing ───────────────────────────────────────────────────────

    public function test_border_crossing_only(): void
    {
        $this->assertSame(
            "BHKFC is the UN/LOCODE for King Fahed Causeway, a border crossing in Ash Shamālīyah, Bahrain. Coordinates: 26°11'N, 050°19'E.",
            DescriptionBuilder::build('BHKFC', 'King Fahed Causeway', $this->functionNames('-------B'), 'Ash Shamālīyah', 'Bahrain', $this->coordinatesDegrees('2611N 05019E'))
        );
    }

    // multimodal is excluded — border crossing still surfaces for INHND
    public function test_border_crossing_when_multimodal_also_present(): void
    {
        $this->assertSame(
            "INHND is the UN/LOCODE for Hemnagar Lcs, a border crossing in West Bengal, India. Coordinates: 22°27'N, 088°58'E.",
            DescriptionBuilder::build('INHND', 'Hemnagar Lcs', $this->functionNames('-----6-B'), 'West Bengal', 'India', $this->coordinatesDegrees('2227N 08858E'))
        );
    }

    // ── Function codes excluded from description ───────────────────────────────
    // 'To be specified' and 'Postal exchange office' say nothing useful about a location.

    public function test_to_be_specified_excluded(): void
    {
        $this->assertSame(
            'AEARZ is the UN/LOCODE for Arzanah Island in United Arab Emirates.',
            DescriptionBuilder::build('AEARZ', 'Arzanah Island', $this->functionNames('0-------'), null, 'United Arab Emirates (the)', null)
        );
    }

    public function test_postal_exchange_excluded(): void
    {
        $this->assertSame(
            'ARPOC is the UN/LOCODE for Pocitos in Salta, Argentina.',
            DescriptionBuilder::build('ARPOC', 'Pocitos', $this->functionNames('----5---'), 'Salta', 'Argentina', null)
        );
    }
}
