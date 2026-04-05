@extends('layouts.default')

<!-- タイトル -->
@section('title','ログイン')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/auth.css')  }}">
@endsection

<!-- 本体 -->
@section('content')

@include('components.header')
<form action="/login" method="post" class="entry">
    @csrf
    <h1 class="content__title">ログイン</h1>
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
    <button class="entry__btn">ログインする</button>
    <a href="/register" class="link">会員登録はこちら</a>
</form>
@endsection