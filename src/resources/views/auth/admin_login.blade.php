@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/auth/user_login.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="login">

    <form action="/login" class="login__form" method="post">
        @csrf

        <div class="login__heading">
            <h2 class="login__title">管理者ログイン</h2>
        </div>

        <div class="login__group">
            <div class="login__label-wrap">
                <span class="login__label">メールアドレス</span>
            </div>
            <div class="login__control">
                <div class="login__input">
                    <input type="text" name="email">
                </div>
                <div class="login__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="login__group">
            <div class="login__label-wrap">
                <span class="login__label">パスワード</span>
            </div>
            <div class="login__control">
                <div class="login__input">
                    <input type="password" name="password">
                </div>
                <div class="login__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="login__actions">
            <button class="login__submit" type="submit">管理者ログインする</button>
        </div>

    </form>

</div>
@endsection