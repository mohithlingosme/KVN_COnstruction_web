# TODO (BlackboxAI test extensions)

## Step 1: Create OTP fixture + SQLite bootstrap utilities
- [ ] Add `tests/fixtures/otp_sqlite_fixture.php` that creates tables `users`, `user_otps` and seeds data for happy-path, expired, attempt-limit, and unused OTP states.

## Step 2: Extend Auth OTP unit tests
- [ ] Update `tests/AuthOtpTest.php` to include:
  - [ ] rate-limit behavior for `sendLoginOtp` by stubbing real helpers used in `AuthController` (`checkRateLimit`).
  - [ ] verifyPhoneOtp happy-path using seeded `user_otps` fixture.
  - [ ] verifyPhoneOtp expiry behavior.
  - [ ] verifyPhoneOtp attempt limit behavior (>=5 attempts blocks).
  - [ ] verifyPhoneOtp marks `is_used=1` on successful verification.

## Step 3: API Estimator tests
- [ ] Add `tests/ApiEstimatorTest.php` covering:
  - [ ] GET `action=packages`
  - [ ] POST `action=calculate`
  - [ ] POST `action=lead`
  - [ ] invalid CSRF
  - [ ] rate-limit response

## Step 4: Admin panel minimal tests
- [ ] Add `tests/AdminPanelTest.php` covering:
  - [ ] admin auth controller entrypoint behavior (minimal unit assertions)
  - [ ] admin dashboard data queries don’t crash and return expected keys.

## Step 5: Test execution
- [ ] Run `php tests/run.php` and fix any failures.

