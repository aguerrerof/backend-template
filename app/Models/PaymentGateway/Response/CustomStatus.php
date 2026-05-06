<?php

namespace App\Models\PaymentGateway\Response;

enum CustomStatus
{
    case SUCCESS;
    case VALIDATION_OTP_REQUIRED;
    case VALIDATION_3D_REQUIRED;
    case WRONG_CREDENTIALS;
    case TRANSACTION_PENDING_3DS_APPROVAL;
    case WRONG_OTP_CODE;
    case CARD_BLOCKED;
    case ESTABLISHMENT_DOES_NOT_EXIST;
    case EXCEEDED;
    case TRANSACTION_DENIED;
}
