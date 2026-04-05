@extends('layouts.default')

<!-- タイトル -->
@section('title','勤怠詳細')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/detail.css')  }}">
@endsection

<!-- 本体 -->
@section('content')
@include('components.header')

<div class="content">
<h1 class="content__title--left-border">勤怠詳細</h1>

<form action="{{ route('attendance.request', ['attendance_id' => $attendance->id]) }}" method="post">
    @csrf
    <table class="table table--detail {{ $attendance->latestAttendanceAdjustment ? 'readonly' : '' }}">
        <colgroup>
            <col class="table__coll--header">
            <col class="table__coll--data">
            <col class="table__coll--data">
            <col class="table__coll--data">
            <col>
        </colgroup>

        <tr class="table__row">
            <th class="table__header">名前</th>
            <td class="table__cell">{{ $attendance->user->name }}</td>
            <td colspan="3" class="table__cell"></td>
        </tr>
        <tr class="table__row">
            <th class="table__header">日付</th>
            <td class="table__cell">{{ $attendance->work_date->format('Y年') }}</td>
            <td class="table__cell"></td>
            <td class="table__cell">{{ $attendance->work_date->format('n月j日') }}</td>
            <td class="table__cell"></td>
        </tr>
        <tr class="table__row">
            <th class="table__header">出勤・退勤</th>
            <td class="table__cell"><input type="text" name="clock_in_at" value="{{ old('clock_in_at', optional(optional($attendance->latestAttendanceAdjustment)->after_clock_in_at ?? $attendance->clock_in_at)->format('H:i') ?? '') }}"></input></td>
            <td class="table__cell">〜</td>
            <td class="table__cell"><input type="text" name="clock_out_at" value="{{ old('clock_out_at', optional(optional($attendance->latestAttendanceAdjustment)->after_clock_out_at ?? $attendance->clock_out_at)->format('H:i') ?? '') }}"></input></td>
            <td class="table__cell"></td>
        </tr>
        @foreach ($attendance->breaks as $break)
            <tr class="table__row">
            @if ($break->sequence === 1)
                <th class="table__header">休憩</th>
            @else
                <th class="table__header">休憩{{ $break->sequence }}</th>
            @endif
                <td class="table__cell"><input type="text" name="breaks[{{ $break->id }}][break_start_at]" value="{{ old('breaks.' . $break->id . '.break_start_at', optional(optional($break->breakAdjustment)->after_break_start_at ?? $break->break_start_at)->format('H:i')) }}"></input></td>
                <td class="table__cell">〜</td>
                <td class="table__cell"><input type="text" name="breaks[{{ $break->id }}][break_end_at]" value="{{ old('breaks.' . $break->id . '.break_end_at', optional(optional($break->breakAdjustment)->after_break_end_at ?? $break->break_end_at)->format('H:i')) }}"></input></td>
                <td class="table__cell"></td>
                <input type="hidden" name="breaks[{{ $break->id}}][break_id]" value="{{ $break['id'] }}">
            </tr>
        @endforeach
        <tr class="table__row">
            <th class="table__header">備考</th>
            <td colspan="3" class="table__cell"><textarea name="remarks" rows="3" >{{ old('remarks', $attendance->latestAttendanceAdjustment->remarks ?? '') }}</textarea></td>
        </tr>
    </table>

    <div class="content__submit">
        <div class="content__submit--error">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </div>

        @if ($attendance->latestAttendanceAdjustment)
            <p class="content__submit--message">*承認待ちのため修正はできません。</p>
        @else
            <button class="content__submit--btn">申請</button>
        @endif
    </div>
</form>

@if ($attendance->latestAttendanceAdjustment)
<script>
const rdonly = document.querySelectorAll('input, textarea');
for(var i = 0; i< rdonly.length; i++){
rdonly[i].setAttribute("readonly","readonly");
}
</script>
@endif

@endsection