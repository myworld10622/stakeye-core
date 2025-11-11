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
              
            </div>
        </div>
    </div>
</div>
 
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <form method="GET" action="">
                    <div class="input-group">
                        <select class="form-select" name="type" style="    background: #dbdbdb;">
                            <option value="">@lang('All Types')</option>
                            <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>@lang('Credit')</option>
                            <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>@lang('Debit')</option>
                            <option value="converted" {{ request('type') == 'converted' ? 'selected' : '' }}>@lang('Converted')</option>
                        </select>
                        <button class="btn btn--base" type="submit">@lang('Filter')</button>
                    </div>
                </form>
            </div>
        </div>
 
            <div class="row justify-content-center mt-4">
                <div class="card custom__bg">
                    <div class="card-body">
                        <h5 class="mb-3">@lang('Bonus Transactions')</h5>
                        <div class="table-responsive--md">
                            <table class="custom--table table">
                                <thead>
                                    <tr>
                                        <th scope="col">@lang('S.N.')</th>
                                        <th scope="col">@lang('Reward')</th>
                                        <th scope="col">@lang('Amount')</th>
                                        <th scope="col">@lang('Type')</th>
                                        <th scope="col">@lang('Date')</th>
                                        <th scope="col">@lang('Detail')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sn = 1;
                                    @endphp

                                    @forelse($bonusTransactions as $transaction)
                                    <tr>
                                        <td>{{ $sn++ }}</td>
                                        <td>{{ $transaction->redeemReward->reward->name ?? '' }}</td>
                                        <td>{{ showAmount($transaction->amount ?? 0) }}</td>
                                        <td>{{ ucfirst($transaction->type ?? '') }}</td>
                                        <td>{{ showDateTime($transaction->created_at) }}</td>
                                        <td>
                                             {{$transaction->details??''}}
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
                    {{ paginateLinks($bonusTransactions) }}
                </div>
            </div>
        </div>
    </section>
@endsection
  
