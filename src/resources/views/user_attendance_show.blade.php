@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/user_attendance_show.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
@endsection

@php
use App\Models\Correction;
@endphp

@section('content')
<div class="attendance">

    <div class="attendance__title">
        勤怠一覧
    </div>

    <form action="{{ route('wait.approval') }}" method="post">
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

        <div class="attendance__content">
            <table>
                <tr>
                    <th>名前</th>
                    <td>{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <span class="date-year">{{ $attendance->date->format('Y年') }}</span>
                        <span class="date-space"></span>
                        <span class="date-monthday">{{ $attendance->date->format('n月j日') }}</span>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <input type="time" name="work_start" value="{{ $attendance->work_start?->format('H:i') }}" @if($status==='pending' ) disabled @endif>
                        <span class="time-separator">〜</span>
                        <input type="time" name="work_end" value="{{ $attendance->work_end?->format('H:i') }}" @if($status==='pending' ) disabled @endif>
                    </td>
                </tr>
                {{-- 既存の休憩を回数分出す --}}
                {{-- foreach (配列 as キー => 値)のキー名はなんでもいい --}}
                @foreach ($attendance->rests as $restNo => $rest)
                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="rest-row">
                            <input type="time"
                                name="rests[{{ $restNo }}][rest_start]"
                                value="{{ $rest->rest_start?->format('H:i') }}" @if($status==='pending' ) disabled @endif>
                            <span class="time-separator">〜</span>
                            <input type="time"
                                name="rests[{{ $restNo }}][rest_end]"
                                value="{{ $rest->rest_end?->format('H:i') }}" @if($status==='pending' ) disabled @endif>

                            {{-- 更新用に既存レコードIDを送る --}}
                            <input type="hidden"
                                name="rests[{{ $restNo }}][id]"
                                value="{{ $rest->id }}">
                        </div>
                    </td>
                </tr>
                @endforeach

                {{-- 追加用の休憩 --}}
                @php
                $nextRestNo = $attendance->rests->count(); // 次の休憩番号
                @endphp

                <tr>
                    <th>休憩{{ $nextRestNo + 1 }}</th>
                    <td>
                        <div class="rest-row">
                            <input type="time"
                                name="rests[{{ $nextRestNo }}][rest_start]"
                                value="">
                            <span class="time-separator">〜</span>
                            <input type="time"
                                name="rests[{{ $nextRestNo }}][rest_end]"
                                value="">
                        </div>
                    </td>
                </tr>
                <tr class="remarks-row">
                    <th>備考</th>
                    <td>
                        <textarea name="textarea"></textarea>
                    </td>
                </tr>
            </table>
        </div>
        <div class="attendance__submit">
            @if (($status ?? null) === Correction::STATUS_PENDING)
            <p class="pending-message">*申請中のため修正はできません。</p>
            @else
            <button class="attendance__submit-button" type="submit">
                修正
            </button>
            @endif
        </div>
    </form>

</div>
@endsection