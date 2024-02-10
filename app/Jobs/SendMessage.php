<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Telegram\Bot\Laravel\Facades\Telegram;

class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $student;
    protected $message;
    protected $time;

    public function __construct(Student $student, $message, $time)
    {
        $this->student = $student;
        $this->message = $message;
        $this->time = $time;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tutorStudent = TutorStudent::where('student_id', $this->student->id)->first();

        if ($tutorStudent) {
            $tutor = Tutor::find($tutorStudent->tutor_id);
            if ($tutor) {
                $name = $this->student->name;
                $telegram_chat_id = $tutor->telegram_chat_id;
                if ($telegram_chat_id) {
                    Telegram::sendMessage([
                        'chat_id' => $tutor->telegram_chat_id,
                        'text' => 'Se ha registrado una ' . $this->message . ' de ' . $name . ' a las ' . $this->time . ' horas'
                    ]);
                }
            }
        }
    }
}
