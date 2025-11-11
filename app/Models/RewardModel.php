<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardModel extends Model
{
    protected $table = 'rewards';
    
    use HasFactory;
    protected $fillable = [
       
        'name',
        'type',
        'min_deposit_amount',
        'max_deposit_amount',
        'reward_type',
        'description',
        'amount',
        'max_amount_allowed',
        'timeline_in_days',
        'pnl_required_amount',
        'pnl_required_multiplier',
        'conversion_type',
        'for_first_deposit',
        'status',
    ];

    /**
     * Get the user that owns the security pin.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
     
    public function users(){
        return $this->hasMany(RedeemRewardModel::class, 'reward_id');
    }
    
     //check user has claimed this reward
    public function hasClaimedReward($rewardId, $userId)
    {
        return RedeemRewardModel::where('user_id', $userId)
                    ->where('reward_id', $rewardId)
                    ->exists();
    }
    public function redeemed(){
        return $this->hasOne(RedeemRewardModel::class, 'reward_id');
    }
}
