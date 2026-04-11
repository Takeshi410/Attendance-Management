@extends('layouts.default')

<!-- タイトル -->
@section('title','勤怠詳細')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/approve.css')  }}">
@endsection

<!-- 本体 -->
@section('content')
@include('components.header_admin')

<div class="content">
<h1 class="content__title--left-border">勤怠詳細</h1>

<form action="{{ route('admin.approve_patch', ['attendance_correction_request_id' => $adjustment->id]) }}" method="post">
    @csrf
    @method('PATCH')
    <table class="table table--detail">
        <colgroup>
            <col class="table__coll--header">
            <col class="table__coll--data">
            <col class="table__coll--data">
            <col class="table__coll--data">
            <col>
        </colgroup>

        <tr class="table__row">
            <th class="table__header">名前</th>
            <td class="table__cell">{{ $adjustment->attendance->user->name }}</td>
            <td colspan="3" class="table__cell"></td>
        </tr>
        <tr class="table__row">
            <th class="table__header">日付</th>
            <td class="table__cell">{{ $adjustment->attendance->work_date->format('Y年') }}</td>
            <td class="table__cell"></td>
            <td class="table__cell">{{ $adjustment->attendance->work_date->format('n月j日') }}</td>
            <td class="table__cell"></td>
        </tr>
        <tr class="table__row">
            <th class="table__header">出勤・退勤</th>
            <td class="table__cell">{{ $adjustment->after_clock_in_at->format('H:i') }}</td>
            <td class="table__cell">〜</td>
            <td class="table__cell">{{ $adjustment->after_clock_out_at->format('H:i') }}</td>
            <td class="table__cell"></td>
        </tr>
        @foreach ($adjustment->breakAdjustments as $break)
            <tr class="table__row">
            @if ($break->break->sequence === 1)
                <th class="table__header">休憩</th>
            @else
                <th class="table__header">休憩{{ $break->break->sequence }}</th>
            @endif
                <td class="table__cell">{{ $break->after_break_start_at->format('H:i') }}</td>
                <td class="table__cell">〜</td>
                <td class="table__cell">{{ $break->after_break_end_at->format('H:i') }}</td>
                <td class="table__cell"></td>
            </tr>
        @endforeach
        <tr class="table__row">
            <th class="table__header">備考</th>
            <td colspan="3" class="table__cell table__cell--remarks">{{ $adjustment->remarks }}</td>
        </tr>
    </table>

    <div class="content__approve">
        <div class="content__approve--error">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </div>

        @if (
            (($adjustment->is_approval) === false)
        )
            <button class="content__approve--btn">承認</button>
        @else
            <button class="content__approve--btn-disable" disabled>承認済み</button>
        @endif
    </div>
</form>

@endsection