@extends('admin.layouts.app')
@section('panel')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                @if ($errors->any())
                  <div class="alert alert-danger">
                    <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                </div>

                <div class="card-body">
        <form action="" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row g-4">
            <div class="form-group mb-3 col-lg-6">
              <label for="rewardName" class="form-label">@lang('Name')</label>
              <input id="rewardName" class="form-control" type="text" name="name" required value="{{ $reward->name ?? '' }}">
            </div>

            <div class="form-group mb-3 col-lg-6">
              <label for="rewardType" class="form-label">@lang('Reward Type')</label>
              <select id="rewardType" class="form-control" name="reward_type" required>
                <option value="deposit" {{ isset($reward) && $reward->reward_type == 'deposit' ? 'selected' : '' }}>@lang('On Deposit')</option>
                <option value="offer" {{ isset($reward) && $reward->reward_type == 'offer' ? 'selected' : '' }}>@lang('Offer')</option>
                  <option value="gift_credit" {{ isset($reward) && $reward->reward_type == 'gift_credit' ? 'selected' : '' }}>@lang('Gift Credits')</option>
              </select>
            </div>

            <div class="form-group mb-3 col-lg-6">
              <label for="minDepositAmount" class="form-label">@lang('Minimum Deposit')</label>
              <input id="minDepositAmount" class="form-control" type="number" step="0.0001" name="min_deposit_amount"  value="{{ $reward->min_deposit_amount ?? 0 }}">
            </div>

            <div class="form-group mb-3 col-lg-6">
              <label for="maxDepositAmount" class="form-label">@lang('Maximum Deposit')</label>
              <input id="maxDepositAmount" class="form-control" type="number" step="0.0001" name="max_deposit_amount"   value="{{ $reward->max_deposit_amount ?? 0 }}">
            </div>

            <div class="form-group mb-3 col-lg-6">
              <label for="bonusRewardType" class="form-label">@lang('Bonus reward type')</label>
              <select id="bonusRewardType" class="form-control" name="bonus_reward_type" required>
                <option value="percentage" {{ isset($reward) && $reward->bonus_reward_type == 'percentage' ? 'selected' : '' }}>@lang('Percentage')</option>
                <option value="fix" {{ isset($reward) && $reward->bonus_reward_type == 'fix' ? 'selected' : '' }}>@lang('Fix')</option>
              </select>
            </div>

            <div class="form-group mb-3 col-lg-6">
              <label for="rewardAmount" class="form-label">@lang('Amount')</label>
              <input id="rewardAmount" class="form-control" type="number" step="0.01" name="amount" required value="{{ $reward->amount ?? '' }}">
            </div>

            <div class="form-group mb-3 col-lg-6">
              <label for="maxAmountAllowed" class="form-label">@lang('Maximum amount we will gave')</label>
              <input id="maxAmountAllowed" class="form-control" type="number" step="0.01" name="max_amount_allowed" required value="{{ $reward->max_amount_allowed ?? '' }}">
            </div>
             <div class="form-group mb-3 col-lg-6">
              <label for="conversion_type" class="form-label">@lang('Conversion type')</label>
              <select id="conversion_type" class="form-control" name="conversion_type" required>
                <option value="bet" {{ isset($reward) && $reward->conversion_type == 'bet' ? 'selected' : '' }}>@lang('Bet')</option>
                <option value="pnl" {{ isset($reward) && $reward->conversion_type == 'pnl' ? 'selected' : '' }}>@lang('P&L')</option>
              </select>
            </div>
      <div class="form-group mb-3 col-lg-6">
              <label for="for_first_deposit" class="form-label">@lang('For First Deposit?')</label>
              <select id="for_first_deposit" class="form-control" name="for_first_deposit" required>
                <option value="0" {{ isset($reward) && $reward->for_first_deposit == '0' ? 'selected' : '' }}>@lang('No')</option>
                <option value="1" {{ isset($reward) && $reward->for_first_deposit == '1' ? 'selected' : '' }}>@lang('Yes')</option>
              </select>
            </div>
            
            <div class="form-group mb-3 col-lg-6">
              <label for="pnlMultiplier" class="form-label">@lang('P/L & Bet Required Multiplier')</label>
              <input id="pnlMultiplier" class="form-control" type="number" step="0.0001" name="pnl_required_multiplier" required value="{{ $reward->pnl_required_multiplier ?? '' }}">
            </div>

            <div class="form-group mb-3 col-lg-6">
              <label for="timelineDays" class="form-label">@lang('Timeline (Days)')</label>
              <input id="timelineDays" class="form-control" type="number" name="timeline_in_days" required value="{{ $reward->timeline_in_days ?? '' }}">
            </div>

            <div class="form-group mb-3 col-lg-6">
              <label for="rewardDescription" class="form-label">@lang('Description')</label>
              <textarea id="rewardDescription" class="form-control" name="description" rows="3">{{ $reward->description ?? '' }}</textarea>
            </div>

            <div class="form-group mb-3 col-lg-6 d-none">
              <label for="pnlRequiredAmount" class="form-label">@lang('PNL Required Amount')</label>
              <input id="pnlRequiredAmount" class="form-control" type="number" step="0.01" name="pnl_required_amount" readonly value="{{ $reward->pnl_required_amount ?? '' }}">
            </div>

            <div class="form-group mb-3 col-lg-6">
              <label for="rewardStatus" class="form-label">@lang('Status')</label>
              <select id="rewardStatus" class="form-control" name="status" required>
                <option value="active" {{ isset($reward) && $reward->status == 'active' ? 'selected' : '' }}>@lang('Active')</option>
                <option value="inactive" {{ isset($reward) && $reward->status == 'inactive' ? 'selected' : '' }}>@lang('Inactive')</option>
              </select>
            </div>
          </div>
          <input type="hidden" name="id" value="{{ $reward->id ?? '' }}">
          <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
        </form>
    </div>
  </div>
</div>
    </div>
</div>
 
  
@endsection
 
@push('script')
    <script>
      $(document).ready(function () {
        const $multiplierInput = $('input[name="pnl_required_multiplier"]');
        const $amountInput = $('input[name="amount"]');
        const $requiredAmountInput = $('input[name="pnl_required_amount"]');

        function updateRequiredAmount() {
          const multiplier = parseFloat($multiplierInput.val()) || 0;
          const amount = parseFloat($amountInput.val()) || 0;
          $requiredAmountInput.val((multiplier * amount).toFixed(2));
        }

        $multiplierInput.on('input', updateRequiredAmount);
        $amountInput.on('input', updateRequiredAmount);
      });
    </script>
</script>
      @endpush