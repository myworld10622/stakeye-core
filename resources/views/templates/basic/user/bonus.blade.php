@extends($activeTemplate . 'layouts.master')
@section('content')
    <section class="pt-100 pb-100">
        <div class="container">


<div class="row justify-content-center mb-4">
    <div class="col-md-12">
        <div id="jsonResponseMessages"></div>
    </div>
</div>
<div class="row justify-content-center mb-4">
    <div class="col-md-6">
        <div class="card custom__bg">
            <div class="card-body text-center">
                <h5 class="mb-2">@lang('Current Bonus Points')</h5>
                <h2 class="fw-bold text--base">
                     {{ auth()->user()->reward_points ?? 0 }}
                </h2>
                <a href="{{ route('user.claim-bonus-history') }}" class="btn btn--base mt-3">
                    @lang('View Bonus History')
                </a>
            </div>
        </div>
    </div>
</div>
 
        
 
            <div class="row justify-content-center mt-4">
                
                <div class="card custom__bg">
                    <div class="card-body">
                       
                    <ul class="nav nav-tabs mb-3" id="bonusTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="active-rewards-tab" data-bs-toggle="tab" data-bs-target="#active-rewards" type="button" role="tab" aria-controls="active-rewards" aria-selected="true">
                              @lang('Claimed Bonus Rewards')   
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="claimed-rewards-tab" data-bs-toggle="tab" data-bs-target="#claimed-rewards" type="button" role="tab" aria-controls="claimed-rewards" aria-selected="false">
                          @lang('All Bonus Rewards')
                            </button>
                        </li> 
                    </ul>
                    <div class="tab-content" id="bonusTabsContent">
                        <div class="tab-pane fade show active" id="active-rewards" role="tabpanel" aria-labelledby="active-rewards-tab">
                   
                       <div class="table-responsive--md">
                        <table class="custom--table table">
                            <thead>
                                <tr>
                                    <th scope="col">@lang('S.N.')</th>
                                    <th scope="col">@lang('Name')</th> 
                                    <th scope="col">@lang('Bonus Amount')</th>
                                    <th scope="col">@lang('Timeline in Days')</th>
                                    <th scope="col">@lang('Perform in')</th>
                                    <th scope="col">@lang('Status')</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            

                                @forelse($userInfo->bonuses as $key => $bonus)
                                <tr>
                                    <td> {{ $key+1 }}</td>
                                    <td>{{ $bonus->reward->name }}</td> 
                                    <td>{{ showAmount($bonus->reward->redeemed->total_amount) }}</td>
                                    <td>{{ $bonus->reward->timeline_in_days }}</td>
                                    <td>{{ intval($bonus->reward->pnl_required_multiplier) }} X on {{ $bonus->reward->conversion_type }}</td>
                                    <td>
                                        <a href="javascript:void(0)" class="showBonusDescription" data-description="{{ $bonus->reward->description }}">
                                            <i class="las la-info-circle"></i> @lang('View Description')
