<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use App\Services\amoCRM\Client;
use App\Services\amoCRM\Models\Leads;
use Carbon\Carbon;
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

        $transaction->leads_count_new = $countLeads;
        $transaction->save();

        $countPays = (int)$lead->cf('Количество платежей')->getValue();

        $baseLead = $amoApi->service->leads()->find($transaction->lead_id);

        if ($countPays < $countLeads) {

            //урезаем
            //если часть оплачена
            //если нет оплаченных
            $diffCount = $countLeads - $countPays;

            $leadsActive = Leads::searchActivePays($contact, $amoApi, PIPELINE_ID, $transaction->agreement);

            $i = 0;

            foreach ($leadsActive as $leadActive) {

                if ($diffCount != $i) {

                    $i++;
//dd(count($leadsActiveArray));
//                $leadActive = $amoApi->service->leads()->find($leadsActiveArray[$i]['id']);
                    $leadActive->status_id = 143;
                    $leadActive->save();
                }
            }

            //пересчитываем но только в активных!!!
            $leadsActive = Leads::searchActivePays($contact, $amoApi, PIPELINE_ID, $transaction->agreement);

            $leadsSuccess = Leads::searchSuccessPays($contact, $amoApi, PIPELINE_ID, $transaction->agreement);

            if ($leadsSuccess->count() > 0) {
                //уже выплачивали

                $successSale = 0;

                foreach ($leadsSuccess->toArray() as $leadArray) {

                    $successSale =+ $leadArray['sale'];
                }

                $partSum = ($baseLead->sale - $successSale) / $leadsActive->count();

                foreach ($leadsActive as $lead) {

                    $lead->sale = $partSum;
                    $lead->save();
                }

            } else {
                //еще не выплачивали

                $partSum = $baseLead->sale / $leadsActive->count();

                foreach ($leadsActive as $lead) {

                    $lead->sale = $partSum;
                    $lead->save();
                }
            }

        } elseif ($countPays > $countLeads) {

            //добавляем
            $diffCount = $countPays - $countLeads;

            $nextPayMonth = Carbon::parse($leads->last()->cf('Дата платежа')->getValue())->addMonth();

            for ($i = 0; $i < $diffCount; $i++, $nextPayMonth = Carbon::parse($nextPayMonth)->addMonth()) {

                $lead = $contact->createLead();
                $lead->name = 'Перерасчет '.$transaction->lead_id.' - '.$i;
                $lead->status_id = 68804058;//выплаты 1 этап
                $lead->cf('Номер договора')->setValue($transaction->agreement);
                $lead->cf('Дата платежа')->setValue($nextPayMonth->format('Y-m-d'));
                $lead->cf('Тип договора')->setValue($baseLead->cf('Тип договора')->getValue());
                $lead->cf('Срок инвестиций')->setValue($baseLead->cf('Срок инвестиций')->getValue());
                $lead->cf('Проект')->setValue($baseLead->cf('Проект')->getValue());
                $lead->cf('Дата договора')->setValue($baseLead->cf('Дата договора')->getValue());
                $lead->cf('Дата окончания договора')->setValue($baseLead->cf('Дата окончания договора')->getValue());
                $lead->cf('Валюта')->setValue($baseLead->cf('Валюта')->getValue());
                $lead->cf('Способ расчетов')->setValue($baseLead->cf('Способ расчетов')->getValue());
                $lead->cf('Срок выплат')->setValue($baseLead->cf('Срок выплат')->getValue());
                $lead->cf('Количество платежей')->setValue($baseLead->cf('Количество платежей')->getValue());
                $lead->cf('Дата первого платежа')->setValue($baseLead->cf('Дата первого платежа')->getValue());

                $lead->save();
            }

            $countLeads = $leads->count();

            $partSum = $baseLead->sale / $countLeads;

            foreach ($leads as $lead) {

                $lead->sale = $partSum;
                $lead->save();
            }
        }

        //пересчитываем
        $leadsActive = Leads::searchActivePays($contact, $amoApi, PIPELINE_ID, $transaction->agreement);

        $lastLead = $leadsActive->last();

        $lastLead->sale = $lastLead->sale + $baseLead->sale;
        $lastLead->save();

        $transaction->leads_count_last = Leads::searchActivePays($contact, $amoApi, PIPELINE_ID, $transaction->agreement)->count();
        $transaction->part_sum = $partSum ?: 0;
        $transaction->status = 1;
        $transaction->save();

        //сверяем колво сделок в воронке и выплат (в тч и закрытые)
        //если больше то закрываем с конца
        //если меньше то добавляем с конца

        //пересчитываем по логике первой части задачи

        //! не трогаем закрытые ваще
    }
}
