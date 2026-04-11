@extends('layouts.default')

<!-- タイトル -->
@section('title','出勤登録')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/correction.css')  }}">
@endsection

<!-- 本体 -->
@section('content')
@if($role === 'admin')
    @include('components.header_admin')
@else
    @include('components.header')
@endif

<div class="content">
<h1 class="content__title--left-border">申請一覧</h1>

<nav class="nav">
    <ul class="nav__list">
        <li class="{{ $tab === 'recommend' ? 'nav__item nav__item--active' : 'nav__item'}}"><a href="/stamp_correction_request/list">承認待ち</a></li>
        <li class="{{ $tab === 'approved' ? 'nav__item nav__item--active' : 'nav__item'}}"><a href="/stamp_correction_request/list?tab=approved">承認済み</a>
        </li>
    </ul>
</nav>

<table class="table table--list">
    <thead>
        <tr>
            <th class="table__header">状態</th>
            <th class="table__header">名前</th>
            <th class="table__header">対象日時</th>
            <th class="table__header">申請理由</th>
            <th class="table__header">申請日時</th>
            <th class="table__header">詳細</th>
        </tr>
    </thead>

    <tbody>
    @foreach ($corrections as $correction)
        <tr class="table__row">
            @if ($correction->is_approval)
                <td class="table__cell">承認済み</td>
            @else
                <td class="table__cell">承認待ち</td>
            @endif
        <td class="table__cell">{{ $correction->attendance->user->name }}</td>
        <td class="table__cell">{{ $correction->attendance->work_date->format('Y/m/d') }}</td>
        <td class="table__cell">{{ $correction->remarks }}</td>
        <td class="table__cell">{{ $correction->created_at->format('Y/m/d') }}</td>
        <td class="table__cell">
            @if($role === 'admin')
                <a href="{{ route('admin.approve', ['attendance_correction_request_id' => $correction->id]) }}">詳細</a>
            @else
                <a href="{{ route('attendance.detail', ['attendance_id' => $correction->attendance->id]) }}">詳細</a>
            @endif
        </td>
    @endforeach
    </tbody>
</table>
@endsection