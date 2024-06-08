<?php

/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */
declare(strict_types = 1);

namespace coso\report;

use DateTimeInterface;
use JetBrains\PhpStorm\ArrayShape;
use Stringable;
use Throwable;

/**
 * Totals & sub totals from arrays
 *
 * $totalIt = new TotalIt();
 * $totales = $totalIt->totals($notas, ['total_rolls', 'total_quantity']);
 * // Con subTotales
 * $subTotals = $totalIt->subTotalsBy($notas, ['total_rolls', 'total_quantity'], ['entrada_salida', 'tipo']);
 *
 * checar $totales vs SELECT COUNT(*), SUM(total_rolls) as rolls, SUM(total_quantity) as quantity FROM nota_bodega; -- agregar avg, min, max
 * checar $subTotales vs
 *  SELECT entrada_salida, COUNT(*), SUM(total_rolls) as rolls, SUM(total_quantity) as quantity FROM nota_bodega GROUP BY 1;  -- agregar avg, min, max
 *  y
 *  SELECT tipo, COUNT(*), SUM(total_rolls) as rolls, SUM(total_quantity) as quantity FROM nota_bodega GROUP BY 1;  -- agregar avg, min, max
 *
 *
 *
 *  subTotalArray = ['fecha'=>
 *   function($subTotalValue, array $row, int|string $totalKey,  int|string $subTotalKey):int|string {return $subTotalValue; } // igual que el valor del campo
 *
 * @see
 *    FormatIt: Ayudantes de Formatear, para subTotalBy, ie 'Y-m',
 *    RoundIt:  Ayudantes de Redondear, para subTotalBy ie: RoundTo($value, 1000) redondea a miles
 */
class TotalIt {
    public const ARRAY_SHAPE_DATA = //array<int|string, array<>>
        [
            'int|string' => [
                'int|string' => ['int|string, string|Stringable|int|float|bool|DateTimeInterface|null']
            ]
        ];

    public const ARRAY_SHAPE_GRAND_TOTAL = [
        'string' => [
            'count' => 'int|null',
            'sum' => 'int|float|null',
            'min' => 'mixed', 'avg' => 'int|float', 'max' => 'mixed'
        ]
    ];
    // array{string:array{count:int|null, sum:int|float|null, min:mixed, avg:int|float, max:mixed}}

    /* por si sub totaliza por DateTimeInterface usa este formato para los subtotales y no se usa callback */
    protected string $defaultDateFormat;

    /**
     * @param string $defaultDateFormat por si se sub totliza por DateTimeInterface usa este formato para los subtotales y no se usa callback
     */
    public function __construct(string $defaultDateFormat = 'Y-m-d') {
        $this->defaultDateFormat = $defaultDateFormat;
    }


    /**
     * Foreach $totalsFor element calculate totals from $data
     *
     * @param null|false|array<int|string, array<int|string, string|Stringable|int|float|bool|DateTimeInterface|null>> $data { {colName:value,...},...}
     * @param array<int|string, int|string|Stringable> $totalsFor [totalColName, ...]
     * @return array<string, array<string, bool|DateTimeInterface|float|int|string|Stringable|null>>
     */
    #[ArrayShape(self::ARRAY_SHAPE_GRAND_TOTAL)]
    public function totals(array|bool|null $data, array $totalsFor):array {
        if(empty($data)) {
            return [];
        }
        $grandTotal = [];
        foreach($totalsFor as $totalKey) {
            try {
                $d = array_column($data, (string)$totalKey);
            } catch(Throwable) {
                continue;
            }
            $count = count($d);
            try {
                $sum = array_sum($d);
            } catch(Throwable) {
                $sum = 0;
            }
            $grandTotal[(string)$totalKey] = [
                'count' => $count,
                'sum' => $sum,
                'min' => min($d),
                'avg' => $count === 0 ? 0 : $sum/$count,
                'max' => max($d),
            ];
        }
        return $grandTotal;
    }

