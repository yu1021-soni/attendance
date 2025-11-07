@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/user_attendance.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
@endsection

@php
use App\Models\Attendance;

$status = $status ?? Attendance::STATUS_OFF;

$labels = [
Attendance::STATUS_OFF => '勤務外',
Attendance::STATUS_ON => '出勤中',
Attendance::STATUS_BREAK => '休憩中',
Attendance::STATUS_DONE => '退勤済',
];
@endphp

@section('content')
<div class="content">

    {{-- ステータス --}}
    <div class="attendance__status">
        {{ $labels[$status] }}
    </div>

    <div class="attendance__current">
        <div class="attendance__current-date">
            {{ $now->format('Y年m月d日') }}
        </div>
        <div class="attendance__current-time">
            {{ $now->format('H:i') }}
        </div>
    </div>


    <div class="attendance__actions">

        {{-- 勤務外 --}}
        @if ($status === Attendance::STATUS_OFF)
        <form method="POST" action="{{ route('work.start') }}">
            @csrf
            <button type="submit" class="attendance__button">出勤</button>
        </form>

        {{-- 出勤中 --}}
        @elseif ($status === Attendance::STATUS_ON)
        <form method="POST" action="{{ route('work.end') }}">
            @csrf
            <button type="submit" class="attendance__button">退勤</button>
        </form>
        <form method="POST" action="{{ route('break.start') }}">
            @csrf
            <button type="submit" class="attendance__rest-button">休憩入</button>
        </form>

        {{-- 休憩中 --}}
        @elseif ($status === Attendance::STATUS_BREAK)
        <form method="POST" action="{{ route('break.end') }}">
            @csrf
            <button type="submit" class="attendance__rest-button">休憩戻</button>
        </form>
        @endif
    </div>

    @if ($status === Attendance::STATUS_DONE)
    <div class="message">お疲れ様でした。</div>
    @endif

</div>
@endsection