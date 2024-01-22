<?php

namespace Database\Seeders;

use App\Models\HistoryMessage;
use App\Models\Message;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HistoryMessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = Message::all();
        foreach ($messages as $message) {
            if ($message->active) {
                switch ($message->type) {
                    case 'check_in':
                        HistoryMessage::create([
                            'message_id' => $message->id,
                        ]);
                    break;
                    case 'check_out':
                        HistoryMessage::create([
                            'message_id' => $message->id,
                        ]);
                    break;
                }
            };
        }
    }
}
