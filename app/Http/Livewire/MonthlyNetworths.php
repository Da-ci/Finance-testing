<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\HomeLoan;
use App\Models\HomeLoanData;
use App\Models\ProgramSuper;
use App\Models\InvestPersonal;
use App\Models\MonthlyNetworth;
use App\Models\LongTermInvestment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MonthlyNetworths extends Component
{
    public $home_value_mod;
    public $cash_mod;
    public $home_app_mod;
    public $other_invest_mod;
    public $date_mod;
    public $home_loan_mod;
    public $show_data_mod;

    public $home_value;
    public $cash;
    public $home_app;
    public $other_invest;
    public $show_data;

    protected $messages = [
        '*.required' => 'This field is required',
        '*.numeric' => 'This field must be a number',
        '*.date' => 'This field must a date',
        '*.min:0' => 'This field must be greated than 0',
    ];

    protected $validationAttributes = [
        'home_value' => 'home value',
        'cash' => 'cash',
        'home_app' => 'home app',
        'other_invest' => 'other investments',

        'home_value_mod' => 'home value',
        'cash_mod' => 'cash',
        'home_app_mod' => 'home app',
        'other_invest_mod' => 'other investments',
        'date_mod' => 'date'
    ];

    protected $listeners = ['asx' => 'issawa'];


    public function InitializeTable()
    {
        $check = MonthlyNetworth::select('date')->orderBy('id', 'DESC')->first();

        if (!$check) {
            $dates = HomeLoan::select('pay_date')->get();

            foreach ($dates as $date)
                MonthlyNetworth::create([
                    "user_id" => Auth::user()->id,
                    "date" => $date->pay_date
                ]);
        }
    }

    public function render()
    {
        $this->InitializeTable();

        $this->show_data = 5;
        $start_date = HomeLoan::select('pay_date')->orderBy('id', 'ASC')->first();

        if (!is_null($start_date))
            $end_date = date('Y-m-d', strtotime($start_date->pay_date . " + " . $this->show_data . "  years"));
        else
            $end_date = null;


        $from = date($start_date ? $start_date->pay_date : null);
        $to = date($end_date ? $end_date : null);

        $home_loan = HomeLoan::select('pay_date', 'end_balance')->whereBetween('pay_date', [$from, $to])->orderBy('id','ASC')->get();

        $dates = MonthlyNetworth::whereBetween('date', [$from, $to])->get();

        // ASSETS
        $home_values = MonthlyNetworth::whereBetween('date', [$from, $to])->get();
        $cashs = MonthlyNetworth::whereBetween('date', [$from, $to])->get();
        $investPersonals = InvestPersonal::whereBetween('date', [$from, $to])->get();
        $longTermInvests = LongTermInvestment::whereBetween('date', [$from, $to])->get();
        $investSupers = ProgramSuper::whereBetween('date', [$from, $to])->get();
        $other_invests = MonthlyNetworth::whereBetween('date', [$from, $to])->get();

        $value1 = null;
        $value2 = null;
        $value3 = null;
        $value4 = null;
        $value5 = null;
        $value6 = null;

        foreach ($dates as $date) {
            $home_value = MonthlyNetworth::select('home_value')->Where('date', $date->date)->first();
            $cash = MonthlyNetworth::select('cash')->Where('date', $date->date)->first();
            $investPersonal = InvestPersonal::select('total_invested')->Where('date', $date->date)->first();
            $longTermInvest = LongTermInvestment::select('total_invested')->Where('date', $date->date)->first();
            $investSuper = ProgramSuper::select('total_invested')->Where('date', $date->date)->first();
            $other_invest = MonthlyNetworth::select('other_invest')->Where('date', $date->date)->first();


            $value1 += $home_value->home_value ? $home_value->home_value : 0;
            $value2 += $cash->cash ? $cash->cash : 0;
            $value3 += $investPersonal ? $investPersonal->total_invested : 0;
            $value4 += $longTermInvest ? $longTermInvest->total_invested : 0;
            $value5 += $investSuper ? $investSuper->total_invested : 0;
            $value6 += $other_invest->other_invest ? $other_invest->other_invest : 0;

            $assets[] =  $value1 + $value2 + $value3 + $value4 + $value5 + $value6;
        }
        // End ASSETS

        // DIFFERENCE
        foreach ($home_values as $key => $record)
            $difference[] = $assets[$key] - $record->home_value;
        // End DIFFERENCE


        // DIFFERENCE SUPER 
        foreach ($investSupers as $key => $record)
            $differenceSuper[] = $difference[$key] - $record->total_invested;
        // End DIFFERENCE SUPER

        if (isset($differenceSuper)) {
            // RUNNING DIFF
            foreach ($differenceSuper as $key => $record) {
                if (!$key == 0)
                    $runningDiff[] = $differenceSuper[$key] - $differenceSuper[$key - 1];
                else
                    $runningDiff[] = $differenceSuper[$key];
            }
            // End RUNNING DIFF

            // DIFFERENCE
            foreach ($difference as $key => $record) {
                if ($key != 0)
                    $overallDiff[] = $difference[$key] - $difference[$key - 1];
                else
                    $overallDiff[] = $difference[$key];
            }
            // END DIFFERENCE
        }

        return view('livewire.monthly-networths', [
            "home_loan" => $home_loan,
            "home_values" => $home_values,
            "cashs" => $cashs,
            "other_invests" => $other_invests,
            "investSupers" => $investSupers,
            "investPersonals" => $investPersonals,
            "longTermInvests" => $longTermInvests,
            "assets" => isset($assets) ? $assets : null,
            "difference" => isset($difference) ? $difference : null,
            "differenceSuper" => isset($differenceSuper) ? $differenceSuper : null,
            "runningDiff" => isset($runningDiff) ? $runningDiff : null,
            "overallDiff" => isset($overallDiff) ? $overallDiff : null
        ]);
    }

    public function ModifyData()
    {
        $date = $this->validate([
            'date_mod' => 'required|date'
        ]);

        $record = MonthlyNetworth::where('date', $date['date_mod'])->first();

        if (!is_null($record)) {

            $this->checkHomeInputs($date, $record);

            if ($this->cash_mod != null) {
                $data = $this->validate([
                    "cash_mod" => 'numeric|min:0',
                ]);

                $from = date($date['date_mod'] ? $date['date_mod'] : null);
                $to = MonthlyNetworth::select('date')->orderBy('id', 'DESC')->first();

                $dates = MonthlyNetworth::whereBetween('date', [$from, $to->date])->get();

                foreach ($dates as $key => $record) {

                    if ($key == 0) {
                        if ($record->id == 1) {
                            $record->cash = $data['cash_mod'];
                            $record->passed = true;
                            $record->save();
                        } else {
                            $last_record = MonthlyNetworth::where('id', $record->id - 1)->first();
                            $record->cash = $data['cash_mod'];
                            $record->passed = true;
                            $record->save();
                        }
                    }
                }
            }

            if ($this->other_invest_mod != null) {
                $data = $this->validate([
                    "other_invest_mod" => 'numeric|min:0',
                ]);

                $from = date($date['date_mod'] ? $date['date_mod'] : null);
                $to = MonthlyNetworth::select('date')->orderBy('id', 'DESC')->first();

                $dates = MonthlyNetworth::whereBetween('date', [$from, $to->date])->get();

                foreach ($dates as $key => $record) {

                    if ($key == 0) {
                        if ($record->id == 1) {
                            $record->cash = $data['other_invest_mod'];
                            $record->passed = true;
                            $record->save();
                        } else {
                            $last_record = MonthlyNetworth::where('id', $record->id - 1)->first();
                            $record->cash = $data['other_invest_mod'];
                            $record->passed = true;
                            $record->save();
                        }
                    }
                }
            }

            if ($this->home_loan_mod != null) {
                $this->HomeLoanModifydata($date, $this->home_loan_mod);
            }
        } else if (is_null($record)) {
            throw ValidationException::withMessages(['date_mod' => 'This value doesn\'t exits in the table']);
        }
    }


    public function HomeLoanModifydata($date, $loan_amount)
    {
        $record_date = HomeLoan::where('pay_date', $date['date_mod'])->first();
        
        if (!is_null($record_date)) {
            $data = HomeLoanData::first();
            $data = $this->FormatVariable($data, $record_date, $loan_amount);
            $data = $this->scheduled_payment($data);

            $from = $record_date->pay_date;
            $to = HomeLoan::select('pay_date')->orderBy('id', 'DESC')->first();
            HomeLoan::whereBetween('pay_date', [$from, $to->pay_date])->delete();
            DB::table('home_loans_savings')->whereBetween('pay_date', [$from, $to->pay_date])->delete();

            $last_record = HomeLoan::select('beg_balance', 'end_balance', 'tot_payment', 'pay_date', 'pmt_no', 'cum_interest')->orderBy('id', 'DESC')->first();

            if (isset($last_record['end_balance'])) {
                $data['beg_balance'] = $last_record->end_balance;
                $this->Recalculate($data);
                $this->RecalculateSavings($data);
            } else {
                $this->calculate($data);
            }

        } else {
            throw ValidationException::withMessages(['date' => 'This value doesn\'t exits in the table']);
        }
    }

    public function FormatVariable($record, $record_date, $loan_amount)
    {

        $data['date'] = $record_date->pay_date;
        $data['interest_rate'] =   $record['int_rate'];
        $data['nb_payments'] = $record['no_payments']; //months
        $data['loan_period'] = $record['loan_period']; // years
        $data['loan_amount'] = $loan_amount; // loan amount
        $data['ext_payment'] = $record['opt_payment'];


        return $data;
    }

    public function scheduled_payment($data)
    {
        $up = $data['interest_rate'] * $data['loan_amount'];
        $pow = pow(1 + ($data['interest_rate'] / $data['nb_payments']), -$data['nb_payments'] * $data['loan_period']);
        $data['sch_payment'] = $up / ($data['nb_payments'] * (1 - $pow));

        return $data;
    }

    public function calculate($data)
    {
        $stop = null;
        do {
            $last_record = HomeLoan::select('end_balance', 'principal', 'interest')->orderBy('id', 'DESC')->first();

            if ($last_record != null) {
                do {

                    $last_record = HomeLoan::select('beg_balance', 'end_balance', 'tot_payment', 'pay_date', 'pmt_no', 'cum_interest')->orderBy('id', 'DESC')->first();
                    $data['beg_balance'] = $last_record->end_balance;

                    $daystosum = 1;
                    $date = date('Y-m-d', strtotime($last_record->pay_date . ' + ' . $daystosum . ' months')); // add days to d-m-Y format

                    $data['pmt_no'] = $last_record->pmt_no + 1;

                    $data['interest'] = round($data['beg_balance'], 2) * (round($data['interest_rate'], 2) / 12); // Interest
                    $data['total_payment'] = $data['sch_payment'] + $data['ext_payment'];
                    $data['principal'] = $data['total_payment'] - $data['interest'];
                    $data['end_balance'] = $data['beg_balance'] - $data['principal'];
                    $data['cum_interest'] = $last_record->cum_interest + $data['interest'];

                    if ($data['end_balance'] > 50) {
                        HomeLoan::create([
                            "user_id" => Auth::user()->id,
                            "beg_balance" => $data['beg_balance'],
                            "pay_date" => $date,
                            "sch_payment" => $data['sch_payment'],
                            "ext_payment" => $data['ext_payment'],
                            "tot_payment" => $data['total_payment'],
                            "principal" => $data['principal'],
                            "interest" => $data['interest'],
                            "pmt_no" => $data['pmt_no'],
                            "end_balance" => $data['end_balance'],
                            "cum_interest" => $data['cum_interest'],
                        ]);

                        $stop = 0;
                    } else {
                        $stop = 1;
                    }
                } while ($stop == 0);
            } else {

                $data['beg_balance'] = $data['loan_amount'];

                $data['interest'] = round($data['beg_balance'], 2) * (round($data['interest_rate'], 2) / 12); // Interest
                $data['total_payment'] = $data['sch_payment'] + $data['ext_payment'];
                $data['principal'] = $data['total_payment'] - $data['interest'];
                $data['end_balance'] = $data['beg_balance'] - $data['principal'];

                HomeLoan::create([
                    "user_id" => Auth::user()->id,
                    "beg_balance" => $data['beg_balance'],
                    "pay_date" => $data['date'],
                    "sch_payment" => $data['sch_payment'],
                    "ext_payment" => $data['ext_payment'],
                    "tot_payment" => $data['total_payment'],
                    "principal" => $data['principal'],
                    "interest" => $data['interest'],
                    "pmt_no" => 1,
                    "end_balance" => $data['end_balance'],
                    "cum_interest" => $data['interest'],
                ]);
                
            }
        } while ($stop == 0);

        $stop = null;
        do {
            $last_record = DB::table('home_loans_savings')->select('end_balance', 'principal', 'interest')->orderBy('id', 'DESC')->first();

            if ($last_record != null) {

                do {

                    $last_record = DB::table('home_loans_savings')->select('beg_balance', 'end_balance', 'tot_payment', 'pay_date', 'pmt_no', 'cum_interest', 'cum_interest')->orderBy('id', 'DESC')->first();
                    $data['beg_balance'] = $last_record->end_balance;

                    $daystosum = 1;
                    $date = date('Y-m-d', strtotime($last_record->pay_date . ' + ' . $daystosum . ' months')); // add days to d-m-Y format

                    $data['pmt_no'] = $last_record->pmt_no + 1;

                    $data['interest'] = round($data['beg_balance'], 2) * (round($data['interest_rate'], 2) / 12); // Interest
                    $data['total_payment'] = $data['sch_payment'];
                    $data['principal'] = $data['total_payment'] - $data['interest'];
                    $data['end_balance'] = $data['beg_balance'] - $data['principal'];
                    $data['cum_interest'] = $last_record->cum_interest + $data['interest'];

                    if ($data['end_balance'] > 50) {

                        DB::table('home_loans_savings')->insert([
                            "user_id" => Auth::user()->id,
                            "beg_balance" => $data['beg_balance'],
                            "pay_date" => $date,
                            "sch_payment" => $data['sch_payment'],
                            "ext_payment" => 0,
                            "tot_payment" => $data['total_payment'],
                            "principal" => $data['principal'],
                            "interest" => $data['interest'],
                            "pmt_no" => $data['pmt_no'],
                            "end_balance" => $data['end_balance'],
                            "cum_interest" => $data['cum_interest'],
                        ]);

                        $stop = 0;
                    } else {
                        $stop = 1;
                    }
                } while ($stop == 0);
            } else {

                $data['beg_balance'] = $data['loan_amount'];

                $data['interest'] = round($data['beg_balance'], 2) * (round($data['interest_rate'], 2) / 12); // Interest
                $data['total_payment'] = $data['sch_payment'];
                $data['principal'] = $data['total_payment'] - $data['interest'];
                $data['end_balance'] = $data['beg_balance'] - $data['principal'];


                DB::table('home_loans_savings')->insert([
                    "user_id" => Auth::user()->id,
                    "beg_balance" => $data['beg_balance'],
                    "pay_date" => $data['date'],
                    "sch_payment" => $data['sch_payment'],
                    "ext_payment" => 0,
                    "tot_payment" => $data['total_payment'],
                    "principal" => $data['principal'],
                    "interest" => $data['interest'],
                    "pmt_no" => 1,
                    "end_balance" => $data['end_balance'],
                    "cum_interest" => $data['interest'],
                ]);
            }
        } while ($stop == 0);
    }

    public function Recalculate($data)
    {
        $last_record = HomeLoan::select('end_balance', 'principal', 'interest')->orderBy('id', 'DESC')->first();

        $stop = null;
        do {
            $last_record = HomeLoan::select('beg_balance', 'end_balance', 'tot_payment', 'pay_date', 'pmt_no', 'cum_interest')->orderBy('id', 'DESC')->first();
            $data['beg_balance'] = $last_record->end_balance;

            $daystosum = 1;
            $date = date('Y-m-d', strtotime($last_record->pay_date . ' + ' . $daystosum . ' months')); // add days to d-m-Y format

            $data['pmt_no'] = $last_record->pmt_no + 1;

            $data['interest'] = round($data['beg_balance'], 2) * (round($data['interest_rate'], 2) / 12); // Interest
            $data['total_payment'] = $data['sch_payment'] + $data['ext_payment'];
            $data['principal'] = $data['total_payment'] - $data['interest'];
            $data['end_balance'] = $data['beg_balance'] - $data['principal'];
            $data['cum_interest'] = $last_record->cum_interest + $data['interest'];

            if ($data['end_balance'] > 50) {
                HomeLoan::create([
                    "user_id" => Auth::user()->id,
                    "beg_balance" => $data['beg_balance'],
                    "pay_date" => $date,
                    "sch_payment" => $data['sch_payment'],
                    "ext_payment" => $data['ext_payment'],
                    "tot_payment" => $data['total_payment'],
                    "principal" => $data['principal'],
                    "interest" => $data['interest'],
                    "pmt_no" => $data['pmt_no'],
                    "end_balance" => $data['end_balance'],
                    "cum_interest" => $data['cum_interest'],
                ]);

                $stop = 0;
            } else {
                $stop = 1;
            }
        } while ($stop == 0);
    }


    public function RecalculateSavings($data)
    {

        $last_record = DB::table('home_loans_savings')->select('end_balance', 'principal', 'interest')->orderBy('id', 'DESC')->first();

        $stop = null;
        do {
            $last_record = DB::table('home_loans_savings')->select('beg_balance', 'end_balance', 'tot_payment', 'pay_date', 'pmt_no', 'cum_interest')->orderBy('id', 'DESC')->first();
            $data['beg_balance'] = $last_record->end_balance;

            $daystosum = 1;
            $date = date('Y-m-d', strtotime($last_record->pay_date . ' + ' . $daystosum . ' months')); // add days to d-m-Y format

            $data['pmt_no'] = $last_record->pmt_no + 1;

            $data['interest'] = round($data['beg_balance'], 2) * (round($data['interest_rate'], 2) / 12); // Interest
            $data['total_payment'] = $data['sch_payment'] + $data['ext_payment'];
            $data['principal'] = $data['total_payment'] - $data['interest'];
            $data['end_balance'] = $data['beg_balance'] - $data['principal'];
            $data['cum_interest'] = $last_record->cum_interest + $data['interest'];

            if ($data['end_balance'] > 50) {
                DB::table('home_loans_savings')->insert([
                    "user_id" => Auth::user()->id,
                    "beg_balance" => $data['beg_balance'],
                    "pay_date" => $date,
                    "sch_payment" => $data['sch_payment'],
                    "ext_payment" => $data['ext_payment'],
                    "tot_payment" => $data['total_payment'],
                    "principal" => $data['principal'],
                    "interest" => $data['interest'],
                    "pmt_no" => $data['pmt_no'],
                    "end_balance" => $data['end_balance'],
                    "cum_interest" => $data['cum_interest'],
                ]);

                $stop = 0;
            } else {
                $stop = 1;
            }
        } while ($stop == 0);
    }

    public function ResetTables()
    {
        MonthlyNetworth::truncate();
    }


    public function checkHomeInputs($date, $record)
    {
        $check_value = false;
        $check_app = false;

        if ($this->home_value_mod != null) {
            $var_1 = $this->validate([
                "home_value_mod" => 'numeric|min:0',
            ]);
            $check_value = true;
        }

        if ($this->home_app_mod != null) {
            $var_2 = $this->validate([
                "home_app_mod" => 'numeric|min:0',
            ]);
            $check_app = true;
        }

        if ($check_value != true || $check_app != true) {
            $this->validate([
                'home_value_mod' => 'numeric|min:0',
                'home_app_mod' => 'numeric|min:0',
            ]);
        } else {

            $selectedRecord = MonthlyNetworth::where('date', $date['date_mod'])->first();

            $selectedRecord->home_value = $var_1['home_value_mod'];
            $selectedRecord->home_app = $var_2['home_app_mod'];
            $selectedRecord->save();

            $from = date($date['date_mod'] ? $date['date_mod'] : null);
            $to = MonthlyNetworth::select('date')->orderBy('id', 'DESC')->first();

            $dates = MonthlyNetworth::whereBetween('date', [$from, $to->date])->get();

            $first_loop = false;

            foreach ($dates as $key => $record) {

                if ($record->id == 1) {
                    $record->home_value =  $var_1['home_value_mod'];
                    $record->home_app = $var_2['home_app_mod'];
                    $record->passed = true;
                    $record->save();
                } else {

                    $last_record = MonthlyNetworth::where('id', $record->id - 1)->first();

                    if ($first_loop == false) {
                        $record->home_value = $var_1['home_value_mod'] * ($var_2['home_app_mod'] / 100) + $var_1['home_value_mod'];
                        $first_loop = true;
                    } else {
                        $record->home_value = $last_record->home_value * ($var_2['home_app_mod'] / 100) + $last_record->home_value;
                    }

                    $record->passed = true;
                    $record->save();
                }
            }
        }
    }
}
