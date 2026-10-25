# GMS QA baseline — 24 October 2026

Scope: read-only source review and unauthenticated HTTP probes before upgrade. No application or server configuration changes. This is a starting baseline, not upgrade acceptance.

## Verified checks

- PHP 8.2.13 CLI lint passed: `index.php`, `login.php`, `partials/navbar.php`, `partials/sidebar.php`, `sitedailyrecord.php`.
- `http://localhost/gms/login.php` redirects HTTP to HTTPS (308). Following redirects returns 200, 6,981 bytes; one sample total 0.428 s including redirect/TLS.
- `https://localhost/gms/index.php` returns 404 without authentication. An earlier HTTP probe following redirects also returned 404, total 2.446 s. The cause is unconfirmed; these timings do not measure the authenticated dashboard or database queries.
- The checkout path reports a target at `D:/Wnmp/nginx-bins/default/www/gms`; the inspected nginx configuration uses that docroot. Dashboard and login files exist there. No server reload or configuration edit was performed.

## Upgrade priorities from source

| Priority | Evidence | Required verification |
| --- | --- | --- |
| High | `index.php:55–74` performs five separate counts over `daily_records`; `:55` wraps `verified_on` in `DATE()`. `:80–94` joins historical records for zone statistics. | Measure authenticated SQL timings and EXPLAIN before choosing aggregation/index changes; compare all totals against the old implementation. |
| High | `index.php:186–217` generates dashboard sparklines using `mt_rand()`. | Remove simulated trends or replace with real dated aggregates; totals must remain unchanged. |
| High | `git ls-files includes/x.php apps/x.php` lists both paths. Contents were not printed. | Backend owner to review tracked configuration for secrets and plan removal/rotation as required. |
| Medium | `index.php:244–253` loads multiple icon libraries and CSS; `:859–869` loads chart, calendar, date-range and template scripts. Charts have actual callers at `:916` and `:952`. | Audit DOM, shared partials and script dependencies before removing anything; retest dropdowns, mobile menu and charts. |
| Medium | Local file sizes: base CSS 860,537 bytes; `gms.png` and `favicon.png` each 531,144 bytes; login background 921,138 bytes. | Measure actual encoded transfer/cache behavior; optimize image dimensions/formats and remove unused styles only with visual checks. Disk size is not network transfer size. |
| Medium | `modern-dashboard.css:7` imports external fonts; `sitedailyrecord.php:7` loads the Tailwind browser runtime from a CDN. | Test slow/offline dependencies and printed form fidelity before replacing with static assets. |

## Acceptance still required

- Authenticated baseline and after-change measurements on the same dataset/device: document TTFB, transferred bytes, request count and navigation responsiveness for dashboard, diary, contract details, maps and PDF.
- Test desktop/mobile navigation, focus and keyboard access, table overflow, sidebar position during scrolling, and browser console errors.
- Compare dashboard metrics and filtered lists; exercise empty/error states without fabricating operational rows.
- Preview official daily-record print layout and generated PDF, including page breaks and attachments.
- Backend owner to verify login/RBAC/CSRF/upload protections and API compatibility; preserve routes, selectors and existing data.

No browser, authenticated session, SQL EXPLAIN, database write, cross-browser run or PDF generation was performed in this baseline. No performance improvement is claimed.

## Runtime investigation follow-up

The dashboard requests at 21:25:04 and 21:25:15 on 24 October match errors in `D:/Wnmp/nginx/logs/localhost_https_error.log`: PHP throws `mysqli_sql_exception` at `includes/connection.php:11` because the server requests the unsupported authentication method `auth_gssapi_client`. This establishes a database connection failure behind the observed dashboard response. The exact nginx error-status mapping was not investigated further.

`includes/connection.php` connects before checking the session, so anonymous dashboard requests also reach this failure. Login rendering does not require this connection and therefore returning 200 does not prove login processing works. Backend owner was informed; no database account, configuration, or application file was changed by QA. Rerun anonymous redirect and real login checks after the connection fix.

## Regression during implementation

