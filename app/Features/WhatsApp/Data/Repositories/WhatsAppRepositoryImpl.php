<?php

namespace App\Features\WhatsApp\Data\Repositories;

use App\Features\WhatsApp\Domain\Repositories\WhatsAppRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppRepositoryImpl implements WhatsAppRepository
{
    protected $accessToken;
    protected $whatsAppUrlApi;

    public function __construct()
    {
        // Inicializar el token de acceso desde la variable de entorno
        $this->accessToken = env('WHATSAPP_ACCESS_TOKEN');
        $this->whatsAppUrlApi = env('WHATSAPP_API_URL');
    }

    public function sendWhatsAppMessage($recipient, $messageBody, $previewUrl = true): JsonResponse
    {
        $endpointUrl = 'messages';
        $fullUrl = $this->whatsAppUrlApi . $endpointUrl;

        $payload = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $recipient,
            "type" => "text",
            "text" => [
                "preview_url" => $previewUrl,
                "body" => $messageBody
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->accessToken}"
        ])->post($fullUrl, $payload);

        if ($response->successful()) {
            Log::debug($response->body());
            return response()->json($response->json());
        } else {
            $statusCode = $response->status();
            $error = $response->json();
            throw new \Exception("Error $statusCode: " . json_encode($error));
        }
    }
}
