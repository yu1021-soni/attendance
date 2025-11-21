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
                <a href="/" class="header__logo-link">
                    <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH" class="header__logo">
                </a>

                {{-- login,register, mailhog ページではロゴだけ --}}
                @if (!Route::is('register') && !Route::is('mailhog') && !Route::is('login') && !Route::is('verification.*') && !Route::is('admin.login'))


                <nav class="header__nav">
                    <ul class="header__menu">
                        <li>
                            <a href="{{ route('attendance.index') }}" class="header__logout">勤怠一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('correction.create') }}" class="header__logout">申請</a>
                        </li>
                        <li>
                            @if(Auth::check())
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('staff.index') }}" class=" header__logout">スタッフ一覧</a>
                            {{-- 管理者用ログアウト --}}
                            <form method="post" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="header__logout">ログアウト</button>
                            </form>
                            @else
                            {{-- 一般ユーザー用ログアウト --}}
                            <form method="post" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="header__logout">ログアウト</button>
                            </form>
                            @endif
                            @else
                            <form action="{{ route('login') }}" method="get">
                                <button type="submit" class="header__login">ログイン</button>
                            </form>
                            @endif
                        </li>
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