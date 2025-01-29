<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class HookController extends Controller
{
    public function schedule(Request $request)
    {
        $transaction = Transaction::query()->create([
            'type' => 'schedule',
//            'leads_count_last' => 1,
//            'leads_count_new' => 1,
            'lead_id' => $request->lead_id,
            'contact_id' => $request->contact_id,
            'agreement' => $request->agreement,
//            'part_sum' => '',
//        'all_sum' => '',
            'status' => 0,
            'body' => json_encode($request->toArray()),
        ]);

        Artisan::call('app:schedule-send', ['transaction_id' => $transaction->id]);

    }

    public function sum(Request $request)
    {
        $transaction = Transaction::query()->create([
            'type' => $request->type,
//            'leads_count_last' => 1,
//            'leads_count_new' => 1,
            'lead_id' => $request->lead_id,
            'contact_id' => $request->contact_id,
            'agreement' => $request->agreement,
//            'part_sum' => '',
//        'all_sum' => '',
            'status' => 0,
            'body' => json_encode($request->toArray()),
        ]);

        Artisan::call('app:sum-send', ['transaction_id' => $transaction->id]);
    }

    public function calculate(Request $request)
    {
        $leadId = $request->input('lead_id');


    }
}

