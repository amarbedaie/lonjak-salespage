<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AffiliateController extends Controller
{
    public function index()
    {
        $ref = substr(hash('crc32b', (string) Auth::id()), 0, 8);
        $link = config('app.url').'/?ref='.$ref;

        return view('dashboard.affiliate', compact('link'));
    }
}
