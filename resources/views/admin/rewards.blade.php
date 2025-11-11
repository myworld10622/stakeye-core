@extends('admin.layouts.app')
@section('panel')
<div class="row mb-none-30">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive--md  table-responsive">
            <table class="table table--light style--two">
            <thead>
              <tr>
              <th>@lang('Sr.No')</th>
              <th>@lang('Name')</th>
              <th>@lang('Type')</th>
              <th>@lang('Min Deposit Amount')</th>
              <th>@lang('Max Deposit Amount')</th>
              <th>@lang('Reward Type')</th> 
              <th>@lang('Amount')</th>
              <th>@lang('Max Amount Allowed')</th>
              <th>@lang('Timeline(Days)')</th>
              <th>@lang('First Deposit?')</th>
              <th>@lang('P&L Required Multiplier')</th>
              <th>@lang('For')</th>
              <th>@lang('Users')</th>
              <th>@lang('Status')</th>
              <th>@lang('Action')</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rewards as $key => $reward)
              <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $reward->name }}</td>
              <td>{{ $reward->type }}</td>
              <td>{{ $reward->min_deposit_amount }}</td>
              <td>{{ $reward->max_deposit_amount }}</td>
              <td>{{ ucfirst($reward->reward_type) }}</td> 
              <td>{{ $reward->amount }}</td>
              <td>{{ $reward->max_amount_allowed }}</td>
              <td>{{ $reward->timeline_in_days }}</td>
              <td>{{ $reward->for_first_deposit ==1?'Yes':'No'}}</td>
              <td>{{ number_format($reward->pnl_required_multiplier, 2) }}</td>
              <td>{{ $reward->conversion_type}}</td>
              <td><a href="{{ route('admin.reward-users', $reward->id) }}">{{$reward->users->count()}}</a></td>
              <td>{{ ucfirst($reward->status) }}</td>
              <td>
              <a href="{{ route('admin.mange-rewards', $reward->id) }}" class="icon-btn" title="@lang('Manage')">
                <i class="las la-edit"></i>
              </a>
              </td>
              </tr>
              @empty
              <tr>
              <td colspan="14" class="text-center">{{ __($emptyMessage) }}</td>
              </tr>
              @endforelse
            </tbody>
            </table><!-- table end -->
        </div>
      </div>
    </div>
  </div>
</div>

 
 @endsection
@push('breadcrumb-plugins')
    <a href="{{ route('admin.mange-rewards') }}" class="btn btn-sm btn-outline--warning">
      <i class="las la-bug"></i> @lang('Add New Coupon')
    </a>
   
@endpush
