<?php

namespace App\Http\Controllers\v1\Whatsapp;

use App\Features\WhatsApp\Domain\Repositories\WhatsAppRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use PHPUnit\Exception;

class WhatsappController extends Controller
{
    protected $accessToken;

    public function __construct(WhatsAppRepository $whatsappRepository)
    {
        $this->whatsappRepository = $whatsappRepository;
    }

    public function verifyWebhook(Request $request)
    {
        Log::info('WhatsappController@verifyWebhook');

        // Verificar el token de verificación para la configuración del webhook
        $verifyToken = env('WHATSAPP_ACCESS_TOKEN'); // Define este token en tu archivo .env
        $hubMode = $request->input('hub_mode');
        $hubVerifyToken = $request->input('hub_verify_token');
        $hubChallenge = $request->input('hub_challenge');

        if ($hubMode && $hubVerifyToken) {
            if ($hubMode === 'subscribe' && $hubVerifyToken === $verifyToken) {
                Log::info('Webhook verified successfully');
                return response($hubChallenge, 200)->header('Content-Type', 'text/plain');
            } else {
                Log::warning('Webhook verification failed');
                return response('Forbidden', 403);
            }
        }
    }

    /*public function receive(Request $request)
    {
        Log::info('WhatsappController@receive');

        // Verificar el token de verificación para la configuración del webhook
        $verifyToken = env('WHATSAPP_ACCESS_TOKEN'); // Define este token en tu archivo .env
        $hubMode = $request->input('hub_mode');
        $hubVerifyToken = $request->input('hub_verify_token');
        $hubChallenge = $request->input('hub_challenge');

        if ($hubMode && $hubVerifyToken) {
            if ($hubMode === 'subscribe' && $hubVerifyToken === $verifyToken) {
                Log::info('Webhook verified successfully');
                return response($hubChallenge, 200)->header('Content-Type', 'text/plain');
            } else {
                Log::warning('Webhook verification failed');
                return response('Forbidden', 403);
            }
        }
    }*/
    public function holaMundo(Request $request)
    {
        try {
            Log::info('holaMundo');
            $templateName = 'hello_world';
            $message = 'Hola mundo pro desde la api de laravel';
            $numero = '+529513572252';

            $response = $this->whatsappRepository->sendWhatsAppMessage($numero, $message, true);
            $response = $response->getContent();
            Log::debug($response);

        }catch (\Exception $exception){
            Log::error($exception->getMessage());
        }

    }
    public function handle(Request $request)
    {
        Log::info('WhatsappController@handle');
        Log::debug('Request data: ', $request->all());

        // 3. Procesar otras solicitudes que no sean de verificación del webhook
        $this->processWebhookRequest($request);

        return response()->json(['status' => 'success']);
    }
    /**
     * Procesa la solicitud recibida en el webhook.
     *
     * @param Request $request
     */
    protected function processWebhookRequest(Request $request)
    {
        // Procesar el mensaje recibido
        Log::info('processWebhookRequest');

        // Asegurarse de que el campo 'field' existe en el request
        if (!isset($request['entry'][0]['changes'][0]['field'])) {
            Log::warning('Field not found in request.');
            return;
        }

        // Determinar el valor del campo 'field' y procesarlo en consecuencia
        $field = $request['entry'][0]['changes'][0]['field'];

        switch ($field) {
            case 'messages':
                Log::debug('messages');
                // Procesar mensajes
                if (isset($request['entry'][0]['changes'][0]['value']['messages'])) {
                    $messages = $request['entry'][0]['changes'][0]['value']['messages'];
                    foreach ($messages as $message) {
                        $from = $message['from']; // Número de teléfono del remitente
                        $text = $message['text']['body']; // Texto del mensaje
                        $messageId = $message['id']; // ID del mensaje

                        // Procesar el mensaje o almacenarlo en tu base de datos
                        Log::info("Message from $from: $text");

                        // Aquí puedes agregar lógica para responder al mensaje
                        if($text === 'Hola') {
                            $recipient = $request['entry'][0]['changes'][0]['value']['messages'][0]['from'];
                            $recipientLast10Digits = '52'.substr($recipient, -10);

                            Log::debug($recipientLast10Digits);
                            $messageBody = '¿Que hace?';
                            $this->whatsappRepository->sendWhatsAppMessage($recipientLast10Digits, $messageBody, true);
                        }
                    }
                }
                break;

            case 'message_echoes':
                Log::debug('message_echoes');
                // Procesar ecos de mensajes
                if (isset($request['entry'][0]['changes'][0]['value'])) {
                    $value = $request['entry'][0]['changes'][0]['value'];
                    Log::debug($value);
                }
                break;

            default:
                // Manejar otros casos o valores inesperados
                Log::warning("Unhandled field type: $field");
                break;
        }
    }



}
