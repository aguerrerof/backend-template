<?php

return [
    'card_cannot_be_deleted_because_is_assigned_recurring_order' =>
        'No es posible eliminar esta tarjeta porque está asociada a una o más suscripciones u órdenes recurrentes activas. Para continuar, primero actualiza el método de pago de tus órdenes y luego vuelve a intentarlo.',

    'transaction_could_not_be_completed' =>
        'No pudimos completar tu transacción en este momento. Por favor, inténtalo de nuevo en unos minutos. Si el problema persiste, contacta a nuestro equipo de soporte.',

    'error_otp_process' =>
        'No pudimos verificar tu tarjeta. El código ingresado es incorrecto. Por favor, inténtalo nuevamente.',

    'transaction_not_allowed' =>
        'Tu transacción no pudo ser procesada en este momento. Inténtalo otra vez en unos minutos.',

    'wrong_otp_code' =>
        'El código de seguridad ingresado no es válido. Asegúrate de usar el último código que te enviamos e inténtalo de nuevo.',

    'wrong_credentials' =>
        'Los datos de acceso no son correctos. Verifica tu información e inténtalo nuevamente.',

    'card_exceeded' =>
        'Tu tarjeta ha alcanzado su límite de gasto. Por favor, usa otra tarjeta para completar tu compra.',

    'establishment_not_found' =>
        'No pudimos procesar tu pago debido a un problema con el comercio. Inténtalo nuevamente en unos minutos.',

    'card_blocked' =>
        'Tu tarjeta está bloqueada. Para continuar, comunícate con tu banco y solicita el desbloqueo.',

    'card_not_found' =>
        'No pudimos verificar tu tarjeta. Información incorrecta',

    'address_already_exists' =>
        'La dirección ingresada ya existe',

    'address_not_exists' =>
        'La dirección ingresada no existe',
    'missing_customer_address' => "El cliente no tiene direcciones asignadas.",
    'error_trying_to_process_request' => "Hubo un error al tratar de procesar su solicitud, inténtalo nuevamente en unos minutos.",
    'error_trying_to_get_product_offer_recurrence' => "Error al obtener la oferta de recurrencia del producto",
    'card_registered_successfully' => "Tarjeta de credito registrada exitosamente.",
    'recurring_order_registered_successfully' => "Orden recurrente registrada exitosamente.",
    'order_registered_successfully' => "Su orden ha sido creada correctamente.",
    'error_trying_to_get_registering_order' => "Hubo un error al tratar de crear su orden, inténtalo nuevamente en unos minutos.",
    'payment_order_could_not_be_processed' => "No pudimos procesar tu orden. El cobro con la tarjeta seleccionada no se realizó correctamente. Por favor, intenta nuevamente con otra tarjeta.",
    'cart_was_cleared_successfully' => "El carrito de compras fue vaciado con éxito.",
    'identification_already_registered' => 'La identificación ya se encuentra registrada.',
    'login' => "Iniciar Sesión",
    'session_expired' => 'Tu sesion expiro. Por favor inicia sesion nuevamente.',
    'users' => "Usuarios",
    'is_admin' => "Es administrador?",
    'per-page' => "Resultados por página",
    'access_denied' => "Acceso denegado",
    'access_denied_full_message' => "No tienes permiso para ver esta página.",
    'forgot-your-password' => "¿Olvidaste tu contraseña?",
    'forgot-password-text' => "¿Olvidaste tu contraseña? No hay problema. Simplemente indícanos tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña y podrás elegir una nueva.",
    'create_user' => "Registrar nuevo usuario",
    'create_logistic_provider' => "Registrar nuevo proveedor logístico",
    'name' => "Nombre",
    'save' => "Guardar",
    'password' => "Contraseña",
    'password_confirmation' => "Confirmar Contraseña",
    'wrong_format_email' => "Email incorrecto",
    'email_already_exists' => "Email ya se encuentra registrado",
    'edit_user' => "Editar Usuario",
    'orders' => "Ordenes",
    'logistic-providers' => "Proveedores Logísticos",
    'invalid-date-range-selected' => 'El rango de fechas no puede superar un mes',
    'user_was_deleted_successfully' => 'El usuario fue borrado correctamente.',
    'user_is_already_deleted' => 'Su cuenta fue eliminada previamente. ¿Desea reactivarla?',
    'user_was_restored_successfully' => 'Su cuenta fue restaurada exitosamente.',
    'user_was_already_restored_successfully' => 'Su cuenta ya fue restaurada exitosamente.',
    'user_has_already_default_address' => 'Ya existe una dirección predeterminada para este usuario.',
    'error_trying_to_get_product_inventory' => 'Hubo un error al tratar de obtener el inventario del producto.',
    'error_logs' => 'Errores',
    'system_operations' => 'Operaciones del sistema',
    'system_cache_cleared_successfully' => 'El cache del sistema ha sido limpiado éxitosamente.',
    'run_recurring_payments_cron' => 'Correr cron de pagos recurrentes.',
    'run_recurring_payments_cron_description' => 'Ejecuta el proceso en segundo plano que cobra a los clientes con suscripciones recurrentes activas.',
    'clear_system_cache' => 'Borrar caché del sistema.',
    'clear_cache' => 'Borrar caché',
    'run_cron' => 'Ejecutar cron',
    'select_date' => 'Seleccione una fecha',
    'cron_executed' => 'Cron fue ejecutado con éxito',
    'clear_system_cache_description' => 'Borra todas las cachés de la aplicación (configuración, rutas y vistas). Útil cuando los cambios no parecen aplicarse.',
    'sync_discounts_from_shopify' => 'Sincronizar descuentos de shopify',
    'sync_discounts_from_shopify_description' => 'Actualiza los descuentos de Shopify en nuestro sistema automáticamente',
    'recurring_orders' => 'Ordenes recurrentes',
    'graphql_sandbox_shopify' => 'Graphql sandbox de shopify',
    'logout' => 'Cerrar sesión',
    'activity_logs' => 'Actividad',
    'app_mobile_settings' => 'Configuraciones App Móvil',
    'order' => 'Orden',
    'fulfillment' => 'Cumplimiento de pedido',
    'provider' => 'Proveedor',
    'create_fulfillment' => 'Crear cumplimiento de pedido',
    'edit_profile' => 'Editar perfil',
    'edit_profile_description' => 'Actualiza la información del perfil y la dirección de correo electrónico de tu cuenta.',
    'update_password' => 'Actualizar contraseña',
    'update_password_description' => 'Asegúrate de que tu cuenta use una contraseña larga y aleatoria para mantenerla segura.',
    'current_password'=>'Contraseña actual',
    'new_password'=>'Nueva contraseña',
    'confirm_password'=>'Confirmar contraseña',
    'device_already_assigned_to_user'=>'El usuario ya tiene asignado a este dispositivo.',
    'fulfillment_already_assigned'=>'Ya existe una orden de despacho en curso para esta orden.',
    'fulfillment_cancelled_success'=>'El despacho fue cancelado correctamente y el proveedor fue notificado.',
    'fulfillment_cancellation_instruction'=>'Al cancelar este pedido se avisará automáticamente al proveedor logístico (:provider) y se registrará como cancelado en el sistema.',
    'fulfillment_created_success'=>'El despacho se creó correctamente.',
    'only_can_cancel_fulfillment_one_day_before_delivery_date' => 'Solo se puede cancelar un despacho hasta un día antes de la fecha de entrega.',
    'fulfillment_cannot_be_created_if_weight_is_zero' => 'No se puede registrar porque el peso total de los productos no está definido o es igual a 0.',
    'fulfillment_weight_exceeds_limit' => 'El peso total no puede exceder los :limit kg permitidos por el proveedor :provider.',
    'fulfillment_weight_exceeds_absolute_limit' => 'El peso total no puede exceder los :limit kg permitidos por el sistema.',
    'support' => 'Soporte',
    'support_rate_limited' => 'Alcanzaste el limite de tickets. Intenta de nuevo mas tarde.',
];
