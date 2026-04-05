@extends('layouts.default')

<!-- タイトル -->
@section('title','メール認証')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/verify.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<div class="verify">
    <p class="verify--p">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

    <button onclick="location.href='http://localhost:8025/#'" class="verify--button">認証はこちらから</button>

        @if (session('status') == 'verification-link-sent')
            <p class="verify_resend--message">
                認証メールを送信しました
            </p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="verify__resend">
                認証メールを再送する
            </button>
        </form>
</div>
@endsection