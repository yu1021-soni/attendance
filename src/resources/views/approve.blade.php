@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/approve.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
@endsection

@php
use App\Models\Correction;
@endphp

@section('content')
<div class="attendance">

    <div class="attendance__title">
        勤怠詳細
    </div>

    <form action="{{ route('admin.approval', ['id' => $attendance->id]) }}" method="post">
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
        <input type="hidden" name="date" value="{{ $attendance->date?->format('Y-m-d') }}">

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
                        <input type="time"
                            value="{{ $correction->new_work_start?->format('H:i') ?? $attendance->work_start?->format('H:i') }}"
                            disabled>
                        <span class="time-separator">〜</span>
                        <input type="time"
                            value="{{ $correction->new_work_end?->format('H:i') ?? $attendance->work_end?->format('H:i') }}"
                            disabled>
                    </td>
                </tr>

                {{-- ★ 承認待ち：CorrectionRest の new_* を表示（入力不可） --}}
                @foreach(($correction->rests ?? collect()) as $restNo => $rest)
                <tr>
                    <th>休憩{{ $restNo + 1 }}</th>
                    <td>
                        <div class="rest-row">
                            <input type="time"
                                value="{{ $rest->new_rest_start?->format('H:i') }}"
                                disabled>
                            <span class="time-separator">〜</span>
                            <input type="time"
                                value="{{ $rest->new_rest_end?->format('H:i') }}"
                                disabled>
                        </div>
                    </td>
                </tr>
                @endforeach


                <tr class="remarks-row">
                    <th>備考</th>
                    <td>
                        {{-- ★ 申請中：ユーザーの申請コメントを表示（編集不可） --}}
                        <textarea disabled>{{ $correction->comment }}</textarea>
                    </td>
                </tr>
            </table>
        </div>

        <div class="attendance__submit">
            
        </div>
    </form>

</div>
@endsection