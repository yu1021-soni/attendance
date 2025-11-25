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

$isPending = ($status ?? null) === Correction::STATUS_PENDING;
$inputDisabled = $isPending ? 'disabled' : '';

// ★ 備考に表示する値を決める
if ($isPending && isset($correction)) {
// 承認待ちのとき → 修正申請(Correction)に保存されている comment を表示
$commentValue = $correction->comment;
} else {
// それ以外（初回表示 / 再入力など）→ old優先、なければ Attendance 側の comment
$commentValue = old('comment', $attendance->comment);
}
@endphp

@section('content')
<div class="attendance">

    <div class="attendance__title">
        勤怠詳細
    </div>

    @if ($attendance->id)
    {{-- 既存レコードなら修正申請へ --}}
    <form action="{{ route('wait.approval') }}" method="post">
        @else
        {{-- 新規申請なら新規用のルートへ --}}
        <form action="{{ route('correction.newStore') }}" method="post">
            @endif
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
                            @if ($isPending)
                            {{-- 申請中：修正後の値を表示 --}}
                            <input type="time" value="{{ $afterStart }}" disabled>
                            <span class="time-separator">〜</span>
                            <input type="time" value="{{ $afterEnd }}" disabled>
                            @else
                            {{-- 申請前：Attendance の編集欄 --}}
                            <input type="time"
                                name="work_start"
                                value="{{ old('work_start', $beforeStart) }}">
                            <span class="time-separator">〜</span>
                            <input type="time"
                                name="work_end"
                                value="{{ old('work_end', $beforeEnd) }}">
                            @endif

                            {{-- 出勤・退勤のエラー --}}
                            @error('work_start')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                            @error('work_end')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>

                    @php
                    // 「申請中」かどうか（休憩の表示切り替え用）
                    $hasPendingCorrection = isset($correction)
                    && $correction->status === Correction::STATUS_PENDING;
                    @endphp

                    {{-- 休憩行 --}}
                    @if ($hasPendingCorrection)

                    {{-- 申請中：CorrectionRest の new_* を表示（入力不可） --}}
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

                    {{-- 申請前：Attendance の休憩を編集できる --}}
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

                    {{-- 追加用の休憩（申請前のときだけ出す） --}}
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

                    @endif

                    <tr class="remarks-row">
                        <th>備考</th>
                        <td>
                            <textarea name="comment" {{ $inputDisabled }}>{{ $commentValue }}</textarea>

                            @error('comment')
                            <p class="field-error">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                </table>
            </div>

            <div class="attendance__submit">
                @if ($isPending)
                <p class="pending-message">*承認待ちのため修正はできません。</p>
                @else
                <button class="attendance__submit-button" type="submit">
                    修正
                </button>
                @endif
            </div>
        </form>

</div>
@endsection