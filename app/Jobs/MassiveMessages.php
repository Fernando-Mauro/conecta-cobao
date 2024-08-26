<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;

class MassiveMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $message;
    protected $chat_id;
    
    public function __construct($message, $chat_id)
    {
        $this->message = $message;
        $this->chat_id = $chat_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Telegram::sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $this->message
        ]);

        Message::create([
            'type' => 'Aviso Urgente',
            'body' => 'Aviso de que no hay wifi',
            'description' => 'Aviso de que no hay wifi el 26/08/24',
            'active' => true
        ]);
    }
}
