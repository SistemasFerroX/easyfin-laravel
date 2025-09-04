<?php

namespace App\Services;

use Carbon\Carbon;

class AmortizacionService
{
    /**
     * Genera tabla de amortización a cuota fija.
     * @param float $monto        Principal
     * @param float $tasaMensual  En decimal (ej. 2.2% => 0.022)
     * @param int   $meses        Plazo en meses
     * @param Carbon|null $inicio Fecha primera cuota (default: próximo mes)
     * @return array ['cuota','rows','total_intereses','total_pagado']
     */
    public static function generar(float $monto, float $tasaMensual, int $meses, ?Carbon $inicio = null): array
    {
        $r = max(0, $tasaMensual);
        $n = max(1, $meses);
        $cuota = $r > 0 ? $monto * ($r / (1 - pow(1 + $r, -$n))) : $monto / $n;

        $rows = [];
        $saldo = $monto;
        $sumInt = 0;
        $sumTot = 0;
        $inicio = ($inicio ?? now())->copy()->addMonth()->startOfDay();

        for ($i = 1; $i <= $n; $i++) {
            $interes = round($saldo * $r, 2);
            $capital = round($cuota - $interes, 2);
            if ($i === $n) { // ajusta centavos finales
                $capital = round($saldo, 2);
                $cuota   = round($capital + $interes, 2);
            }
            $saldo = round($saldo - $capital, 2);

            $rows[] = [
                'n'       => $i,
                'fecha'   => $inicio->copy()->addMonths($i - 1),
                'cuota'   => $cuota,
                'interes' => $interes,
                'capital' => $capital,
                'saldo'   => max($saldo, 0),
            ];
            $sumInt += $interes;
            $sumTot += $cuota;
        }

        return [
            'cuota'           => round($cuota, 2),
            'rows'            => $rows,
            'total_intereses' => round($sumInt, 2),
            'total_pagado'    => round($sumTot, 2),
        ];
    }
}
