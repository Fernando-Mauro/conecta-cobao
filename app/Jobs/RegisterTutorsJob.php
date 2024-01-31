<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorStudent;
use App\Models\User;
use App\Traits\StudentTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Jobs\RegisterTutorsJob;

class RegisterTutorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tutors;

    /**
     * Create a new job instance.
     */
    public function __construct($tutors)
    {
        //
        $this->tutors = $tutors;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->tutors as $tutorRequest) {
            $name = $tutorRequest['nombre'];
            $phone = $tutorRequest['telefono'];
            $campus = $tutorRequest['plantel'];
            $email = $tutorRequest['email'];
            $password = $tutorRequest['contraseña'];
            $curp = $tutorRequest['curp'];
            $this->createTutor($name, $phone, $campus, $email, $password, $curp);
        }
    }
    
    public function createTutor($name, $phone, $campus, $email, $password, $curp)
    {
        try {
            // Verificar si el usuario ya existe
            $user = User::where('email', $email)->first();
    
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password)
                ]);
    
                $role = Role::where('name', 'tutor')->first();
                $user->assignRole($role);
            }
    
            // Verificar si el tutor ya existe
            $tutor = Tutor::where('user_id', $user->id)->first();
    
            if (!$tutor) {
                $tutor = Tutor::create([
                    'name' => $name,
                    'phone' => $phone,
                    'campus' => $campus,
                    'user_id' => $user->id
                ]);
            }
    
            $student = Student::where('curp', $curp)->first();
    
            if ($student) {
                // Verificar si la relación tutor-estudiante ya existe
                $tutorStudent = TutorStudent::where('tutor_id', $tutor->id)
                                            ->where('student_id', $student->id)
                                            ->first();
    
                if (!$tutorStudent) {
                    TutorStudent::create([
                        'tutor_id' => $tutor->id,
                        'student_id' => $student->id
                    ]);
                }
            } else {
                throw new Exception('Estudiante no encontrado');
            }
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Estudiante no encontrado') {
                \Log::info('Estudiante no encontrado');
            } else {
                \Log::info('Error al resgistrar el tutor');
            }
        }
    }
}
