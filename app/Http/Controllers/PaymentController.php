<?php

namespace App\Http\Controllers;

use App\Services\BayarcashGateway;
use App\Services\Mailer;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Browser is redirected here after paying (best-effort).
    public function return(Request $request)
    {
        $order = BayarcashGateway::applyCallback($request->all(), isWebhook: false);

        if ($order && $order->salespage) {
            return redirect()->route('salespage.public', $order->salespage->slug)->with('ordered', true);
        }

        return redirect('/')->with('payment_failed', true);
    }

    // Server-to-server webhook (authoritative). Verify, mark paid, email.
    public function callback(Request $request)
    {
        $order = BayarcashGateway::applyCallback($request->all(), isWebhook: true);

        if ($order) {
            Mailer::orderPaid($order);
        }

        return response('OK', 200);
    }
}
