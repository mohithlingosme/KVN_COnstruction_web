# Changelog

## Unreleased

- Protected `destroySession`, `sendOtpSms`, and `sendOtpEmail` from duplicate declarations when test doubles are loaded.
- Fixed the dashboard to reuse its injected database connection instead of opening a second production connection.
- Made rate-limit increments accept the public action-only call form used by authentication and public forms.
- Aligned estimator package feature reads with the `features` column and estimator lead storage with `plot_area`.
- Made the cross-platform API test harness pass JSON safely on Windows and supply SQLite's `NOW()` test function.
- Restored visible test-runner output; `php tests/run.php` now completes with 26 passing tests.
- Added a production image `Dockerfile` compatible with the Apache/PHP service configuration.
- Added administrator and client handoff guides.
- Added an opt-in, loopback-only admin-auth bypass for local testing. Enable it
  only through `ADMIN_AUTH_BYPASS_FOR_TESTING=true` in the local server environment.
