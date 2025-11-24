<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coachtech</title>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}">
    @yield('css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>

<body>
    <div class="app">
        <header class="header">
            <div class="header__inner">

                @if (auth()->check())
                {{-- ログイン済み --}}
                @if (auth()->user()->isAdmin())
                {{-- 管理者 --}}
                <a href="/admin/dashboard" class="header__logo-link">
                    <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH" class="header__logo">
                </a>
                @else
                {{-- 一般ユーザー --}}
                <a href="/" class="header__logo-link">
                    <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH" class="header__logo">
                </a>
                @endif
                @else
                {{-- 未ログイン --}}
                <a href="/" class="header__logo-link">
                    <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH" class="header__logo">
                </a>
                @endif


                {{-- login, register, mailhog, 認証系ページではロゴだけ --}}
                @if (
                !Route::is('register') &&
                !Route::is('mailhog') &&
                !Route::is('login') &&
                !Route::is('verification.*') &&
                !Route::is('admin.login')
                )
                <nav class="header__nav">
                    <ul class="header__menu">
                        @auth
                        @if (auth()->user()->isAdmin())
                        {{-- 管理者ユーザーメニュー --}}
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="header__link">勤怠一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('staff.index') }}" class="header__link">スタッフ一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('approval.index') }}" class="header__link">申請一覧</a>
                        </li>
                        <li>
                            {{-- 管理者用ログアウト --}}
                            <form method="post" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="header__logout">ログアウト</button>
                            </form>
                        </li>
                        @else
                        {{-- 一般ユーザーメニュー --}}
                        <li>
                            <a href="/" . class="header__link">勤怠</a>
                        </li>
                        <li>
                            <a href="{{ route('attendance.index') }}" class="header__link">勤怠一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('correction.create') }}" class="header__link">申請</a>
                        </li>
                        <li>
                            {{-- 一般ユーザー用ログアウト --}}
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="header__logout">ログアウト</button>
                            </form>
                        </li>
                        @endif
                        @else
                        {{-- 未ログイン --}}
                        <li>
                            <form action="{{ route('login') }}" method="get">
                                <button type="submit" class="header__login">ログイン</button>
                            </form>
                        </li>
                        @endauth
                    </ul>
                </nav>
                @endif
            </div>
        </header>

        <div class="content">
            @yield('content')
        </div>

    </div>
</body>

</html>