# Refactor Report

Date: 2026-07-23

## Completed

- Deleted 59 confirmed-unused, empty MVC scaffold files. No active source references were found.
- Fixed nested controller routing in `core/Router.php`, preventing false 404s when the legacy router resolves a controller below `app/controllers/`.
- Fixed first-run Docker database provisioning by mounting the real schema and migrations from `database/migration/` in dependency order.
- Removed the obsolete Docker Compose `version` field.
- Rebuilt public CSS and JavaScript assets successfully.

## Validation

- All PHP files parse under PHP 8.2.12.
- All 26 existing tests pass.
- NPM production dependency audit reports no vulnerabilities.
- Docker Compose configuration validates.

## Files Moved

None. The project is already organized around a public web root, with shared PHP code outside it. Moving the large set of file-backed routes would change URLs and risk breaking the deployed application.

## HTTP/API/Database Fixes

- 404: nested controller loading now uses the discovered nested file path.
- 500: new Docker databases now receive tables before the application starts.
- API: estimator endpoint contract remains covered by tests for 200, 400, 403, 404, 429, and 500-safe behavior.

## Security and Performance

- Existing headers, CSRF, validation, prepared statements, and rate limiting were retained.
- No unused production npm packages or production npm vulnerabilities were found.
- No behavior-changing performance rewrites were made.

## Remaining TODO

- Run an end-to-end smoke test against a fresh, disposable MySQL volume; do not delete an existing volume containing data.
- Review and separately commit the substantial pre-existing worktree changes that were present before this audit.
