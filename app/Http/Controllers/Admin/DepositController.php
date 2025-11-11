<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Constants\Status;
use App\Models\RewardModel;
use Illuminate\Http\Request;
use App\Models\RedeemRewardModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\PaymentController;

class DepositController extends Controller
{
    public function pending($userId = null)
    {
        $pageTitle = 'Pending Deposits';
        $deposits = $this->depositData('pending', userId:$userId);
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }


    public function approved($userId = null)
    {
        $pageTitle = 'Approved Deposits';
        $deposits = $this->depositData('approved', userId:$userId);
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function successful($userId = null)
    {
        $pageTitle = 'Successful Deposits';
        $deposits = $this->depositData('successful', userId:$userId);
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function rejected($userId = null)
    {
        $pageTitle = 'Rejected Deposits';
        $deposits = $this->depositData('rejected', userId:$userId);
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function initiated($userId = null)
    {
        $pageTitle = 'Initiated Deposits';
        $deposits = $this->depositData('initiated', userId:$userId);
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function deposit($userId = null)
    {
        $pageTitle = 'Deposit History';
        $depositData = $this->depositData($scope = null, $summary = true, userId:$userId);
        $deposits = $depositData['data'];
        $summary = $depositData['summary'];
        $successful = $summary['successful'];
        $pending = $summary['pending'];
        $rejected = $summary['rejected'];
        $initiated = $summary['initiated'];
        return view('admin.deposit.log', compact('pageTitle', 'deposits', 'successful', 'pending', 'rejected', 'initiated'));
    }

    protected function depositData($scope = null, $summary = false, $userId = null)
    {
        if ($scope) {
            $deposits = Deposit::$scope()->with(['user', 'gateway']);
        } else {
            $deposits = Deposit::with(['user', 'gateway']);
        }

        if ($userId) {
            $deposits = $deposits->where('user_id', $userId);
        }

        $deposits = $deposits->searchable(['trx','user:username'])->dateFilter();
        $request = request();

        if ($request->method) {
            if ($request->method != Status::GOOGLE_PAY) {
                $method = Gateway::where('alias', $request->method)->firstOrFail();
                $deposits = $deposits->where('method_code', $method->code);
            } else {
                $deposits = $deposits->where('method_code', Status::GOOGLE_PAY);
            }
        }

        if (!$summary) {
            return $deposits->orderBy('id', 'desc')->paginate(getPaginate());
        } else {
            $successful = clone $deposits;
            $pending = clone $deposits;
            $rejected = clone $deposits;
            $initiated = clone $deposits;

            $successfulSummary = $successful->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
            $pendingSummary = $pending->where('status', Status::PAYMENT_PENDING)->sum('amount');
            $rejectedSummary = $rejected->where('status', Status::PAYMENT_REJECT)->sum('amount');
            $initiatedSummary = $initiated->where('status', Status::PAYMENT_INITIATE)->sum('amount');

            return [
                'data' => $deposits->orderBy('id', 'desc')->paginate(getPaginate()),
                'summary' => [
                    'successful' => $successfulSummary,
                    'pending' => $pendingSummary,
                    'rejected' => $rejectedSummary,
                    'initiated' => $initiatedSummary,
                ]
            ];
        }
    }

    public function details($id)
    {
        $deposit = Deposit::where('id', $id)->with(['user', 'gateway'])->firstOrFail();
        $pageTitle = $deposit->user->username . ' requested ' . showAmount($deposit->amount);
        $details = ($deposit->detail != null) ? json_encode($deposit->detail) : null;
        return view('admin.deposit.detail', compact('pageTitle', 'deposit', 'details'));
    }


    public function approve($id)
    {
        $request = request();
        $bonus = $request->with_bonus ? 1 : 0;
        $deposit = Deposit::where('id', $id)->where('status', Status::PAYMENT_PENDING)->firstOrFail();
        $user = User::find($deposit->user_id);
         
        //check bonus included
        if ($bonus == 1 && $deposit->reward_id != 0) {
            $bonusId = $deposit->reward_id;

             //check if user already has another active bonus
            $activeBonus = RedeemRewardModel::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
            if ($activeBonus) {
                $notify[] = ['error', 'User already have an active bonus.'];
                return back()->withNotify($notify);
            }

            $reward = RewardModel::where("reward_type", "deposit")->where("id", $bonusId)->where("status", "active")->first();
            if ($reward) {
                //check its first deposit and coupon is for first deposit
                $firstDeposit = Deposit::where('user_id', $user->id)
                    ->where('status', Status::PAYMENT_SUCCESS)
                    ->exists();
                if ($reward->is_first_deposit == 1 && !$firstDeposit) {
                    $user->first_deposit = 1;
                    $user->save();
                }
                
                if (!$reward->hasClaimedReward($reward->id, $user->id)) {
                    //check minimum and maximum deposit amount
                    if ($reward->min_deposit_amount <= $deposit->amount && $reward->max_deposit_amount >= $deposit->amount) {
                        //calculate bonus
                        $bonusAmount = $reward->type == 'percentage'
                            ? ($reward->amount / 100) * $deposit->amount
                            : $reward->amount;
                        //check if bonus is greater than zero
                        if (!empty($reward->max_amount_allowed)) {
                            $bonusAmount =  $reward->max_amount_allowed > $bonusAmount ? $bonusAmount : $reward->max_amount_allowed;
                        }
                        
                     //claim bonus
                        $redeemReward = new RedeemRewardModel();
                        $redeemReward->user_id = $user->id;
                        $redeemReward->reward_id = $reward->id;
                        $redeemReward->redeem_at = now();
                        $redeemReward->total_amount = $bonusAmount;
                        $redeemReward->converted_amount = 0;
                        $redeemReward->status = 'active';
                        $redeemReward->save();
                    //update the bonus balances
                        $user->reward_points += $bonusAmount;
                        $user->total_reward_points += $bonusAmount;
                        $user->save();
                    //log in bonus transaction
                        DB::table('bonus_transactions')->insert([
                        'user_id' => $user->id,
                        'reward_redeem_id' => $redeemReward->id,
                        'amount' => $bonusAmount,
                        'type' => 'credit',
                        'details' => 'Redeem reward for deposit',
                        'created_at' => now(),
                        ]);
                    }
                }
            }
        }
        //remove gift card if user has any active giftcard
        $activeGiftCreditBonus = RedeemRewardModel::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();
        if ($activeGiftCreditBonus && $activeGiftCreditBonus->reward->reward_type == 'gift_credit') {
            $activeGiftCreditBonus->status = 'expired';
            $activeGiftCreditBonus->save();
            $giftCredit = $activeGiftCreditBonus->total_amount;
            //deduct the gift credit from user balance
            $amountNeedToDeduct = $user->balance > $giftCredit ? $giftCredit : $user->balance;
            $rewardNeedToDeduct = $user->reward_points > $giftCredit ? $giftCredit : $user->reward_points;

            
            //update the bonus balances
            $user->balance -= $amountNeedToDeduct;
            $user->reward_points -= $rewardNeedToDeduct;
            $user->save();

            //log in bonus transaction
            DB::table('bonus_transactions')->insert([
                'user_id' => $user->id,
                'reward_redeem_id' => $activeGiftCreditBonus->id,
                'amount' => $rewardNeedToDeduct,
                'type' => 'debited',
                'details' => 'Expiration of reward ' . $activeGiftCreditBonus->reward->name,
                'created_at' => now(),
            ]);
        }
    

        PaymentController::userDataUpdate($deposit, true);
 
        $notify[] = ['success', 'Deposit request approved successfully'];

        return to_route('admin.deposit.pending')->withNotify($notify);
    }

    public function reject(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'message' => 'required|string|max:255'
        ]);
        $deposit = Deposit::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->firstOrFail();

        $deposit->admin_feedback = $request->message;
        $deposit->status = Status::PAYMENT_REJECT;
        $deposit->save();

        notify($deposit->user, 'DEPOSIT_REJECT', [
            'method_name' => $deposit->methodName(),
            'method_currency' => $deposit->method_currency,
            'method_amount' => showAmount($deposit->final_amount, currencyFormat:false),
            'amount' => showAmount($deposit->amount, currencyFormat:false),
            'charge' => showAmount($deposit->charge, currencyFormat:false),
            'rate' => showAmount($deposit->rate, currencyFormat:false),
            'trx' => $deposit->trx,
            'rejection_message' => $request->message
        ]);

        $notify[] = ['success', 'Deposit request rejected successfully'];
        return  to_route('admin.deposit.pending')->withNotify($notify);
    }
}
