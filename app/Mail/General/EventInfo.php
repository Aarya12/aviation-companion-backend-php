<?php

namespace App\Mail\General;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventInfo extends Mailable
{
    use Queueable, SerializesModels;

    private $event;
    private $instructor;

    public function __construct($instructor,$event,$location)
    {

        $this->event = $event;
        $this->instructor = $instructor;
        $this->location = $location;
    }


    public function build()
    {
        //dd($this->event);
        return $this->view('mail.General.event_info', [
            'event' => $this->event,
            'instructor'=>$this->instructor,
            'location'=>$this->location,
        ])->subject('New event created');
    }
}
