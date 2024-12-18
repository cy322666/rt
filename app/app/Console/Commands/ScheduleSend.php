<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Leads;
use Illuminate\Console\Command;

const PIPELINE_ID = 8459130;
class ScheduleSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:schedule-send {transaction_id}';

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

        $leads = Leads::searchAllPays($contact, $amoApi, PIPELINE_ID, $transaction->agreement);

        $countLeads = $leads->count();

        $countPays = $lead->cf('Количество платежей')->getValue();

        if ($countPays > $countLeads) {

            //урезаем
            $diffCount = $countPays - $countLeads;

            for ($i = 0; $i < $diffCount; $i++) {

                $lead->status = 143;
                $lead->save();
            }
        }

        if ($countPays < $countLeads) {

            //добавляем
            $diffCount = $countLeads - $countPays;

            for ($i = 0; $i < $diffCount; $i++) {

                $lead = $amoApi->service->leads()->create();
                $lead->contact = $contact;
                $lead->cf('Номер договора')->setValue($transaction->agreement);
                //дата выплаты
                $lead->save();
            }
        }

        //пересчитываем


        //сверяем колво сделок в воронке и выплат (в тч и закрытые)
        //если больше то закрываем с конца
        //если меньше то добавляем с конца

        //пересчитываем по логике первой части задачи

        //! не трогаем закрытые ваще
    }
}
