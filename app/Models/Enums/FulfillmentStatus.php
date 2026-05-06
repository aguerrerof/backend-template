<?php

namespace App\Models\Enums;

use App\Models\LogisticProvider;

enum FulfillmentStatus: string
{
    case PENDING = 'PENDING';               // Pedido creado, aún no recogido
    case PICKED_UP = 'PICKED_UP';           // Recogido por el transportista
    case IN_WAREHOUSE = 'IN_WAREHOUSE';     // En bodega del proveedor
    case IN_TRANSIT = 'IN_TRANSIT';         // En ruta hacia destino
    case OUT_FOR_DELIVERY = 'OUT_FOR_DELIVERY'; // En zona de entrega
    case RETURNING = 'RETURNING';           // En devolución hacia el remitente
    case DELIVERED = 'DELIVERED';           // Entregado al destinatario
    case RETURNED = 'RETURNED';             // Devuelto al remitente
    case ISSUE = 'ISSUE';                   // Novedad o siniestro
    case CANCELLED = 'CANCELLED';           // Cancelado manualmente
    case BOOKED = 'BOOKED';                 // Reservado, si aplica
    case DISPATCHED = 'DISPATCHED';

    public static function fromProvider(LogisticProvider $logisticProvider, string $providerStatus): ?self
    {
        if ($logisticProvider->code != 'LAAR') {
            return null;
        }
        $map = [
            2 => self::PENDING,
            4 => self::IN_WAREHOUSE,
            5 => self::IN_TRANSIT,
            6 => self::OUT_FOR_DELIVERY,
            6.01,
            '6,01' => self::RETURNING,
            7 => self::DELIVERED,
            14 => self::ISSUE,
            8 => self::CANCELLED
        ];
        return $map[$providerStatus] ?? null;
    }

    public static function all(array $excludes = [], array $only = []): array
    {
        $statuses = array_map(fn ($case) => $case->value, self::cases());

        if (!empty($excludes)) {
            $statuses = array_diff($statuses, $excludes);
        }
        if (!empty($only)) {
            $statuses = array_filter($statuses, fn ($case) => in_array($case, $only));
        }

        return array_values($statuses);
    }

    public static function getSteps(): array
    {
        return [
            '1' => self::PENDING,
            '2' => self::BOOKED,
            '3' => self::DISPATCHED,
            '4' => self::IN_TRANSIT,
            '5' => self::DELIVERED,
            '6' => self::CANCELLED,
        ];
    }

    public static function getDefaultStep(): int
    {
        return array_search(
            self::getDefault(),
            self::getSteps(),
            true
        );
    }

    public static function getDefault(): FulfillmentStatus
    {
        return self::PENDING;
    }

    public static function getActualStepByStatus(string $status): int
    {
        return match ($status) {
            self::PENDING->value => 1,
            self::BOOKED->value => 2,
            self::IN_WAREHOUSE->value,
            self::DISPATCHED->value,
            self::PICKED_UP->value,
            self::OUT_FOR_DELIVERY->value => 3,
            self::IN_TRANSIT->value,
            self::ISSUE->value => 4,
            self::DELIVERED->value => 5,
            self::CANCELLED->value => 6,
            default => 0
        };
    }
}
