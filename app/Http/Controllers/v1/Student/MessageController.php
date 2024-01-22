<?php

namespace App\Http\Controllers\v1\Student;

require __DIR__ . '../../../../../../vendor/autoload.php';

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Models\SentMessage;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function sendMessage()
    {
        // Your Account SID and Auth Token from twilio.com/console
        // To set up environmental variables, see http://twil.io/secure
        $account_sid = getenv('TWILIO_ACCOUNT_SID');
        $auth_token = getenv('TWILIO_AUTH_TOKEN');
        // In production, these should be environment variables. E.g.:
        // $auth_token = $_ENV["TWILIO_AUTH_TOKEN"]

        // A Twilio number you own with SMS capabilities
        $twilio_number = '+13344544864';
        $client = new Client($account_sid, $auth_token);
        $time = getDate();
        $bodyMessage = 'Mensaje de prueba para probar 😎'.$time['hours'].' '.$time['minutes'];
        $to = '+529513947132';

        $client->messages->create(
            // Where to send a text message (your cell phone?)
            $to,
            array(
                'from' => $twilio_number,
                'body' => $bodyMessage
            )
        );

        $sentMessage = new SentMessage();
        $sentMessage->to = $to; // Reemplaza esto con el número de teléfono correcto
        $sentMessage->from = $twilio_number; // Reemplaza esto con el número de teléfono correcto
        $sentMessage->body = $bodyMessage;
        $sentMessage->save();
        Log::channel('daily')->debug('Mensaje guardado con exito');
    }
}
