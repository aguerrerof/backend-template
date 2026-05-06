<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Mi API de Laravel",
 *      description="Documentación de la API de ejemplo",
 *      @OA\Contact(
 *          email="soporte@example.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Servidor principal de la API"
 * )
 *
 * @OA\SecurityScheme(
 *      type="http",
 *      description="Autenticación basada en app token",
 *      name="Bearer",
 *      in="header",
 *      scheme="bearer",
 *      bearerFormat="APP-TOKEN",
 *      securityScheme="bearerAuth",
 * )
 */
abstract class Controller
{
    //
}
