<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Formats an event's `starts_at` (ISO8601, from the events API) into
 * "Qui, 25 Set · 19h" — shared by Home's teaser, the Eventos list and
 * EventoDetail, which all show the same date shape.
 */
final class EventDateFormatter
{
    private const WEEKDAYS = [
        0 => 'Dom', 1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sáb',
    ];

    private const MONTHS = [
        1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
        5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
        9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez',
    ];

    public static function when(string $iso): string
    {
        $parsed = Carbon::parse($iso);
        $weekday = self::WEEKDAYS[$parsed->dayOfWeek];
        $month = self::MONTHS[$parsed->month];

        return "{$weekday}, {$parsed->day} {$month} · {$parsed->format('H')}h";
    }
}
