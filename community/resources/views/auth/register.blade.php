<form action="{{ route('register.submit') }}" method="POST">
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
        <label for="name">이름</label>
        <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="이름"
        >

        @error('name')
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

    <div>
        <label for="password_confirmation">비밀번호 재입력</label>
        <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="비밀번호 확인"
        >

        @error('password_confirmation')
        <p>{{ $message }}</p>
        @enderror
    </div>

    <button type="submit">가입</button>
</form>
