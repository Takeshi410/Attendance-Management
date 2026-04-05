@extends('layouts.default')

<!-- タイトル -->
@section('title','スタッフ一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/list.css')  }}">
@endsection

<!-- 本体 -->
@section('content')
@include('components.header_admin')

<div class="content">
<h1 class="content__title--left-border">スタッフ一覧</h1>

<table class="table table--staff-list">
    <thead>
        <tr>
            <th class="table__header--left">名前</th>
            <th class="table__header">メールアドレス</th>
            <th class="table__header--right">月次勤怠</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($members as $member)
        <tr class="table__row">
            <td class="table__cell--left">{{ $member->name }}</td>
            <td class="table__cell">{{ $member->email }}</td>
            <td class="table__cell--right">
                <a href="{{ route('admin.staff_detail', ['id' => $member->id]) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>