    /**
     * @param null|false|array<int|string, array<int|string, string|Stringable|int|float|bool|DateTimeInterface|null>> $data
     * @param array<int|string, string|Stringable|int> $totalsFor ie ['sales', 'feetOrPounds']
     * @param array<int|string, int|string|array<string|Stringable|Callable|Closure>> $subTotalsByValuesOf ie [sales => currency, units => [callable]]
     * @return array<string, array<string, bool|DateTimeInterface|float|int|string|Stringable|null>>
     */
    #[ArrayShape(['int|string'=>['int|string' => self::ARRAY_SHAPE_GRAND_TOTAL]])]
    public function subTotalsBy($data,$totalsFor, $subTotalsByValuesOf):array {
        if(empty($data)) {
            return [];
        }
        $totalsFor = $this->stringableArrayToStringArray($totalsFor);
        /** @var array<int|string, array<int|string, mixed>> $grandTotal */
        $grandTotal = [];
        foreach($data as $d) {
            foreach($totalsFor as $totalKey) {
                if( !array_key_exists($totalKey, $d)) {
                    continue;
                }
                $value = $this->toValue($d[$totalKey]);
                foreach($subTotalsByValuesOf as $keyBy => $by) {
                    if(!is_array($by)) {
                        $this->subTotalBy(
                            $value,
                            $totalKey,
                            (string)$this->toValue($d[$this->toValue($by)] ?? ''),
                            $grandTotal
                        );
                        continue;
                    }
                    foreach($by as $b) {
                        if(is_callable($b)) {
                            $subBy = (string)$b($d[$keyBy] ?? '', $d, $totalKey, $keyBy);
                        } else {
                            $subBy = (string)$this->toValue($d[$this->toValue($b)] ?? '');
                        }
                        $this->subTotalBy(
                            $value,
                            $totalKey,
                            $subBy,
                            $grandTotal
                        );
                    }
                }
            }
        }
        foreach($grandTotal as &$g) {
            $g = $this->average($g);
        }
        return $grandTotal;
    }

/////

    /**
     * @param mixed $value
     * @param int|string $totalKey
     * @param string $subTotalBy
     * @param array $grandTotal
     * @return void
     */
    protected function subTotalBy($value,  $totalKey, $subTotalBy, array &$grandTotal):void {
        if($subTotalBy === '' || $subTotalBy === null) {
            return;
        }
        if( !array_key_exists($subTotalBy, $grandTotal[$totalKey] ?? [])) {
            $grandTotal[$totalKey][$subTotalBy] = [
                'count' => 1,
                'sum' => is_numeric($value) ? $value : 0,
                'min' => $value,
                'avg' => 0,
                'max' => $value,
            ];
            return;
        }
        $subTotal = &$grandTotal[$totalKey][$subTotalBy];
        $subTotal['count']++;
        if(is_numeric($value)) {
            $subTotal['sum'] += $value;
        }
        if($subTotal < $subTotal['min']) {
            $subTotal['min'] = $value;
        }
        if($subTotal > $subTotal['max']) {
            $subTotal['max'] = $value;
        }
    }

    /**
     * @param  array<string, array<string, bool|DateTimeInterface|float|int|string|Stringable|null>> $grandTotal
     * @return  array<string, array<string, bool|DateTimeInterface|float|int|string|Stringable|null>>
     */
    protected function average($grandTotal):array {
        foreach($grandTotal as &$total) {
            if(is_numeric($total['sum'] ?? [] )) {
                $total['avg'] = $total['count'] === 0 ? 0 : $total['sum'] / $total['count'];
            }
        }
        return $grandTotal;
    }

    /**
     * @param string|Stringable|int|float|bool|array|null|DateTimeInterface $mixed
     * @return string|int|float|bool
     */
    protected function toValue(mixed $mixed):string|int|float|bool {
        if(is_null(($mixed))) {
            return '';
        }
        if(is_array($mixed)) {
            foreach($mixed as &$m) {
                $m = $this->toValue($m);
            }
            return implode(', ', $mixed);
        }
        if($mixed instanceof Stringable) {
            return (string)$mixed;
        }
        if($mixed instanceof DateTimeInterface) {
            return $mixed->format($this->defaultDateFormat);
        }
        return $mixed;
    }

    /**
     * @param array<int|string, int|string|Stringable> $keyValue
     * @return array<int|string, int|string>
     */
    protected function stringableArrayToStringArray(array $keyValue):array {
        foreach($keyValue as &$v) {
            if($v instanceof Stringable) {
                $v = (string)$v;
            }
        }
        return $keyValue;
    }

}
