@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/user_attendance.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="content">

    {{-- ステータス --}}
    <div class="attendance__status">
        @if ($status === 0)
        勤務外
        @elseif ($status === 1)
        出勤中
        @elseif ($status === 2)
        休憩中
        @else
        退勤済
        @endif
    </div>

    <div class="attendance__current">
        <div class="attendance__current-date">
            {{ $now->format('Y年m月d日') }}
        </div>
        <div class="attendance__current-time">
            {{ $now->format('H:i') }}
        </div>
    </div>

    {{-- 勤務外 --}}
    @if ($status === 0)
    <form method="POST" action="{{ route('work.start') }}">
        @csrf
        <button type="submit" class="attendance__button">出勤</button>
    </form>
    @endif

    {{-- 出勤中 --}}
    @if ($status === 1)
    <div class="attendance__actions">
        <form method="POST" action="{{ route('work.end') }}">
            @csrf
            <button type="submit" class="attendance__button">退勤</button>
        </form>
        <form method="POST" action="{{ route('break.start') }}">
            @csrf
            <button type="submit" class="attendance__rest-button">休憩入り</button>
        </form>
    </div>
    @endif

    {{-- 休憩中 --}}
    @if ($status === 2)
    <div class="attendance__actions">
        <form method="POST" action="{{ route('break.end') }}">
            @csrf
            <button type="submit" class="attendance__rest-button">休憩戻</button>
        </form>
    </div>
    @endif

    @if ($status === 3)
    <div class="message">お疲れ様でした。</div>
    @endif

</div>
@endsection