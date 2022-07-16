<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

if (!function_exists('sendMail')) {
    function sendMail($name, $email, $phone, $message, $subject)
    {
        $input_arr = array(
            'name' => $name,
            'email' => $email,
            'contactNo' => $phone,
            'message' => "Testing Message",
            'subject' => $subject,
        );
        $input_arr['msg'] = $message;
        $result = Mail::send('email.contact', $input_arr, function ($message) use ($input_arr) {
            $message->to($input_arr['email'], 'Contact US')
                ->subject($input_arr['subject']);
            $message->from('no-reply@geoteam', 'Laravel CSV Upload Notification');
        });
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}



