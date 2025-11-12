@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/user_attendance_show.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
@endsection


@section('content')
<div class="attendance">

    <div class="attendance__title">
        勤怠一覧
    </div>

    <div class="attendance__content">
        <table>
            <tr>
                <th>名前</th>
                <td></td>
            </tr>
            <tr>
                <th>日付</th>
                <td></td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td></td>
            </tr>
            
        </table>
    </div>
</div>
@endsection