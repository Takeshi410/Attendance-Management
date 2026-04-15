@extends('layouts.default')

<!-- タイトル -->
@section('title','会員登録')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/auth.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<form action="/register" method="post" class="entry" novalidate>
    @csrf
    <h1 class="content__title">会員登録</h1>
    <label for="name" class="entry__label">ユーザ名</label>
    <input name="name" id="name" type="text" class="entry__input" value="{{ old('name') }}">
    <div class="entry__error">
        @error('name')
        {{ $message }}
        @enderror
    </div>
    <label for="mail" class="entry__label">メールアドレス</label>
    <input name="email" id="mail" type="email" class="entry__input" value="{{ old('email') }}">
    <div class="entry__error">
        @error('email')
        {{ $message }}
        @enderror
    </div>
    <label for="password" class="entry__label">パスワード</label>
    <input name="password" id="password" type="password" class="entry__input">
    <div class="entry__error">
        @error('password')
        {{ $message }}
        @enderror
    </div>
    <label for="password_confirm" class="entry__label">確認用パスワード</label>
    <input name="password_confirmation" id="password_confirm" type="password" class="entry__input">
    <div class="entry__error">
        @error('password_confirmation')
        {{ $message }}
        @enderror
    </div>
    <button class="entry__btn">登録する</button>
    <a href="/login" class="link">ログインはこちら</a>
</form>
@endsection