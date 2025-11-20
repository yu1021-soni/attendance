@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/admin_attendance_show.css')}}">
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

    <form action="{{ route('admin.correction', ['id' => $attendance->id]) }}" method="post">
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
                            name="work_start"
                            value="{{ old('work_start', $attendance->work_start?->format('H:i')) }}">
                        <span class="time-separator">〜</span>
                        <input type="time"
                            name="work_end"
                            value="{{ old('work_end', $attendance->work_end?->format('H:i')) }}">

                        {{-- 出勤・退勤のエラー --}}
                        @error('work_start')
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                        @error('work_end')
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>

                {{-- 既存の休憩を回数分出す --}}
                @foreach(($attendance->rests ?? collect()) as $restNo => $rest)
                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="rest-row">
                            <input type="time"
                                name="rests[{{ $restNo }}][rest_start]"
                                value="{{ old("rests.$restNo.rest_start", $rest->rest_start?->format('H:i')) }}">
                            <span class="time-separator">〜</span>
                            <input type="time"
                                name="rests[{{ $restNo }}][rest_end]"
                                value="{{ old("rests.$restNo.rest_end", $rest->rest_end?->format('H:i')) }}">

                            {{-- 更新用に既存レコードIDを送る --}}
                            <input type="hidden"
                                name="rests[{{ $restNo }}][id]"
                                value="{{ $rest->id }}">
                        </div>

                        {{-- 休憩のエラー --}}
                        @error("rests.$restNo.rest_start")
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                        @error("rests.$restNo.rest_end")
                        <p class="field-error">{{ $message }}</p>
                        @enderror
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
                                value="{{ old("rests.$nextRestNo.rest_start") }}">
                            <span class="time-separator">〜</span>
                            <input type="time"
                                name="rests[{{ $nextRestNo }}][rest_end]"
                                value="{{ old("rests.$nextRestNo.rest_end") }}">
                        </div>

                        @error("rests.$nextRestNo.rest_start")
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                        @error("rests.$nextRestNo.rest_end")
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>

                <tr class="remarks-row">
                    <th>備考</th>
                    <td>
                        <textarea name="comment">{{ old('comment') }}</textarea>

                        @error('comment')
                        <p class="field-error">{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            </table>
        </div>

        <div class="flash-success">
            {{ session('success') }}
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