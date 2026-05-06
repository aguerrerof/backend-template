<?php

namespace App\Models\Enums;

enum UrbanoWebhookStatus: string
{
    case PS = 'PS'; // Pedido Solicitado
    case PC = 'PC'; // Pedido Cancelado
    case PR = 'PR'; // Pedido Asignado
    case PP = 'PP'; // Llegamos a la Tienda/Agencia
    case PU = 'PU'; // Pedido Recolectado
    case PD = 'PD'; // En Punto de Entrega/Destino
    case EC = 'EC'; // Entrega Completada
    case DV = 'DV'; // Devolucion del pedido
    case NR = 'NR'; //Pedido no recibido
    case EF = 'EF'; //Entrega fallida
    case EP = 'EP'; //Entrega Parcial

    public static function fromCode(?string $code): ?self
    {
        $normalized = strtoupper(trim((string)$code));
        return self::tryFrom($normalized);
    }

    public function toFulfillmentStatus(): FulfillmentStatus
    {
        return match ($this) {
            self::PS => FulfillmentStatus::BOOKED,
            self::PC => FulfillmentStatus::CANCELLED,
            self::PR => FulfillmentStatus::IN_TRANSIT,
            self::PP => FulfillmentStatus::DISPATCHED,
            self::PU => FulfillmentStatus::PICKED_UP,
            self::PD => FulfillmentStatus::OUT_FOR_DELIVERY,
            self::EC => FulfillmentStatus::DELIVERED,
            self::DV,self::NR => FulfillmentStatus::RETURNED,
            self::EF,self::EP => FulfillmentStatus::ISSUE,
        };
    }
}
