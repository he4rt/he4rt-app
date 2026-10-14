<?php

declare(strict_types=1);

namespace App\Support;

/**
 * PT-BR labels for the Events API's enums — mirrors heartdevs.com's
 * app-modules/events/lang/pt_BR/enums.php, which this app has no access
 * to. Shared by the Eventos list and EventoDetail (full precision —
 * Home's teaser pill uses its own smaller, deliberately-collapsed map).
 */
final class EventPresenter
{
    /** Mirrors He4rt\Events\Event\Enums\EventType. */
    private const TYPE_LABELS = [
        'meetup' => 'Meetup',
        'workshop' => 'Workshop',
        'conference' => 'Conferência',
    ];

    /** Mirrors He4rt\Events\Enrollment\Enums\EnrollmentMethod. */
    private const METHOD_LABELS = [
        'rsvp' => 'RSVP',
        'rsvp_checkin' => 'RSVP + Check-in',
        'application' => 'Inscrição',
    ];

    /** Mirrors He4rt\Events\Enrollment\Enums\EnrollmentStatus. */
    private const ENROLLMENT_LABELS = [
        'pending' => 'Pendente',
        'confirmed' => 'Confirmado',
        'waitlisted' => 'Lista de espera',
        'checked_in' => 'Check-in realizado',
        'attended' => 'Presente',
        'cancelled' => 'Cancelado',
        'rejected' => 'Rejeitado',
        'no_show' => 'Não compareceu',
    ];

    /** success/primary/secondary/destructive — matches this app's theme tokens. */
    private const ENROLLMENT_TOKENS = [
        'pending' => 'primary',
        'confirmed' => 'success',
        'waitlisted' => 'secondary',
        'checked_in' => 'success',
        'attended' => 'success',
        'cancelled' => 'destructive',
        'rejected' => 'destructive',
        'no_show' => 'destructive',
    ];

    public static function typeLabel(string $type): string
    {
        return self::TYPE_LABELS[$type] ?? $type;
    }

    public static function methodLabel(?string $method): ?string
    {
        return $method === null ? null : self::METHOD_LABELS[$method] ?? $method;
    }

    public static function enrollmentLabel(?string $status): ?string
    {
        return $status === null ? null : self::ENROLLMENT_LABELS[$status] ?? $status;
    }

    public static function enrollmentToken(?string $status): string
    {
        return $status === null ? 'primary' : self::ENROLLMENT_TOKENS[$status] ?? 'primary';
    }
}
