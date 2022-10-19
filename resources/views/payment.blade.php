@extends('layouts.app')

@section('content')

@if (session('message'))
    <div class="flash_message">
        {{ session('message') }}
    </div>
@endif

@if (session('error-message'))
    <p>{{ session('error-message') }}</p>
@endif

<form action="{{ route('payment') }}" method="post">
    @csrf
    <script
        src="https://checkout.pay.jp/"
        class="payjp-button"
        data-key="{{ config('payjp.public_key') }}"
        data-text="カード情報入力"
        data-submit-text="カートを登録する"
        ></script>
</form>

@if(!empty($cardList))
    <p>以前使用したカードで決済</p>
    <form action="{{ route('payment') }}" method="post">
        @csrf
        @foreach($cardList as $card)
            <div class="card-item">
                <label>
                    <input type="radio" name="payjp_card_id" value="{{ $card['id'] }}">
                    <span class="brand">{{ $card['brand'] }}</span>
                    <span class="number">{{ $card['cardNumber'] }}</span>
                </label>
                <div>
                    <p>名義：{{ $card['name'] }}</p>
                    <p>期限：{{ $card['exp_year'] }}/{{ $card['exp_month'] }}</p>
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">選択したカードで決済する</button>
    </form>
@endif

@endsection
