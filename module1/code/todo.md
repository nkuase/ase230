# Module 1 code TODO

현재 검토 범위는 `1_First_PHP_Server`이다.

## 배포 준비 완료

- [x] `index.php`에 strict types와 UTF-8 일반 텍스트 응답 헤더를 적용했다.
- [x] `Student.php`의 속성, 생성자, `greet()`, `toArray()`를 강의 내용과 맞췄다.
- [x] `student_test.php`에서 메서드 호출, public property 접근, 객체→배열→JSON 변환을 모두 실행한다.
- [x] `student_test.php`가 유효한 JSON과 올바른 `Content-Type`을 반환한다.
- [x] `Student.php` include에 실행 위치와 무관한 `require_once __DIR__`를 사용한다.
- [x] `test.php`는 Xdebug가 없어도 치명적 오류를 내지 않는다.
- [x] `test.php`에 개발 전용 페이지라는 보안 경고를 표시했다.
- [x] PHP 전용 파일에서 닫는 태그를 생략하고 코드 스타일을 정리했다.

## 남은 작업

현재 `1_First_PHP_Server` 코드 배포를 막는 알려진 작업은 없다.

## 범위 밖

`src` 강의 원본과 `2_Connect_PHP_with_MySQL` 이후 단원은 이번 변경 대상이 아니다.
