<?php

namespace App\Mail\General;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserAddManually extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $instructor;

    public function __construct($instructor,$user)
    {
        $this->user = $user;
        $this->instructor = $instructor;
    }


    public function build()
    {
        return $this->view('mail.General.new_student_reg_via_email', [
            'user' => $this->user,
            'instructor'=>$this->instructor,
        ])->subject('New instructor added you as a student');
    }
}
