<?php

namespace App\Http\Controllers\v1\Student;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class WaController extends Controller
{
    public function sendMessage($phone, $name, $enrollment, $time, $template)
    {
        try {
            $token = getenv('FACEBOOK_AUTH_TOKEN');
            // $phone = getenv('FACEBOOK_PHONE');
            $url = 'https://graph.facebook.com/v17.0/136038262931701/messages';
            Log::channel('daily')->debug('Token: ' . $token . '');
            // Configuración del mensaje
            $message =
                '{
                "messaging_product": "whatsapp",
                "to": "' . $phone . '",
                "type": "template",
                "template": {
                    "name": "' . $template . '",
                    "language": {
                        "code": "es_MX"
                    },
                    "components" : [{
                        "type": "body",
                        "parameters": [
                            {
                                "type": "text",
                                "text": "' . $name . '"
                            },
                            {
                                "type": "text",
                                "text": "' . $enrollment . '"
                            },
                            {
                                "type": "text",
                                "text": "' . $time . '"
                            }
                        ]
                    }]
                }
            }';

            // Definimos las cabeceras
            $header = array("Authorization: Bearer " . $token, "Content-Type: application/json");

            // Iniciamos el cURL
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $message);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            // Realizamos la solicitud cURL
            $response = json_decode(curl_exec($curl), true);

            // Verificamos si ocurrió un error en la respuesta
            if ($response === false) {
                throw new Exception("Error en la solicitud cURL: " . curl_error($curl));
            }

            // Verificamos si la respuesta contiene un error de la API
            if (isset($response['error'])) {
                throw new Exception("Error en la respuesta de la API: " . $response['error']['message']);
            }

            // Cerramos la solicitud cURL
            curl_close($curl);

            return Response::json([
                'data' => $response
            ], 200);
        } catch (Exception $err) {
            return Response::json([
                'error' => 'Hubo un error al enviar el mensaje ' . $err->getMessage()
            ], 500);
        }
    }

    public function WaRecibe()
    {
        /* VERIFICACION DEL WEBHOOK */
        //TOQUEN QUE QUERRAMOS PONER 
        $token = 'registros';

        //RETO QUE RECIBIREMOS DE FACEBOOK
        $palabraReto = $_GET['hub_challenge'];
        //TOQUEN DE VERIFICACION QUE RECIBIREMOS DE FACEBOOK
        $tokenVerificacion = $_GET['hub_verify_token'];
        //SI EL TOKEN QUE GENERAMOS ES EL MISMO QUE NOS ENVIA FACEBOOK RETORNAMOS EL RETO PARA VALIDAR QUE SOMOS NOSOTROS
        if ($token === $tokenVerificacion) {
            echo $palabraReto;
            exit;
        }
        /* RECEPCION DE MENSAJES*/
        //LEEMOS LOS DATOS ENVIADOS POR WHATSAPP
        $respuesta = file_get_contents("php://input");
        //CONVERTIMOS EL JSON EN ARRAY DE PHP
        $respuesta = json_decode($respuesta, true);
        //EXTRAEMOS EL TELEFONO DEL ARRAY
        $mensaje = "Telefono:" . $respuesta['entry'][0]['changes'][0]['value']['messages'][0]['from'] . "</br>";
        //EXTRAEMOS EL MENSAJE DEL ARRAY
        $mensaje .= "Mensaje:" . $respuesta['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'];
        //GUARDAMOS EL MENSAJE Y LA RESPUESTA EN EL ARCHIVO text.txt
        file_put_contents("text.txt", $mensaje);
    }
}
