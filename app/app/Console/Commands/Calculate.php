<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\amoCRM\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Calculate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate {lead_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle(): void
    {
        $amoApi = (New Client(Account::query()->first()))->init();

        $lead = $amoApi->service->leads()->find($this->argument('lead_id'));

        $number = $lead->cf('Номер договора')->getValue();
        $countPays = $lead->cf('Количество платежей')->getValue();
        $dateNextPay = $lead->cf('Дата первого платежа')->getValue();

//        $contact = $lead->contact;

        if ($lead->cf('Срок выплат')->getValue() == 'Ежемесячно')
            $saleLead = round($lead->sale * $lead->cf('Процентная ставка % числом')->getValue() / 100 / 12);
        else
            $saleLead = round($lead->sale * $lead->cf('Процентная ставка % числом')->getValue() / 100 / 12) * 3;

        if ($lead->cf('Тип договора')->getValue() == 'ЮЛ+ФЛ') {

            $saleLead = $saleLead - ($saleLead / 100 * 13);
        }

        for ($createdLeads = 0; $countPays != $createdLeads; ) {

            $copy = clone $lead;
            $copy->name = 'Выплата №'.++$createdLeads.' #'.$number;

            $copy->sale = $saleLead;
            $copy->cf('Дата платежа')->setValue($dateNextPay);
            $copy->status_id = 68804058;
            $copy->save();

            if ($lead->cf('Срок выплат')->getValue() == 'Ежемесячно')
                $dateNextPay = Carbon::parse($dateNextPay)->addMonth()->format('Y-m-d');
            else
                $dateNextPay = Carbon::parse($dateNextPay)->addMonths(3)->format('Y-m-d');

//            $leadCreate = $contact->createLead();
//            $leadCreate->name = 'Выплата №'.$createdLeads.' #'.$number;
//            $leadCreate->created_at = $lead->created_at;
//            $leadCreate->cf('Номер договора')->setValue($number);
//            $leadCreate->save();
        }

        $copy->sale = $lead->sale + $saleLead;
        $copy->save();

        Log::debug(__METHOD__, [
            'count leads' => $createdLeads,
            'sale leads'  => $saleLead,
        ]);
    }
}
