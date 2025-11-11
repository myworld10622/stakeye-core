<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusTransactions extends Model
{
     protected $table = 'bonus_transactions';
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_redeem_id',
        'amount',
        'type',
        'details',
    ];

    /**
     * Get the user that owns the security pin.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function redeemReward()
    {
        return $this->belongsTo(RedeemRewardModel::class, 'reward_redeem_id');
    }
}
