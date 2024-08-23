<?php

namespace App\Features\WhatsApp\Domain\Repositories;

use Illuminate\Http\JsonResponse;

interface WhatsAppRepository
{
    function sendWhatsAppMessage($recipient, $messageBody, $previewUrl): JsonResponse;

}
