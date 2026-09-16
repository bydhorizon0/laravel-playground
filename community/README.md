# 💬 Laravel + Blade 커뮤니티 프로젝트

> **Laravel 프레임워크와 Blade 템플릿 엔진의 핵심 개념 및 실무 패턴을 학습하기 위한 커뮤니티 게시판 애플리케이션입니다.**  
> MVC 아키텍처, Eloquent ORM 쿼리 최적화, 계층형 댓글, 캐싱 전략, 인증 및 인가(Policy), 공통 레이아웃 설계를 단계별로 구현하고 학습했습니다.

---

## 📌 목차
- [프로젝트 개요](#-프로젝트-개요)
- [주요 기능 및 학습 포인트](#-주요-기능-및-학습-포인트)
  - [1. Blade 템플릿 엔진 및 공통 레이아웃](#1-blade-템플릿-엔진-및-공통-레이아웃)
  - [2. Eloquent ORM 및 쿼리 최적화](#2-eloquent-orm-및-쿼리-최적화)
  - [3. 대댓글(Nested Replies) 및 소프트 삭제](#3-대댓글nested-replies-및-소프트-삭제)
  - [4. 캐싱 전략 및 인기글 집계 서비스](#4-캐싱-전략-및-인기글-집계-서비스)
  - [5. 인증(Auth) 및 인가(Policy) 보안](#5-인증auth-및-인가policy-보안)
- [기술 스택](#-기술-스택)
- [디렉터리 구조](#-디렉터리-구조)
- [시작하기 (Getting Started)](#-시작하기-getting-started)
- [테스트 및 코드 품질](#-테스트-및-코드-품질)

---

## 📖 프로젝트 개요

- **목적**: Laravel 13과 Blade 템플릿 엔진을 활용한 실전 웹 애플리케이션 개발 역량 강화
- **형태**: 모던 반응형 커뮤니티 게시판 (게시글 CRUD, 검색, 페이징, 좋아요, 계층형 댓글, 카테고리/태그, 인기글 추천)
- **특징**: 단순 CRUD 구현을 넘어 N+1 문제 해결, Redis/Cache 기반 성능 최적화, Route Scoped Binding, Policy 기반 보안 등을 체계적으로 적용

---

## 🎯 주요 기능 및 학습 포인트

### 1. Blade 템플릿 엔진 및 공통 레이아웃
- **공통 레이아웃 설계 (`resources/views/layouts/app.blade.php`)**:
  - 반복되는 HTML 보일러플레이트를 제거하고, `@extends('layouts.app')` 및 `@section('content')` 기반의 템플릿 상속 구조 구축
  - 전통적 상속(`@yield('content')`)과 모던 컴포넌트 슬롯(`$slot`) 방식을 유연하게 지원
  - `@hasSection('title')`을 통한 동적 브라우저 타이틀 관리
- **반응형 네비게이션 & UI**:
  - Tailwind CSS v4를 활용한 미니멀하고 직관적인 인터페이스
  - `@auth` / `@guest` 지시어를 통한 인증 상태별 동적 네비게이션 바 (로그인/회원가입 vs 사용자명/새 글 작성/로그아웃)
  - 세션 플래시 메시지(`session('success')`, `session('error')`) 일괄 표시 배너
- **폼 처리 및 유효성 검사**:
  - `@csrf` 토큰 및 `@method('PUT')`, `@method('DELETE')` 메서드 스푸핑
  - `@error('field')`와 `old('field')`를 결합한 실패 시 입력값 유지 및 피드백 렌더링

### 2. Eloquent ORM 및 쿼리 최적화
- **N+1 문제 해결 (Eager Loading)**:
  - 게시글 목록 조회 시 연관된 작성자(`user`), 카테고리(`category`), 해시태그(`tags`)를 사전 로딩:
    ```php
    Post::query()->with(['user', 'category', 'tags'])
    ```
- **서브쿼리 존재 여부 최적화 (`withExists`)**:
  - 로그인한 사용자의 좋아요 여부를 각 게시글마다 별도 쿼리로 조회하지 않고, `withExists(['likes as is_liked' => ...])` 서브쿼리로 한 번에 처리
- **카운터 캐시 컬럼 활용**:
  - `view_count`, `like_count`, `comment_count`를 테이블 컬럼으로 관리하여 매 목록 조회 시 무거운 COUNT 집계 쿼리 방지
- **검색 쿼리 파라미터 그룹화**:
  - `when()` 조건문 및 클로저를 활용해 OR 조건이 다른 WHERE 조건에 영향을 주지 않도록 파라미터 그룹화:
    ```php
    $query->when($request->filled('search'), function ($query) use ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    });
    ```
- **페이지네이션 쿼리스트링 보존**:
  - `paginate()->withQueryString()`을 적용하여 검색 조건과 필터가 다음 페이지 이동 시에도 유지되도록 처리

### 3. 대댓글(Nested Replies) 및 소프트 삭제
- **1-Depth 계층형 대댓글 구조**:
  - `comments` 테이블의 `parent_id` 자기 참조 외래키를 활용해 댓글과 답글 관계 표현
- **소프트 삭제(SoftDeletes)와 문맥 유지**:
  - 원 댓글이 삭제되어도 하위 답글의 흐름이 끊기지 않도록 `withTrashed()`로 조회하고, UI에서 `"삭제된 댓글입니다."`로 마스킹 표시
- **Scoped Route Model Binding (`scopeBindings`)**:
  - 중첩 라우트 `/posts/{post}/comments/{comment}`에서 해당 댓글이 실제로 해당 게시글의 자식인지 자동 검증하여 타 게시글 리소스 변조 방지:
    ```php
    Route::delete('/posts/{post}/comments/{comment}', 'destroy')->scopeBindings();
    ```

### 4. 캐싱 전략 및 인기글 집계 서비스
- **카테고리 및 태그 목록 캐싱**:
  - 갱신이 드문 카테고리와 태그 목록을 `Cache::remember()`로 캐싱
  - 모델 이벤트(`booted()`)의 `saved`, `deleted` 훅을 통해 변경 시 자동으로 캐시 무효화(`Cache::forget()`)
- **안전하고 가벼운 순수 배열 캐싱**:
  - 모델 인스턴스 직렬화 시 발생할 수 있는 메모리 낭비 및 역직렬화 보안 위험을 방지하기 위해 필요한 컬럼만 `toArray()`로 변환하여 캐싱
- **가중치 기반 인기글 집계 서비스 (`TrendingPostService`)**:
  - 단순 어뷰징(새로고침)에 취약한 조회수 대신 가중치 알고리즘 적용:
    - **실시간 인기글 (5분 캐시)**: 최근 24시간 내 `(좋아요 * 5) + (댓글수 * 3) + 조회수` TOP 10
    - **주간 베스트 (30분 캐시)**: 최근 7일 내 `(좋아요 * 3) + (댓글수 * 2) + 조회수` TOP 10

### 5. 인증(Auth) 및 인가(Policy) 보안
- **세션 보안**:
  - 로그인 성공 시 `$request->session()->regenerate()`를 호출하여 세션 고정(Session Fixation) 공격 방어
- **정책(Policy) 기반 인가 (`PostPolicy`, `CommentPolicy`)**:
  - 컨트롤러에서 `$this->authorize('update', $post)`를 통해 본인 작성 글/댓글만 수정·삭제 허용
  - Blade 뷰에서 `@can('update', $post)` 지시어를 통해 권한에 따른 버튼 노출 제어
- **PHP 8 Attribute 미들웨어**:
  - `#[Middleware('auth', only: ['logout'])]` 문법을 컨트롤러 클래스에 선언적으로 적용

---

## 🛠 기술 스택

| 구분 | 기술 |
|---|---|
| **Language** | PHP 8.5+ |
| **Framework** | Laravel 13.x |
| **View Template** | Blade Engine |
| **Styling & Assets** | Tailwind CSS v4, Vite 8 |
| **Database** | SQLite / MySQL |
| **Cache / Queue** | Redis, Array Cache |
| **Testing** | PHPUnit 12.x |
| **Code Style** | Laravel Pint |

---

## 📂 디렉터리 구조

```
community/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # 비즈니스 로직 제어 (Post, Comment, Auth, PostLike)
│   │   └── Requests/             # FormRequest 유효성 검증 클래스 (Auth, Post, Comment)
│   ├── Models/                   # Eloquent 모델 (User, Post, Comment, Category, Tag 등)
│   ├── Policies/                 # 리소스별 인가 정책 (PostPolicy, CommentPolicy)
│   └── Services/                 # 도메인 서비스 (TrendingPostService)
├── database/
│   ├── factories/                # 테스트 데이터 팩토리
│   ├── migrations/               # 데이터베이스 스키마 마이그레이션 파일
│   └── seeders/                  # 기초 카테고리, 사용자, 게시글 시더
├── resources/
│   ├── css/                      # Tailwind CSS v4 설정 및 스타일
│   ├── js/                       # Vite 엔트리포인트 스크립트
│   └── views/                    # Blade 템플릿
│       ├── layouts/
│       │   └── app.blade.php     # 🌟 공통 레이아웃 템플릿
│       ├── auth/
│       │   ├── login.blade.php   # 로그인 뷰
│       │   └── register.blade.php# 회원가입 뷰
│       ├── posts/
│       │   ├── index.blade.php   # 게시글 목록 & 인기글 뷰
│       │   ├── show.blade.php    # 게시글 상세 & 댓글 뷰
│       │   ├── create.blade.php  # 게시글 작성 뷰
│       │   └── edit.blade.php    # 게시글 수정 뷰
│       └── welcome.blade.php     # 커뮤니티 메인 소개 뷰
├── routes/
│   └── web.php                   # 웹 애플리케이션 라우트 정의
└── tests/
    └── Feature/                  # 주요 기능 통합 테스트 (Post, Cache, Auth 등)
```

---

## 🚀 시작하기 (Getting Started)

### 1. 요구사항
- PHP >= 8.5
- Composer
- Node.js & npm
- SQLite (또는 MySQL)

### 2. 설치 및 환경설정
```bash
# 1. 저장소 클론 (또는 프로젝트 디렉터리 이동)
cd community

# 2. PHP 의존성 패키지 설치
composer install

# 3. 환경설정 파일 생성 및 앱 키 생성
cp .env.example .env
php artisan key:generate

# 4. 프론트엔드 의존성 패키지 설치
npm install
```

### 3. 데이터베이스 초기화 및 시딩
```bash
# SQLite DB 파일 생성 (필요 시)
touch database/database.sqlite

# 마이그레이션 실행 및 초기 데이터(카테고리, 유저, 게시글) 시딩
php artisan migrate --seed
```

### 4. 로컬 개발 서버 실행
```bash
# Vite 에셋 빌드
npm run build

# Laravel 로컬 웹 서버 구동
php artisan serve
```
> 브라우저에서 `http://127.0.0.1:8000`에 접속하여 확인할 수 있습니다.  
> 실시간 프론트엔드 HMR 개발 모드를 사용할 경우 `npm run dev`를 별도 터미널에서 함께 실행합니다.

---

## 🧪 테스트 및 코드 품질

### 테스트 실행 (PHPUnit)
애플리케이션의 주요 라우트, 뷰 렌더링, 캐시 무효화 및 CRUD 동작은 단위/기능 테스트로 검증됩니다.

```bash
# 전체 테스트 실행
php artisan test

# 또는 PHPUnit 직접 실행
vendor/bin/phpunit
```

### 코드 스타일 검사 및 포맷팅 (Laravel Pint)
PSR-12 및 Laravel 표준 코드 스타일을 준수합니다.

```bash
# 코드 스타일 자동 교정
vendor/bin/pint
```
