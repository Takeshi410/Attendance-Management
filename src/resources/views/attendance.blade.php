@extends('layouts.default')

<!-- タイトル -->
@section('title','出勤登録')

<!-- css読み込み -->
@section('css')
<link rel="stylesheet" href="{{ asset('/css/attendance.css')  }}">
@endsection

<!-- 本体 -->
@section('content')
@include('components.header')

<div class="attendance">
    @if ($attendance === null)
        <div class="attendance__stat"><p class="attendance__stat--p">勤務外</p></div>
    @elseif ($attendance->clock_out_at !== null)
        <div class="attendance__stat"><p class="attendance__stat--p">退勤済</p></div>
    @else
        <div class="attendance__stat"><p class="attendance__stat--p">出勤中</p></div>
    @endif

    <p class="attendance__date" id="date"></p>
    <h2 class="attendance__time" id="time"></h2>

    <div class="attendance__btn">
    @if ($attendance === null)
        <form action="/attendance/clock-in" method="post">
            @csrf
            <button type="submit" class="btn">出勤</button>
        </form>
    @elseif ($attendance->clock_out_at !== null)
        <p class="attendance_message">お疲れ様でした</p>
    @elseif ($attendance->latestBreak !== null && $attendance->latestBreak->break_end_at === null)
        <form action="/attendance/break-end" method="post">
            @method('PATCH')
            @csrf
            <input type="hidden" name="break_id" value="{{ $attendance->latestBreak->id }}">
            <button type="submit" class="btn btn--break">休憩戻</button>
        </form>
    @else
        <form action="/attendance/clock-out" method="post">
            @method('PATCH')
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance['id'] }}">
            <button type="submit" class="btn">退勤</button>
        </form>
        <form action="/attendance/break-start" method="post">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance['id'] }}">
            <button type="submit" class="btn btn--break">休憩入</button>
        </form>
    @endif
    </div>
</div>

<script>
    const weekdays = ['日', '月', '火', '水', '木', '金', '土'];

    function updateDateTime() {
        const now = new Date();
        const yyyy = now.getFullYear();
        const mm = String(now.getMonth() + 1);
        const dd = String(now.getDate());
        const hh = String(now.getHours()).padStart(2, '0');
        const mi = String(now.getMinutes()).padStart(2, '0');
        const w = weekdays[now.getDay()];

        document.getElementById('date').textContent =
        `${yyyy}年${mm}月${dd}日(${w})`;
        document.getElementById('time').textContent =`${hh}:${mi}`;
    }

    updateDateTime();
    setInterval(updateDateTime, 1000);
</script>
@endsection