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
<h1 class="content__title--left-border">{{ $user->name }}さんの勤怠</h1>

<nav class="nav">
    <ul class="nav__list">
        <li class="nav__item">
            <form action="{{ route('admin.staff_detail', ['id' => $user->id]) }}" method="post">
                @csrf
                <input type="hidden" name="month" value="{{ $last_month }}">
                <button>前月</button>
            </form>
        </li>
        <li class="nav__item nav__item--date">{{ $month }}</li>
        <li class="nav__item">
            <form action="{{ route('admin.staff_detail', ['id' => $user->id]) }}" method="post">
                @csrf
                <input type="hidden" name="month" value="{{ $next_month }}">
                <button>翌月</button>
            </form>
        </li>
    </ul>
</nav>

<table class="table table--list">
    <thead>
        <tr>
            <th class="table__header">日付</th>
            <th class="table__header">出勤</th>
            <th class="table__header">退勤</th>
            <th class="table__header">休憩</th>
            <th class="table__header">合計</th>
            <th class="table__header">詳細</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($days as $day)
        @php $key = $day->format('Y-m-d'); @endphp
        <tr class="table__row">
            <td class="table__cell">{{ $day->format('m/d') . ' (' . $week[date('w',strtotime($day->format('Ymd')))] . ')' }}</td>
            @if (isset($attendances[$key]))
            <td class="table__cell">{{ $attendances[$key]->clock_in_at?->format('H:i') }}</td>
            <td class="table__cell">{{ $attendances[$key]->clock_out_at?->format('H:i') }}</td>
            <td class="table__cell">{{ $attendances[$key]->break_hm }}</td>
            <td class="table__cell">{{ $attendances[$key]->work_hm }}</td>
            <td class="table__cell">
                <a href="{{ route('admin.detail', ['attendance_id' => $attendances[$key]->id]) }}">詳細</a>
            </td>
            @else
            <td class="table__cell"></td>
            <td class="table__cell"></td>
            <td class="table__cell"></td>
            <td class="table__cell"></td>
            <td class="table__cell"></td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
</div>