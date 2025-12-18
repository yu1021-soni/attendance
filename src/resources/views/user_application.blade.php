@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/user_application.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
@endsection

@php
use App\Models\Correction;
@endphp

@section('content')
<div class="content">

    <div class="content__title">
        申請一覧
    </div>


    <div class="application__tabs">
        {{-- 承認待ちタブ（?tab が approved 以外のとき） --}}
        <a href="{{ route('correction.create', $searchParams) }}"
            class="application__tab-link {{ request('tab') !== 'approved' ? 'is-active' : '' }}">
            承認待ち
        </a>

        {{-- 承認済みタブ --}}
        <a href="{{ route('correction.create', array_merge($searchParams, ['tab' => 'approved'])) }}"
            class="application__tab-link {{ request('tab') === 'approved' ? 'is-active' : '' }}">
            承認済み
        </a>
    </div>

    <div class="application__list">
        <table>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
            @foreach ($corrections as $correction)
            <tr>
                <td>
                    {{-- 1:申請中 2:承認済み --}}
                    @if ($correction->status === 1)
                    承認待ち
                    @elseif ($correction->status === 2)
                    承認済み
                    @endif
                </td>
                <td>{{ $correction->user->name }}</td>
                <td>{{ $correction->attendance->date->format('Y/m/d') }}</td>
                <td>{{ $correction->comment }}</td>
                <td>{{ $correction->created_at?->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('correction.show', ['id' => $correction->attendance_id]) }}" class="detail-button">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection