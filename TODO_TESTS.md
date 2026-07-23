# TODO - Tests for OTP, Admin Panel, API & DB Communication

## OTP login unit tests
- [x] Create isolated PHP test harness (`tests/bootstrap.php`, `tests/run.php`).
- [x] Add initial OTP unit test coverage for controller early-return paths (`tests/AuthOtpTest.php`).
- [ ] Extend tests to cover:
  - [ ] rate-limit behavior (requires stubbing real helpers used in AuthController)
  - [ ] verifyPhoneOtp happy-path (requires DB/user_otps fixture)
  - [ ] OTP expiry / attempt limit / marking `is_used`.

## API Estimator tests
- [ ] Add API endpoint tests for:
  - [ ] `GET action=packages`
  - [ ] `POST action=calculate`
  - [ ] `POST action=lead`
  - [ ] invalid CSRF / rate-limit responses

## Admin panel tests
- [ ] Add minimal tests for admin auth controller + dashboard data queries.

## Test execution
- [ ] Run `php tests/run.php` and ensure zero failures.

