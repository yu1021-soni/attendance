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

            {{-- 月移動 --}}
            <form action="{{ route('attendance.index') }}" method="get" class="month-nav__form">

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
            $weekNames = ['日','月','火','水','木','金','土'];
            @endphp

            @foreach($days as $day)
            @php
            $attendance = $attendanceByDate[$day] ?? null;

            $date = \Carbon\Carbon::parse($day);

            $weekNames = ['日','月','火','水','木','金','土'];
            $label = $date->format('m/d') . '（' . $weekNames[$date->dayOfWeek] . '）';
            @endphp

            <tr>
                <td>{{ $label }}</td>

                {{-- 出勤 --}}
                <td>{{ $attendance?->work_start ? $attendance->work_start->format('H:i') : '' }}</td>

                {{-- 退勤 --}}
                <td>{{ $attendance?->work_end ? $attendance->work_end->format('H:i') : '' }}</td>

                {{-- 休憩 --}}
                <td>{{ $attendance?->rest_total_human }}</td>

                {{-- 合計 --}}
                <td>{{ $attendance?->work_time_human ?? '' }}</td>

                {{-- 詳細 --}}
                <td>
                    {{-- <a href="{{ route('correction.store', ['id' => auth()->id()]) }}" class="detail-button">
                    詳細
                    </a> --}}

                    @if ($attendance)
                    {{-- attendance がある日は既存詳細へ --}}
                    <a href="{{ route('correction.store', ['id' => $attendance->id]) }}" class="detail-button">
                        詳細
                    </a>
                    @else
                    {{-- attendance がない日は新規修正申請へ --}}
                    <a href="{{ route('correction.createNew', ['date' => $day]) }}" class="detail-button">
                        詳細
                    </a>
                    @endif
                </td>
            </tr>

            @endforeach

        </table>

    </div>

</div>
@endsection