- Added `tools/qa-smoke.ps1`: six anonymous GET assertions with redirects disabled and no cookies/credentials, runnable with `powershell -NoProfile -File tools/qa-smoke.ps1`. All six passed after the backend owner's auth-before-DB change: login/CSS/logo 200; dashboard/diary/contracts 302 to login. These checks do not establish database connectivity or authenticated behavior.
- Chrome displayed login at desktop size and at 390×844. Mobile document width/height matched viewport (390×844), with no overflow. Captured console warnings/errors were empty on login.
- QA added `aria-label` to the username and password inputs in `login.php`; after reload Chrome's accessibility tree exposes both names. PHP lint and targeted whitespace check passed.
- Submitting the browser's existing autofilled login reached `includes/login_action.php` and displayed nginx 404. No credentials were read or printed. Authenticated workflow remains blocked pending backend runtime repair.
- The temporary mobile viewport override was reset. No map, authenticated dashboard, PDF or cross-browser acceptance has been completed.
- After the diary owner's validation change, reran all six smoke checks: 6/6 passed and zero new nginx error-log lines appeared during the run. `daily_diary.php`, `index.php`, `partials/navbar.php` and `partials/sidebar.php` passed PHP lint and targeted whitespace checks. The anonymous diary URL without `id` now redirects without the earlier undefined-key warning. Authenticated invalid/missing-ID branches are still untested because login is blocked by DB authentication.
- Static review of the updated dashboard confirms the random sparkline generator is removed and the verification/fund canvases have accessible labels. This is source verification; authenticated rendering remains unverified.

## Authenticated UI checks — 25 October 2026

The existing Chrome session now opens dashboard data. This supersedes the earlier runtime blocker for that session; a fresh login was not repeated and no database configuration change is attributed to QA.

- Dashboard, zone list and contract list rendered in Chrome. Dashboard mobile menu opened/closed and updated `aria-expanded`; viewport 390×844 had no document horizontal overflow. Contract table has an `overflow-x:auto` parent for its wider content.
- Reproduced desktop sidebar failure: at viewport 1920×855, Ctrl+End produced `scrollY=2231`, sidebar top `-2183`, computed position `static`; the navbar stayed at top 0. Reported to the UI owner before editing shared CSS.
- Dashboard console reported Google Maps KmlLayer unavailable in API 3.66; satellite tiles rendered, but KMZ overlay acceptance failed. Reported to the map/UI owners. No API key is recorded here.
- Dashboard search typed `ABRAR` correctly left one visible row. Filling/pasting the same value left all seven rows because the source listens only to `keyup`; reported to the UI owner. Zone and contract browser titles also display mojibake around their dash separator.
- Fixed missing `K` suffix in the contract-value KPI in `contract_list.php`. Browser now shows `RM 2,393.37K`; table sum was RM 2,393,367.72, within the RM 5 rounding tolerance for two decimals in thousands. The browser comparison returned `pass:true`; the KPI did not overflow at mobile width.
- Removed a subsequently added unconditional certificate-validation bypass from `tools/qa-smoke.ps1`. All 11 expanded anonymous checks passed using normal TLS validation.

No create/update/delete action, PDF generation, fresh login, Firefox/Edge run or before/after performance benchmark was performed in this pass. Open sidebar/map/search defects prevent full UI sign-off.

Contract detail navigation from the list also rendered (`contract_details.php?id=1`), including diary/location content in the DOM. Current-page console errors identify Chartist attempting `querySelectorAll` on null and `assets/js/dashboard.js:418` reading `classList` on null. The detail title and visible heading contain mojibake. The page loads template dashboard scripts even though their expected widgets are absent; reported for cleanup. No location/upload/edit/delete controls were submitted.

## Sidebar correction and regression

With CSS ownership transferred by codex-1, QA changed the desktop sidebar to fixed positioning below the 70px navbar, constrained it to the viewport with internal vertical scrolling, and offset the main panel by the existing 250px sidebar width. Mobile styling remains outside this desktop media query.

Browser checks passed:

