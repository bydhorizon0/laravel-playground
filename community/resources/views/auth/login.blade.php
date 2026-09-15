<form action="{{ route('login.submit') }}" method="POST">
    @csrf

    <div>
        <label for="email">이메일</label>
        <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="xxx@example.com"
        >

        @error('email')
        <p>{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password">비밀번호</label>
        <input
                type="password"
                id="password"
                name="password"
                placeholder="비밀번호"
        >

        @error('password')
        <p>{{ $message }}</p>
        @enderror
    </div>

    <button type="submit">가입</button>
</form>
