@extends('admin.layouts.app')
@section('panel')
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body p-0">
          <div class="table-responsive--md table-responsive">
            <table class="table table--light style--two">
              <thead>
                <tr>
                  <th>@lang('User')</th>
                  <th>@lang('Reward Info')</th> 
                  <th>@lang('Amount')</th>
                  <th>@lang('Converted Amount')</th>
                  <th>@lang('Status')</th>
                  <th>@lang('Claimed At')</th>
                  <th>@lang('Action')</th>
                </tr>
              </thead>
              <tbody>
                @forelse($rewardUsers as $rewardUser)
                  <tr>
                    <td>
                      <span class="fw-bold">{{ $rewardUser->user->username ?? '-' }}</span>
                      <br>
                      <span class="small">{{ $rewardUser->user->email ?? '' }}</span>
                    </td>
                    <td>
                      {{ $rewardUser->reward->name ?? '-' }}
                      <br/>
                      Reward /Type : {{ $rewardUser->reward->amount ?? '-' }} ({{ ucfirst($rewardUser->reward->reward_type ?? '-') }})
                       <br/>
                       Deposit : {{ $rewardUser->reward->min_deposit_amount }} - {{ $rewardUser->reward->max_deposit_amount }}
                       <br/>
                        Max Amount : {{ $rewardUser->reward->max_amount_allowed }}
                        <br/>
                        Conversion : {{ $rewardUser->reward->conversion_type }}  on {{ $rewardUser->reward->pnl_required_multiplier }} X
                    </td>
 
                    <td>{{ $rewardUser->reward->redeemed->total_amount ?? '-' }}</td>
                    <td>{{ $rewardUser->reward->redeemed->converted_amount ?? '-' }}</td>

                    <td>{{$rewardUser->reward->redeemed->status}}</td>
                    <td>{{ $rewardUser->reward->redeemed->created_at}}</td>
                    <td>
                    <form method="POST" action="{{ route('admin.reward-update-status', $rewardUser->id) }}">
                      @csrf
                      @method('PUT')
                      <div class="input-group">
                        <select name="status" class="form-select form-select-sm">
                          <option value="pending" {{ $rewardUser->status == 'pending' ? 'selected' : '' }}>Pending</option>
                          <option value="active" {{ $rewardUser->status == 'active' ? 'selected' : '' }}>Active</option>
                          <option value="expired" {{ $rewardUser->status == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">@lang('Update')</button>
                      </div>
                    </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td class="text-muted text-center" colspan="100%">@lang('No data found')</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if ($rewardUsers->hasPages())
          <div class="card-footer py-4">
            {{ $rewardUsers->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection

@push('breadcrumb-plugins')
  <x-search-form placeholder="User / Email" />
@endpush
