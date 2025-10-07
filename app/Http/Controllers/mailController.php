<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\Log;

class mailController extends Controller
{
    function getMailData($data)
    {
        $mailData = [
            'from' => 'noreply@samcol.co.mz',
            'fromname' => 'SAMCOL',
        ];

        foreach ($data as $key) {
            $mailData[$key['key']] = $key['value'];
        }

        return $mailData;
    }

    function sendEmail($email_data, $component)
    {
        try {
            Mail::send($component, $email_data, function ($message) use ($email_data) {
                $message->to($email_data['recipient'])
                    ->from($email_data['from'], $email_data['fromname'])
                    ->subject($email_data['subject']);
            });

            return 1;
        } catch (Exception $e) {
            Log::info($e);
            return 0;
        }
    }

    function passwordReset($email, $password)
    {
        //TODO desenhar template de envio de senha para o reset
        $object[] = ['key' => 'subject', 'value' => "Password Reset"];
        $object[] = ['key' => 'recipient', 'value' => $email];
        $object[] = ['key' => 'password', 'value' => $password];
        $object[] = ['key' => 'reset', 'value' => true];

        if ($this->sendEmail($this->getMailData($object), 'newUserEmail') == 1) {
            return 1;
        } else {
            return 0;
        }
    }
}
