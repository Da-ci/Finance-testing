<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\HomeLoan;
use App\Models\InvestPersonal;
use App\Models\InvestPersonalData;
use Illuminate\Support\Facades\Auth;

class InvestPersonals extends Component
{
    public $min;
    public $max;
    public $inflation;
    public $fees;
    public $monthlyInvest;
    public $date;
    public $monthlyFee;

    protected $rules = [
        "min" => 'required|numeric|max:-2|max:0',
        'max' => 'required|numeric|min:0|max:7',
        'inflation' => 'required|numeric|min:0|not_in:0',
        'fees' => 'required|numeric',
        'date' => 'required|date',
        'monthlyInvest' => 'required|numeric|min:0|not_in:0',
        'monthlyFee' => 'required|numeric|min:0|not_in:0'
    ];

    protected $messages = [
        '*.required' => 'This field is required',
        '*.numeric' => 'This field must be a number',
        '*.date' => 'This field must a date',
        '*.min:0' => 'This field must be greated than 0',
        '*.not_in:0' => 'This field must be greated than 0'
    ];

    protected $validationAttributes = [
        'min' => 'min return on invest',
        'max' => 'max return on invest',
        'monthlyInvest' => 'monthly investment',
    ];

    public function render()
    {
        $datas = InvestPersonal::all();

        return view('livewire.invest-personals', [
            "datas" => $datas,
        ]);
    }

    public function InputData()
    {
        $data = $this->FormatVariable();
        $this->InvestPersonalData($data);

        $check = InvestPersonal::select('total_invested')->orderBy('id', 'DESC')->first();

        if(!$check)
            $this->Calculate($data);
        else
            $this->Recalculate($data);    
    }

    public function FormatVariable()
    {
        $this->validate();
        $data['min'] = $this->min;
        $data['max'] =  $this->max;
        $data['inflation'] = $this->inflation / 100; // inflation
        $data['fees'] = $this->fees / 100; // fees percentages
        $data['monthlyInvest'] = $this->monthlyInvest; // monthly_invest
        $data['monthlyFee'] = $this->monthlyFee;
        $data['date'] = $this->date;

        return $data;
    }

    public function InvestPersonalData($data)
    {
        $db_data = InvestPersonalData::get()->first();

        if (!$db_data) {

            InvestPersonalData::create([
                "min" => $data['min'],
                "max" => $data['max'],
                "monthly_invest" => $data['monthlyInvest'],
                "fees" => $data['fees'],
                "start_date" => $data['date'],
                "inflation" => $data['inflation'],
                "user_id" => Auth::user()->id
            ]);
        } else if ($db_data) {
            InvestPersonalData::truncate();
            InvestPersonalData::create([
                "min" => $data['min'],
                "max" => $data['max'],
                "monthly_invest" => $data['monthlyInvest'],
                "fees" => $data['fees'],
                "start_date" => $data['date'],
                "inflation" => $data['inflation'],
                "user_id" => Auth::user()->id
            ]);
        }
    }

    public function Calculate($data)
    {
        $dates = HomeLoan::select('pay_date')->get();

        foreach($dates as $date)
        {

            $data['monthlyInvest'] = $data['monthlyInvest'] + (($data['monthlyInvest'] * $data['inflation']) / 12);

            $data['return_on_invest'] = rand($data['min'], $data['max']) / 100;

            $data['interest'] = round(($data['return_on_invest'] * $data['monthlyInvest']) / 12, 2);

            $data['after_fees'] = round((($data['fees'] * $data['monthlyInvest']) / 12) + $data['monthlyFee'], 2);

            $data['total_invested'] = round($data['monthlyInvest'] + $data['interest'] - $data['after_fees'], 2);

            InvestPersonal::create([
                "user_id" => Auth::user()->id,
                "fees" => $data['fees'],
                "monthly_account_fee" => $data['monthlyFee'],
                "inflation" => $data['inflation'],
                "monthly_invest" => $data['monthlyInvest'],
                "interest" => $data['interest'],
                "after_fees" => $data['after_fees'],
                "total_invested" => $data['total_invested'],
                "date" => $date->pay_date,
                "return_on_invest" => $data['return_on_invest']
            ]);
        }
    }

    public function Recalculate($data)
    {
        $from = $data['date'];
        $to = HomeLoan::select('pay_date')->orderBy('id', 'DESC')->first();
        $dates = HomeLoan::whereBetween('pay_date', [$from, $to->pay_date])->get();
        InvestPersonal::whereBetween('date', [$from, $to->pay_date])->delete();

        foreach($dates as $date)
        {
                $monthlyInvest = $data['monthlyInvest'] + (($data['monthlyInvest'] * $data['inflation']) / 12);

                $return_on_invest = rand($data['min'], $data['max']) / 100;

                $interest = round(($return_on_invest * $data['monthlyInvest']) / 12, 2);

                $after_fees = round((($data['fees'] * $monthlyInvest) / 12) + $data['monthlyFee'], 2);

                $total_invested = round($monthlyInvest + $interest - $after_fees, 2);

            InvestPersonal::create([
                "user_id" => Auth::user()->id,
                "fees" => $data['fees'],
                "monthly_account_fee" => $data['monthlyFee'],
                "inflation" => $data['inflation'],
                "monthly_invest" => $monthlyInvest,
                "interest" => $interest,
                "after_fees" => $after_fees,
                "total_invested" => $total_invested,
                "date" => $date->pay_date,
                "return_on_invest" => $return_on_invest
            ]);
        }
    }
}
