<header class="header">
    <div class="header__logo">
        <a href="/admin/attendance/list"><img src="{{ asset('img/logo.png') }}" alt="ロゴ"></a>
    </div>
    @if( !in_array(Route::currentRouteName(), ['admin.login', 'admin.verification.notice']) )
    <nav class="header__nav">
        <ul>
            <li><a href="/admin/attendance/list">勤怠一覧</a></li>
            <li><a href="/admin/staff/list">スタッフ一覧</a></li>
            <li><a href="/stamp_correction_request/list">申請</a></li>
            <li>
                <form action="/admin/logout" method="post">
                    @csrf
                    <button class="header__logout">ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
    @endif
</header>