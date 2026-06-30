<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Salespage;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function landing(\App\Services\SalespageGenerator $gen)
    {
        $demo = $gen->mock([
            'name' => 'Serum Glow Booster', 'price' => 89, 'comparePrice' => 159,
            'category' => 'Kecantikan', 'audience' => 'wanita yang mahukan kulit berseri',
            'problem' => 'kulit kusam & tak sekata', 'benefits' => 'Hasil 7 hari, bahan semula jadi, sesuai semua kulit', 'tone' => 'santai',
        ]);

        return view('landing', ['demo' => $demo]);
    }

    public function show(string $slug)
    {
        $salespage = Salespage::where('slug', $slug)->where('status', 'live')->firstOrFail();
        $salespage->increment('visits');

        return view('public.salespage', compact('salespage'));
    }

    public function order(string $slug, Request $request)
    {
        $salespage = Salespage::where('slug', $slug)->where('status', 'live')->firstOrFail();

        $data = $request->validate([
            'customer' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:30',
            'address' => 'required|string|max:500',
            'state' => 'required|string|max:100',
            'qty' => 'required|integer|min:1|max:99',
        ]);

        $order = Order::create([
            'user_id' => $salespage->user_id,
            'salespage_id' => $salespage->id,
            'customer' => $data['customer'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'address' => $data['address'],
            'state' => $data['state'],
            'product_name' => $salespage->product_name,
            'qty' => $data['qty'],
            'total' => (float) $salespage->price * $data['qty'],
            'status' => 'baru',
            'payment_status' => 'belum',
        ]);

        \App\Services\Mailer::orderReceived($order);

        // Online payment uses the salespage OWNER's own BayarCash; else COD/manual.
        $gateway = \App\Services\BayarcashGateway::forMerchant($salespage->user);
        if ($gateway && $salespage->gateway === 'BayarCash') {
            return $gateway->createPayment($order);
        }

        return redirect()->route('salespage.public', $slug)->with('ordered', true);
    }
}
