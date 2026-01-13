<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use Illuminate\Http\Request;

class NipGenerator
{
    public static function generate(): string
    {
        $last = Pessoa::lockForUpdate()
            ->orderBy('created_at', 'desc')
            ->value('nip');

            $next = $last
            ? ((int) str_replace('NIP', '', $last)) + 1
            : 10000;

        return 'NIP' . $next;
    }
}