</a>
                                        <br/>
                                         <br/>
                                        Transfer to Main wallet : {{ $bonus->converted_amount }}
                                        <br/>
                               
                                      @php
                                        $daysPassed = \Carbon\Carbon::parse($bonus->created_at)->diffInDays(now());
                                        $expired = $daysPassed > $bonus->reward->timeline_in_days;
                                        $daysLeft = max(0, $bonus->reward->timeline_in_days - $daysPassed);
                                        $exipreAt = \Carbon\Carbon::parse($bonus->created_at)->addDays($bonus->reward->timeline_in_days);
                                      @endphp
                                        @if($expired || $bonus->status == 'expired')
                                            <span class="text--danger">Expired</span>
                                        @else
                                            <span class="text--success">Expire at : {{ $exipreAt }}</span>
                                            <br/>
                                            @if( $bonus->reward->reward_type !='gift_credit')
                                            <a href="{{ route('user.calculate-reward-bonus', $bonus->id) }}" class="btn btn--sm btn--base claimNowBtn mt-2">
                                                <i class="las la-calculator"></i> @lang('Calculate')

                                            </a>
                                            @endif
                                        @endif

                                            

                                    </td>                                        
                                   
                                </tr>
                                @empty
                                    <tr>
                                        <td class="rounded-bottom text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>  
                        </div>
                        <div class="tab-pane fade" id="claimed-rewards" role="tabpanel" aria-labelledby="claimed-rewards-tab">
                                  <div class="table-responsive--md">
                        <table class="custom--table table">
                            <thead>
                                <tr>

                                     <th scope="col">@lang('S.N.')</th>
                                    <th scope="col">@lang('Name')</th> 
                                    <th scope="col">@lang('Bonus Amount')</th>
                                    <th scope="col">@lang('Timeline in Days')</th>
                                    <th scope="col">@lang('Perform in')</th>
                                    <th scope="col">@lang('Status')</th>
 
                                    
                                </tr>
                            </thead>
                            <tbody>
                            <?php

                            $firstDeposit = App\Models\Deposit::where('user_id', auth()->user()->id)->where('status', 1)->first();
                             
                            ?>
                

                                @forelse($activeRewards as $key => $reward)
                                    @if (($reward->is_first_deposit == 1 && $firstDeposit) ||$reward->reward_type == 'gift_credit' && $firstDeposit )
                                        @continue
                                    @endif
                                <tr>
                                    <td> {{ $key+1 }}</td>
                                    <td>{{ $reward->name }}</td> 

                                      <td>{{ showAmount($reward->max_amount_allowed) }}</td> 
                               
 
                                    <td>{{ $reward->timeline_in_days }}</td>
                                  <td>{{ intval($reward->pnl_required_multiplier) }} X on {{ $reward->conversion_type }}</td>
                                    <td>

                                        <a href="javascript:void(0)" class="showBonusDescription" data-description="{{ $reward->description }}">
                                            <i class="las la-info-circle"></i> @lang('View Description')
</a>
                                        <br/> 

                                        @if($reward->hasClaimedReward($reward->id, auth()->user()->id))
                                        <span class="text--success">@lang('Already Claimed')</span>
                                        @else
                                        @if($reward->status == 'inactive')
                                            <span class="text--danger">@lang('Inactive')</span>
                                          @else
                                          
                                            @if($reward->reward_type == 'deposit')
                                            <a href="{{ route('user.deposit.index') }}" class="btn btn--sm btn--base claimNowBtn"  >
                                                @lang('Claim Now')
                                            </a>
                                            @else
                                            <a href="{{ route('user.claim-bonus', $reward->id) }}" class="btn btn--sm btn--base claimNowBtn"  >
                                                @lang('Claim Now')
                                            </a>
                                            @endif
                                            @endif
                                        
                                        @endif        
                                    </td>                                        
                                   
                                </tr>
                                @empty
                                    <tr>
                                        <td class="rounded-bottom text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>  
                        </div>
                    
                    </div>
               
                    </div>
                   
                </div>
            </div>
        </div>
    </section>



    <!-- Bonus Description Modal -->
    <div class="modal fade" id="bonusDescriptionModal" tabindex="-1" aria-labelledby="bonusDescriptionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom__bg">
                <div class="modal-header">
                    <h5 class="modal-title" id="bonusDescriptionModalLabel">@lang('Bonus Description')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('Close')"></button>
                </div>
                <div class="modal-body" id="bonusDescriptionContent">
                    <!-- Description will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    @push('script')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
            // Delegate click event for all bonus description buttons/links
            document.querySelectorAll('.showBonusDescription').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            
                            var description = this.getAttribute('data-description') || '';
                            document.getElementById('bonusDescriptionContent').innerHTML = description;
                            var modal = new bootstrap.Modal(document.getElementById('bonusDescriptionModal'));
                            modal.show();
                    });
            });
    });
    </script>
    @endpush

    @push('style')
    <style>
    /* Optional: style for modal content */
    #bonusDescriptionContent {
            white-space: pre-line;
    }
    </style>
    @endpush
@endsection
  
