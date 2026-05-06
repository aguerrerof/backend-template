<?php

return [
    'messages' => [
        'PENDING' => [
            'title' => 'Preparando pedido',
            'body' => 'Tu pedido ha sido creado y se está preparando. Pronto el transportista lo recogerá.',
            'progress' => 10,
        ],
        'BOOKED' => [
            'title' => 'Orden creada',
            'body' => 'Tu pedido está listo, un transportista lo recogerá pronto! Pronto el transportista retirará tu pedido de nuestra bodega.',
            'progress' => 25,
        ],
        'PICKED_UP' => [
            'title' => 'Delivery asignado',
            'body' => '¡Tu pedido está listo! Un transportista lo recogerá pronto. Pronto el transportista retirará tu pedido de nuestra bodega.',
            'progress' => 25,
        ],
        'IN_WAREHOUSE' => [
            'title' => 'En bodega',
            'body' => '¡El transportista ya tiene tu pedido! Tu pedido estará en camino a ti en las próximas horas.',
            'progress' => 50,
        ],
        'IN_TRANSIT' => [
            'title' => 'En tránsito',
            'body' => 'Tu pedido está en camino hacia su destino.',
            'progress' => 60,
        ],
        'OUT_FOR_DELIVERY' => [
            'title' => 'En ruta a destino',
            'body' => '¡Las compras de tus mascotas serán entregadas pronto! Es probable que el delivery te contacte, debes estar atento.' ,
            'progress' => 75,
        ],
        'DELIVERED' => [
            'title' => 'Entregado',
            'body' => '¡Tu pedido fue entregado! Tu mascota al fin tiene lo que necesita',
            'progress' => 100,
        ],
        'RETURNING' => [
            'title' => 'En devolución',
            'body' => 'El pedido está siendo devuelto al remitente.',
            'progress' => 50,
        ],
        'RETURNED' => [
            'title' => 'Devuelto',
            'body' => 'Tu pedido fue devuelto y cancelado.',
            'progress' => 100,
        ],
        'EXCEPTION' => [
            'title' => 'Novedad con tu envío',
            'body' => 'Se presentó una novedad o retraso con tu pedido. Nuestro equipo ya lo está revisando.',
            'progress' => 50,
        ],
        'CANCELLED' => [
            'title' => 'Entrega cancelada',
            'body' => 'El transportista canceló la entrega de tu pedido. Estamos generando un nuevo envío para que lo recibas lo antes posible.',
            'progress' => 0,
        ],
    ],
];
