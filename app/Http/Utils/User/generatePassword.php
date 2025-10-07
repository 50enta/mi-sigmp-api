<?php

namespace App\Http\Utils\User;

class generatePassword
{
    function returnRandomString()
    {
        return sprintf(
            '%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
        );
    }
}
