<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemRewardModel extends Model
{
     protected $table = 'reward_redeemed';
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'redeem_at',
        'status',
        'converted_amount',
        'total_amount'
    ];

    /**
     * Get the user that owns the security pin.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    //get reward
    public function reward()
    {
        return $this->belongsTo(RewardModel::class, 'reward_id');
    }
    

   
}
