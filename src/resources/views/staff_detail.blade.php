@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/staff_detail.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="content">
    <div class="content__title">
        {{ $user->name }}さんの勤怠
    </div>


    <form action="{{ route('staff.show', $user->id) }}" method="get" class="month-nav__form">

        {{-- 今表示している年月も一緒に送る --}}
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="month" value="{{ $month }}">

        <button name="move" value="prev" class="month-nav__btn">← 前月</button>

        <div class="month-nav__current">
            <img src="{{ asset('img/calendar.png') }}" class="month-nav__icon" alt="calendar">
            <span class="month-nav__text">
                {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}
            </span>
        </div>

        <button name="move" value="next" class="month-nav__btn">翌月 →</button>
    </form>

    @php
    use Carbon\Carbon;
    $weekNames = ['日','月','火','水','木','金','土'];
    @endphp

    <div class="attendance__list">
        <table>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @foreach ($attendances as $attendance)
            <tr>
                <td>
                    <span class="date-monthday">{{ $attendance->date->format('n/j') }}</span>
                    <span>({{ $weekNames[$attendance->date->dayOfWeek] }})</span>
                </td>

                <td>{{ $attendance->work_start->format('H:i') }}</td>
                <td>{{ $attendance->work_end->format('H:i') }}</td>
                <td>{{ $attendance->rest_total_human }}</td>
                <td>{{ $attendance->work_time_human }}</td>
                <td>
                    <a href="{{ route('admin.show', $attendance->id) }}" class="detail__button">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>

    <form action="{{ route('attendances.export') }}" method="get" class="cfv__wrapper">
            <button class="cfv__button">CFV出力</button>
    </form>
</div>
@endsection