@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
<link rel="stylesheet" href="{{ asset('css/auth/register.css')}}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
@endsection

@section('content')
<div class="register">

    <form action="/register" class="register__form" method="post">
        @csrf

        <div class="register__heading">
            <h2 class="register__title">会員登録</h2>
        </div>

        <div class="register__group">
            <div class="register__label-wrap">
                <span class="register__label">名前</span>
            </div>
            <div class="register__control">
                <div class="register__input">
                    <input type="text" name="name">
                </div>
                <div class="register__error">
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="register__group">
            <div class="register__label-wrap">
                <span class="register__label">メールアドレス</span>
            </div>
            <div class="register__control">
                <div class="register__input">
                    <input type="text" name="email">
                </div>
                <div class="register__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="register__group">
            <div class="register__label-wrap">
                <span class="register__label">パスワード</span>
            </div>
            <div class="register__control">
                <div class="register__input">
                    <input type="password" name="password">
                </div>
                <div class="register__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="register__group">
            <div class="register__label-wrap">
                <span class="register__label">パスワード確認</span>
            </div>
            <div class="register__control">
                <div class="register__input">
                    <input type="password" name="password_confirmation">
                </div>
            </div>
        </div>

        <div class="register__actions">
            <button class="register__submit" type="submit">登録する</button>
        </div>

        <a class="register__link" href="/user_login">
            ログインはこちら
        </a>

    </form>

</div>
@endsection