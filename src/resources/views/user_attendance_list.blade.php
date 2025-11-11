@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/user_attendance_list.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
@endsection


@section('content')
<div class="attendance">

    <div class="attendance__title">
        勤怠一覧
    </div>

    <div class="attendance__content">
        <div class="attendance__content-month">
            <form action="{{ route('attendance.index') }}" method="post" class="month-nav__form">
                @csrf
                <button name="move" value="prev" class="month-nav__btn">← 前月</button>

                <div class="month-nav__current">
                    <img src="{{ asset('img/calendar.png') }}" class="month-nav__icon" alt="calendar">
                    <span class="month-nav__text">{{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}</span>
                </div>

                <button name="move" value="next" class="month-nav__btn">翌月 →</button>
            </form>
        </div>
    </div>

    <div class="attendance__content-date">
        <table>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @php
            use Carbon\Carbon;
            $w = ['日','月','火','水','木','金','土']; // 曜日
            @endphp

            @foreach($days as $day)
            @php
            $a = $byDate[$day] ?? null; // その日に勤怠があればモデル、無ければ null
            $c = Carbon::parse($day);
            $label = $c->format('m/d') . '（' . $w[$c->dayOfWeek] . '）';
            @endphp
            <tr>
                <td>{{ $label }}</td>

                {{-- 出勤（未打刻は空欄） --}}
                <td>{{ $a && $a->work_start ? $a->work_start->format('H:i') : '' }}</td>

                {{-- 退勤（未打刻は空欄） --}}
                <td>{{ $a && $a->work_end ? $a->work_end->format('H:i') : '' }}</td>

                {{-- 休憩（件数。ゼロは空欄） --}}
                <td>{{ $a?->rest_total_human }}</td>

                {{-- 合計（未計算/未打刻は空欄） --}}
                <td>{{ $a && $a->work_time_total ? $a->work_time_human : '' }}</td>

                {{-- 詳細 --}}
                <td>
                    <a href="{{ route('correction.store', ['id' => auth()->id()]) }}" class="detail-button">詳細</a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>

</div>
@endsection