<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Payment;

use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class PaymentController extends Controller
{
    //

    public function __construct()
    {
        Configuration::setXenditKey(env('XENDIT_SECRET_KEY'));
    }

    public function createInvoice(Request $request)
    {
        try {
            $order = new Payment;
            $order->user_id = $request->input('user_id');
            $order->external_id = (string) Str::uuid();
            $order->amount = $request->input('amount');
            $order->payer_email = $request->input('payer_email');
            $order->description = $request->input('description');
            $createInvoice = new CreateInvoiceRequest([
                'external_id' => $order->external_id,
                'amount' => $request->input('amount'),
                'payer_email' => $request->input('payer_email'),
                'description' => $request->input('description'),
                'invoice_duration' => 172800,
            ]);


            $apiInstance = new InvoiceApi();
            $generateInvoice = $apiInstance->createInvoice($createInvoice);
            $order->checkout_link = $generateInvoice['invoice_url'];
            $order->save();
            return response()->json([
                'message' => 'Invoice created',
                'checkout_link' => $order->checkout_link,
            ]);


        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function handleWebhook(Request $request)
    {
        $data = $request->all();
        $external_id = $data['external_id'];
        $status = strtolower($data['status']);
        $payment_method = $data['payment_method'];


        $order = Payment::where('external_id', $external_id)->first();
        $order->status = $status;
        $order->payment_method = $payment_method;
        $order->save();


        return response()->json([
            'message' => 'Webhook received',
            'status' => $status,
            'payment_method' => $payment_method,
        ]);
    }
}