- Dashboard desktop: main panel starts at x=250; sidebar spans y=70 to viewport bottom. At page scrollY=2203, sidebar top remains 70.
- Short desktop (1280×420): sidebar client height 350, scroll height 569; real sidebar scroll reached scrollTop=219.
- Mobile (390×844): open menu spans x=134–384; main panel starts at x=0; toggle reports true on open and false on close.
- Zone list: at scrollY=1310, sidebar top remains 70 and position is fixed.

Cross-module testing exposed cached old CSS on pages without a versioned URL. Reused the dashboard's existing `filemtime(__DIR__ . '/assets/css/modern-dashboard.css')` query parameter on these 22 root-page stylesheet links only:

`add_zone.php`, `assign_dbku.php`, `assign_contractor.php`, `add_subzone.php`, `contractor_details.php`, `daily_diary.php`, `contractor_list.php`, `daily_details.php`, `contract_list.php`, `contract_details.php`, `new_contract.php`, `maps.php`, `new_contractor.php`, `sitedaily_attachment.php`, `sitedaily_records.php`, `sitedaily_si.php`, `update_sz.php`, `update_zone.php`, `users_management.php`, `zone.php`, `user_edit.php`, `zone_details.php`.

All 22 pages passed PHP lint and the 11 anonymous HTTP smoke checks passed. CSS whitespace check passed. A combined working-tree whitespace check also reports whitespace in concurrent module changes outside these link edits (contract details, contractor details, site records, users and zone); no whole-tree whitespace pass is claimed. Viewport overrides were reset. Map/PDF/fresh-login/performance acceptance remains outstanding.

## Map pin regression — 25 October 2026

Tested the map owner's `v=3.58` pin and constructor guard in authenticated Chrome. PHP lint of `maps.php` passed. Map DOM confirms the requested version is 3.58, but fresh-tab console records both `RetiredVersion` and the same KmlLayer unavailable error referring to API 3.66. Satellite tiles render; zone/KMZ overlay remains unverified and this fix fails acceptance. The `typeof` guard does not prevent the constructor failure.

Official Google documentation currently lists versions 3.66/3.65/3.64/3.63 and states invalid versions use the default channel: https://developers.google.com/maps/documentation/javascript/versions . Its deprecations page states KmlLayer is no longer available and points to migration alternatives: https://developers.google.com/maps/deprecations . Evidence was sent to the map owner and Moderator; no map code was changed by QA in this regression.

## GeoJSON migration regression

The map owner's GeoJSON migration now renders green boundaries in authenticated Chrome; a real click opened the `Kampung Rampangi Perimeter` popup. No KmlLayer constructor error was recorded in this fresh tab. `maps.php` PHP lint passed and the GeoJSON asset returned HTTP 200 (1,549,693 bytes, application/octet-stream).

Acceptance still fails on geometry integrity: a PHP CLI ZIP/XML/JSON comparison found 115 original Placemarks containing 116 Polygons, versus 115 output features all typed Polygon. Placemark index 0, name `1`, has two original Polygons but output feature 0 is a single-ring Polygon. The other 114 single-polygon features preserve their original coordinate sequence and rings exactly. The converter must preserve the first record as a MultiPolygon or equivalent separate geometries. Also, the page still requests v3.58 and the fresh console still records RetiredVersion plus the existing async-loading warning. These findings were sent to the map owner and Moderator. No map/conversion code was changed by QA.

## GeoJSON correction accepted for tested scope

After the map owner's correction, an independent PHP CLI ZIP/XML/JSON check passed: all 115 feature names, 116 polygon geometries and 116 rings exactly preserve the original KMZ coordinate order. Rings are closed, have at least four positions and remain within valid longitude/latitude bounds. This supersedes the geometry failure above. `maps.php` PHP lint and its whitespace check passed.

Fresh authenticated Chrome rendered the green overlay on both standalone `maps.php` and the dashboard. The script no longer pins a version; captured console output contained no KmlLayer error or RetiredVersion warning. The only captured warning was Google's direct-loading notice recommending `loading=async`. A polygon popup click was verified in the preceding GeoJSON run; that interaction was not repeated in this correction run. Map geometry and desktop rendering pass the tested scope; mobile map interactions, loading failures, fresh login, PDF and performance benchmarks remain outside this acceptance.
