<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Leads;
use Illuminate\Console\Command;

class SumSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sum-send {transaction_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transaction = Transaction::query()->find($this->argument('transaction_id'));

        $amoApi = (new Client(Account::query()->first()))->init();

        $contact = $amoApi
            ->service
            ->contacts()
            ->find($transaction->contact_id);

        $lead = $amoApi
            ->service
            ->leads()
            ->find($transaction->lead_id);

        $transaction->all_sum = $lead->sale;

        $leads = Leads::searchActivePays($contact, $amoApi, PIPELINE_ID, $transaction->agreement);

        $countLeads = $leads->count();

        $partSum = $lead->sale / $countLeads;

        foreach ($leads as $lead) {

            $lead->sale = $partSum;
            $lead->save();
        }

        $transaction->leads_count_last = $countLeads;
        $transaction->part_sum = $partSum;
        $transaction->status = 1;
        $transaction->save();
    }
}
