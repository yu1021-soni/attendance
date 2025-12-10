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

    // この月の1日と末日
    $startOfMonth = Carbon::create($year, $month, 1);
    $endOfMonth = $startOfMonth->copy()->endOfMonth();

    // 勤怠コレクションを「日付文字列」をキーにした連想配列に変換しておく
    // 例: '2025-12-01' => Attendanceモデル
    $attendanceMap = $attendances->keyBy(function ($item) {
    return $item->date->format('Y-m-d');
    });
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

            {{-- 1日〜末日まで1日ずつループ --}}
            @for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay())
            @php
            $dateKey = $date->format('Y-m-d');
            // その日の勤怠（あればモデル、なければ null）
            $attendance = $attendanceMap->get($dateKey);
            @endphp

            <tr>
                <td>
                    <span class="date-monthday">{{ $date->format('n/j') }}</span>
                    <span>({{ $weekNames[$date->dayOfWeek] }})</span>
                </td>

                {{-- 出勤 --}}
                <td>
                    @if($attendance?->work_start)
                    {{ $attendance->work_start->format('H:i') }}
                    @endif
                </td>

                {{-- 退勤 --}}
                <td>
                    @if($attendance?->work_end)
                    {{ $attendance->work_end->format('H:i') }}
                    @endif
                </td>

                {{-- 休憩合計 --}}
                <td>
                    @if($attendance)
                    {{ $attendance->rest_total_human }}
                    @endif
                </td>

                {{-- 勤務合計 --}}
                <td>
                    @if($attendance)
                    {{ $attendance->work_time_human }}
                    @endif
                </td>

                <td>
                    @if ($attendance)
                    {{-- 勤怠がある日は既存詳細へ --}}
                    <a href="{{ route('admin.show', $attendance->id) }}" class="detail__button">
                        詳細
                    </a>
                    @else
                    {{-- attendance がない日は新規修正申請へ --}}
                    <a href="{{ route('admin.createNew', [
                'user_id' => $user->id,
                'date'    => $dateKey,
            ]) }}" class="detail__button">
                        詳細
                    </a>
                    @endif
                </td>
            </tr>
            @endfor
        </table>
    </div>

    <form action="{{ route('attendances.export') }}" method="get" class="cfv__wrapper">
        <button class="cfv__button">CFV出力</button>
    </form>
</div>
@endsection