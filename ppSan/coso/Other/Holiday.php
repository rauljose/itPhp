<?php

namespace coso\Other;

use Stringable;

/**
 *  '' = 'exact date'
 *  frases: 'yearly', 'monthly', 'next monday', 'previous monday'
 *  date('Y-m-d D', strtotime('last thursday of november 2022'));
* $MLK = date('Y-m-d', strtotime("january $curYir third monday")); //marthin luthor king day
* $PD = date('Y-m-d', strtotime("february $curYir third monday")); //presidents day
* $Est =  date('Y-m-d', easter_date($curYir))); // easter
* $MDay = date('Y-m-d', strtotime("may $curYir first monday")); // memorial day
* $LD = date('Y-m-d', strtotime("september $curYir first monday"));  //labor day
* $CD = date('Y-m-d', strtotime("october $curYir third monday")); //columbus day
* $TH = date('Y-m-d', strtotime("november $curYir first thursday")); // thanks giving
 * https://gist.github.com/rinogo/eec3c121613d7080439f9cbc1101387d unit-tested class to calculate federal holiday dates for the United States
 * https://gist.github.com/rinogo Convert exported/downloaded Facebook friends file into a simple list of names or links
 *
 * MEX
 * I. El 1o. de enero;
 *
* II. El primer lunes de febrero en conmemoración del 5 de febrero;
 *
* III. El tercer lunes de marzo en conmemoración del 21 de marzo;
* Jue/Vie Santo (Bancario)
* IV. El 1o. de mayo;
 *
* V. El 16 de septiembre
* 2 Nov (Bancario)
* VI. El tercer lunes de noviembre en conmemoración del 20 de noviembre;
 *
* VII. El 1o. de diciembre de cada seis años, cuando corresponda a la transmisión del Poder Ejecutivo Federal;
* 12 Dic  (Bancario)
* VIII. El 25 de diciembre,
 *
 */

class Holidays {
    protected array $holidayDefinition;
    protected array $calculated;
    protected array $years;

    public function add(string|Stringable $strToTime, string|Stringable|null $celebratesShort, string|Stringable|null $celebratesLong ) {
        $this->holidayDefinition[] =
            [
                'abr' => $celebratesShort ?? $celebratesLong ?? '',
                'celebrates' => $celebratesLong ?? $celebratesShort ?? '',
                'str' => $strToTime,
            ];
    }

    public function addUsHolidays():void {
        $this->add("january YEAR third monday", 'MLK', 'Marthin Luther King Day', );
    }

    public function addMexHolidays():void {
        $this->add('YEAR-01-01', 'Año Nuevo' );
    }
}