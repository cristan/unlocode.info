<?php

class DescriptionBuilder
{
    // Functions we're willing to name in a description, in priority order.
    // 'To be specified', 'Postal exchange office', and unknown/future codes are
    // intentionally absent — they will be silently skipped.
    // Only include function types that are unambiguous and meaningful to a general audience.
    // Airport, rail terminal, road terminal etc. are connectivity codes in the UN/LOCODE
    // standard — they don't reliably identify what a location *is*, so we leave them out.
    private const PRIORITY = [
        'port'            => 1,
        'border crossing' => 2,
    ];

    // The official ISO country names are used everywhere on the site, but some are
    // unsuitable for natural prose (bureaucratic suffixes, comma-inverted forms, etc.).
    // This list overrides just those names in descriptions.
    private const COUNTRY_NAME_OVERRIDES = [
        'Bolivia, Plurinational State of'         => 'Bolivia',
        'Congo, The Democratic Republic of the'   => 'Democratic Republic of the Congo',
        'Iran, Islamic Republic of'               => 'Iran',
        "Korea, Democratic People's Republic of"  => 'North Korea',
        'Korea, Republic of'                      => 'South Korea',
        "Lao People's Democratic Republic"        => 'Laos',
        'Marshall Islands (the)'                  => 'Marshall Islands',
        'Micronesia, Federated States of'         => 'Micronesia',
        'Moldova, Republic of'                    => 'Moldova',
        'Northern Mariana Islands (the)'          => 'Northern Mariana Islands',
        'Palestine, State of'                     => 'Palestine',
        'Philippines (the)'                       => 'Philippines',
        'Russian Federation (the)'                => 'Russia',
        'Sudan (the)'                             => 'Sudan',
        'Syrian Arab Republic (the)'              => 'Syria',
        'Taiwan (Province of China)'              => 'Taiwan',
        'Tanzania, United Republic of'            => 'Tanzania',
        'Turks and Caicos Islands (the)'          => 'Turks and Caicos Islands',
        'United Arab Emirates (the)'              => 'United Arab Emirates',
        'United States of America (the)'          => 'United States',
        'Holy See (Vatican City State)'           => 'Vatican City',
        'Venezuela (Bolivarian Republic of)'      => 'Venezuela',
        'Virgin Islands (British)'                => 'British Virgin Islands',
        'Virgin Islands (U.S.)'                   => 'U.S. Virgin Islands',
        'Falkland Islands (Malvinas)'             => 'Falkland Islands',
    ];

    public static function build(
        string  $unlocode,
        string  $name,
        ?array  $functions,
        ?string $regionName,
        string  $countryName,
        ?object $degreesCoordinates
    ): string {
        $countryName = self::COUNTRY_NAME_OVERRIDES[$countryName] ?? $countryName;
        $desc = "{$unlocode} is the UN/LOCODE for {$name}";

        $primaryFunc = self::pickPrimaryFunction($functions);
        if ($primaryFunc !== null) {
            $article = in_array($primaryFunc[0], ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a';
            $desc .= ", {$article} {$primaryFunc}";
        }

        $desc .= $regionName
            ? " in {$regionName}, {$countryName}."
            : " in {$countryName}.";

        if ($degreesCoordinates !== null) {
            $desc .= " Coordinates: {$degreesCoordinates->latitude}, {$degreesCoordinates->longitude}.";
        }

        return $desc;
    }

    /**
     * Returns the single most relevant function label, or null if none is
     * worth showing (postal exchange, to be specified, #NAME? data errors, etc.).
     */
    private static function pickPrimaryFunction(?array $functions): ?string
    {
        if (empty($functions)) {
            return null;
        }

        $best = null;
        $bestPriority = PHP_INT_MAX;

        foreach ($functions as $f) {
            $lower = strtolower($f);
            if (isset(self::PRIORITY[$lower]) && self::PRIORITY[$lower] < $bestPriority) {
                $best = $lower;
                $bestPriority = self::PRIORITY[$lower];
            }
        }

        return $best;
    }
}
