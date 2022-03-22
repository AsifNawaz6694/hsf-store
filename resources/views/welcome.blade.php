@extends('layouts.home')
@section('title', config('app.name', 'ultimatePOS'))

@section('content')
    <style type="text/css">
        .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
                margin-top: 10%;
                text-align: center;
            }
        .title {
                font-size: 84px;
            }
        .tagline {
                font-size:25px;
                font-weight: 300;
                text-align: center;
            }
    </style>
    <div class="title flex-center" style="font-weight: 600 !important;">
        {{ config('app.name', 'Hassan Sanitary & Fitting StoreHassan Sanitary & Fitting StoreHassan Sanitary & Fitting Store') }}
    </div>
    <p class="tagline">
        {{ env('APP_TITLE', '') }}
    </p>
@endsection
            