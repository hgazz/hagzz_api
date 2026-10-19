# Customer mobile catalog: Egypt and Qatar

`GET /api/mobile-catalog?country=eg` (or `qa`) returns 15 academies and 45 training programs per country. It is a public, read-only endpoint independent of login, FCM updates, live partner tables and booking/payment records. Invalid countries return 422; POST is unsupported.

The source snapshot is `resources/catalog/mobile-demo.json`, copied from the reviewed admin catalog. To refresh it after editing the admin data, copy `hagzz_admin/resources/catalog/egypt-qatar-proposals.json` to this path, rerun MobileCatalogTest and deploy API. This endpoint deliberately returns a public field allowlist, excluding lead contacts, sales messages, source notes and verification metadata.

Every academy and training has `is_demo: true` and `bookable: false`, with string catalog keys rather than live academy/training IDs. The new customer mobile screen uses its own catalog view; it never passes these items to live booking, payment, favorite or follow flows. Generic Hagzz illustrations are separate from unverified official academy logos. Missing official logos remain null.

## What users see

Customer app 1.0.19+48 adds an entry below the home slider (also available in the home error state), opening a country selector and expandable academy cards. The screen displays each academy's three programs, package prices, fictional coaches, demo locations and schedules with local time zones, plus a visible demo/no-booking notice. It shows a retry action when the API is unavailable. The old academy/training lists remain dedicated to actual operational records.

**The currently installed Play version does not know this new screen. API deployment alone cannot add a new Flutter screen to an already installed app.** Deploy API first, then publish/install the new app version. Until users update, they will not see this catalog section. Check that version code 48 exceeds every version already uploaded in Play Console; use a higher code if necessary. Validate the signing certificate against the app's existing upload certificate before submitting to Play.

## API deployment

After fetching master in the server's hagzz_api checkout, build and restart the `api` compose service from `/home/hagzz/hagzz/infra`. Then refresh routes:

```bash
docker compose build api
docker compose up -d --no-deps api
docker compose exec -T -w /var/www/html api php artisan route:cache
```

No database migration or seed is required for this feature. APP_URL must point to the public HTTPS API origin so illustration URLs resolve. Existing brand image files under public/media/hagzz/brand-v1 must be present and served as images. Verify the JSON endpoint for both countries externally before releasing the app.

## Validation

API: MobileCatalogTest verifies the actual HTTP endpoint for guests, 15/45 counts per country, currency, no booking IDs or database queries, demo flags, and invalid-country/read-only handling. Existing banner/image/install tests remain applicable.

Customer Flutter: mobile_catalog_test verifies country switching, EGP/QAR prices, coach display, no checkout controls and error retry. The new screen is independent of legacy TrainingModel IDs. A successful debug APK build verifies compilation. Production deployment, Google Play submission and physical-device smoke testing are separate steps and must not be claimed from local tests alone.
