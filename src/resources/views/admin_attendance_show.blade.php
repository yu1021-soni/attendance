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

// 申請中かどうか
$hasPendingCorrection = isset($correction)
&& $correction->status === Correction::STATUS_PENDING
&& $correction->user_id === $attendance->user->id;

// 勤怠レコードが「新規」かどうか
$isNewAttendance = !$attendance->exists;
@endphp

@section('content')
<div class="attendance">

    <div class="attendance__title">
        勤怠詳細
    </div>

    {{-- 新規か既存かで送信先ルートを出し分ける --}}
    @if ($isNewAttendance)
    {{-- 勤怠記録がない日：新規申請用のルートへ --}}
    <form action="{{ route('admin.newStore') }}" method="post">
        @else
        {{-- 既存の勤怠：管理者修正用のルートへ --}}
        <form action="{{ route('admin.correction', ['id' => $attendance->id]) }}" method="post">
            @endif
            @csrf

            {{-- 既存レコードの場合は id を送る / 新規の場合は null のままでもOK --}}
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

            {{-- 日付は新規・既存どちらでも必要 --}}
            <input type="hidden" name="date" value="{{ $attendance->date?->format('Y-m-d') }}">

            {{-- 新規申請のときは、どのユーザーの勤怠かも hidden で送る --}}
            @if ($isNewAttendance)
            <input type="hidden" name="user_id" value="{{ $attendance->user->id }}">
            @endif

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
                            @if ($hasPendingCorrection)
                            <input type="time"
                                value="{{ $correction->new_work_start?->format('H:i') ?? $attendance->work_start?->format('H:i') }}"
                                disabled>
                            <span class="time-separator">〜</span>
                            <input type="time"
                                value="{{ $correction->new_work_end?->format('H:i') ?? $attendance->work_end?->format('H:i') }}"
                                disabled>
                            @else
                            <input type="time"
                                name="work_start"
                                value="{{ old('work_start', $attendance->work_start?->format('H:i')) }}">
                            <span class="time-separator">〜</span>
                            <input type="time"
                                name="work_end"
                                value="{{ old('work_end', $attendance->work_end?->format('H:i')) }}">

                            @error('work_start')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                            @error('work_end')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                            @endif
                        </td>
                    </tr>

                    {{-- 休憩行 --}}
                    @if ($hasPendingCorrection)

                    {{-- 承認待ち --}}
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

                    @else

                    {{-- 申請なし or 承認後 --}}
                    @foreach(($attendance->rests ?? collect()) as $restNo => $rest)
                    <tr>
                        <th>休憩{{ $restNo + 1 }}</th>
                        <td>
                            <div class="rest-row">
                                <input type="time"
                                    name="rests[{{ $restNo }}][rest_start]"
                                    value="{{ old("rests.$restNo.rest_start", $rest->rest_start?->format('H:i')) }}">
                                <span class="time-separator">〜</span>
                                <input type="time"
                                    name="rests[{{ $restNo }}][rest_end]"
                                    value="{{ old("rests.$restNo.rest_end", $rest->rest_end?->format('H:i')) }}">

                                <input type="hidden"
                                    name="rests[{{ $restNo }}][id]"
                                    value="{{ $rest->id }}">
                            </div>

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
                    $nextRestNo = $attendance->rests->count();
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

                    @endif

                    <tr class="remarks-row">
                        <th>備考</th>
                        <td>
                            @if ($hasPendingCorrection)
                            <textarea disabled>{{ $correction->comment }}</textarea>
                            @else
                            <textarea name="comment">{{ old('comment') }}</textarea>

                            @error('comment')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="flash-success">
                {{ session('success') }}
            </div>

            <div class="attendance__submit">
                <button class="attendance__submit-button" type="submit">
                    修正
                </button>
            </div>
        </form>

</div>
@endsection