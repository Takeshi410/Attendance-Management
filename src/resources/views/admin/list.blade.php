@extends('layouts.default')

<!-- タイトル -->
@section('title','勤怠一覧')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/list.css')  }}">
@endsection

<!-- 本体 -->
@section('content')
@include('components.header_admin')

<div class="content">
<h1 class="content__title--left-border">{{ $date->format('Y年n月j日') }}の勤怠一覧</h1>

<nav class="nav">
    <ul class="nav__list">
        <li class="nav__item">
            <form action="/admin/attendance/list" method="post">
                @csrf
                <input type="hidden" name="date" value="{{ $last_date }}">
                <button>前日</button>
            </form>
        </li>
        <li class="nav__item nav__item--date">{{ $date->format('Y/m/d') }}</li>
        <li class="nav__item">
            <form action="/admin/attendance/list" method="post">
                @csrf
                <input type="hidden" name="date" value="{{ $next_date }}">
                <button>翌日</button>
            </form>
        </li>
    </ul>
</nav>

<table class="table table--list">
    <thead>
        <tr>
            <th class="table__header">名前</th>
            <th class="table__header">出勤</th>
            <th class="table__header">退勤</th>
            <th class="table__header">休憩</th>
            <th class="table__header">合計</th>
            <th class="table__header">詳細</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($attendances as $attendance)
        <tr class="table__row">
            <td class="table__cell">{{ $attendance->user->name }}</td>
            <td class="table__cell">{{ $attendance->clock_in_at?->format('H:i') }}</td>
            <td class="table__cell">{{ $attendance->clock_out_at?->format('H:i') }}</td>
            <td class="table__cell">{{ $attendance->break_hm }}</td>
            <td class="table__cell">{{ $attendance->work_hm }}</td>
            <td class="table__cell">
                <a href="{{ route('admin.detail', ['id' => $attendance->id]) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>