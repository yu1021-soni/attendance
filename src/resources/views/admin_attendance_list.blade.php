@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
@endsection

@section('content')

<div class="content">
    
    <div class="flash-success">
        {{ session('success') }}
    </div>

    <div class="content__title">
        {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}の勤怠
    </div>

    <div class="content__list">
        <div class="content__list-day">

            {{-- 月移動 --}}
            <form action="{{ route('admin.dashboard') }}" method="get" class="day-nav__form">

                {{-- 今表示している年月日も一緒に送る --}}
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="day" value="{{ $day }}">

                <button name="move" value="prev" class="day-nav__btn">← 前日</button>

                <div class="day-nav__current">
                    <img src="{{ asset('img/calendar.png') }}" class="day-nav__icon" alt="calendar">
                    <span class="day-nav__text">
                        {{ $year }}/{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ str_pad($day, 2, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <button name="move" value="next" class="day-nav__btn">翌日 →</button>

            </form>

        </div>

        <div class="attendance__content-date">

            <table>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>

                @foreach($attendances as $attendance)



                <tr>
                    <td>{{ $attendance->user->name }}</td>

                    {{-- 出勤 --}}
                    <td>{{ $attendance?->work_start ? $attendance->work_start->format('H:i') : '' }}</td>

                    {{-- 退勤 --}}
                    <td>{{ $attendance?->work_end ? $attendance->work_end->format('H:i') : '' }}</td>

                    {{-- 休憩 --}}
                    <td>
                        {{ $attendance->rest_total_human }}
                    </td>

                    {{-- 合計 --}}
                    <td>{{ $attendance?->work_time_human ?? '' }}</td>

                    {{-- 詳細 --}}
                    <td>
                        <a href="{{ route('admin.show', ['id' => $attendance->id]) }}" class="detail-button">
                            詳細
                        </a>
                    </td>
                </tr>

                @endforeach

            </table>
        </div>
    </div>
</div>
@endsection