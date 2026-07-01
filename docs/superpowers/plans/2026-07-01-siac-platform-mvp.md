# SIAC Platform MVP — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan phase by phase.

**Goal:** Build a national business discovery and product showcase platform for the Salon International Interprofessionnel de l'Aquaculture du Cameroun — API-first, mobile-first, bilingual (FR/EN), no payments, no trading.

**Architecture:** Laravel 12 modular monolith. 13 modules, each owning its controllers/services/models/routes. Shared MySQL database. Sanctum auth. Meilisearch for search. API as a Product with public read endpoints.

**Tech Stack:** Laravel 12, MySQL 8, Laravel Sanctum, Spatie Permission, Laravel Scout + Meilisearch, Laravel Horizon + Redis, Laravel Reverb (messaging), intervention/image, dedoc/scramble (OpenAPI), AWS S3, Tailwind CSS v4, Lucide icons.

---

## Phase 1: Foundation & Cleanup

### Task 1.1 — Swap Passport → Sanctum, remove unused packages

**Files:**
- Modify: `composer.json`

- [ ] Remove `laravel/passport`, `bacon/bacon-qr-code`, `pragmarx/google2fa` from `require`
- [ ] Add `laravel/sanctum` to `require`

```json
"require": {
    "php": "^8.3",
    "dedoc/scramble": "^0.13.29",
    "http-interop/http-factory-guzzle": "^1.2",
    "intervention/image-laravel": "^4.0",
    "laravel/framework": "^13.8",
    "laravel/horizon": "^5.47",
    "laravel/reverb": "^1.0",
    "laravel/sanctum": "^4.0",
    "laravel/scout": "^11.3",
    "laravel/tinker": "^3.0",
    "league/flysystem-aws-s3-v3": "^3.0",
    "meilisearch/meilisearch-php": "^1.16",
    "sentry/sentry-laravel": "^4.26",
    "spatie/laravel-activitylog": "^4.0",
    "spatie/laravel-permission": "^8.0"
}
```

- [ ] Run: `composer update --no-scripts`

### Task 1.2 — Reset bootstrap/providers.php to only AppServiceProvider

**Files:**
- Modify: `bootstrap/providers.php`

- [ ] Replace entire file content:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
];
```

### Task 1.3 — Delete all old module directories

- [ ] Delete: `app/Modules/Directory/`
- [ ] Delete: `app/Modules/Trading/`
- [ ] Delete: `app/Modules/Investors/`
- [ ] Delete: `app/Modules/Payments/`
- [ ] Delete: `app/Modules/Compliance/`
- [ ] Delete: `app/Modules/Verification/`
- [ ] Delete: `app/Modules/Auth/` (will rebuild)
- [ ] Delete: `app/Modules/Notifications/` (will rebuild)
- [ ] Delete: `app/Modules/Support/` (will rebuild)
- [ ] Delete: `app/Modules/Cms/` (will rebuild)
- [ ] Delete: `app/Modules/Api/` (will rebuild)
- [ ] Delete: `app/Modules/Admin/` (will rebuild)

### Task 1.4 — Delete all old migrations

- [ ] Delete everything in `database/migrations/` EXCEPT `database/.gitignore`

### Task 1.5 — Create new module directory skeleton

Create these directories with `.gitkeep` where needed.

Modules to scaffold:
`Auth`, `Businesses`, `Products`, `Taxonomy`, `Search`, `Messaging`, `Saved`, `Notifications`, `Cms`, `Analytics`, `Api`, `Admin`, `Support`

Each module gets: `Controllers/`, `Models/`, `Services/`, `Requests/`, `Resources/`, `Routes/`, `Policies/`, `Providers/`

- [ ] Run scaffold commands (PowerShell):

```powershell
$modules = @("Auth","Businesses","Products","Taxonomy","Search","Messaging","Saved","Notifications","Cms","Analytics","Api","Admin","Support")
$subdirs = @("Controllers","Models","Services","Requests","Resources","Routes","Policies","Providers")
foreach ($m in $modules) {
    foreach ($s in $subdirs) {
        New-Item -ItemType Directory -Force "app/Modules/$m/$s" | Out-Null
    }
}
```

### Task 1.6 — Create all 66 migrations

Create migrations in exact order so foreign keys resolve correctly.

**Migration 1:** `2026_07_01_000001_create_core_tables.php`
— Spatie permissions, regions, cities, industries, sectors, product_categories, certifications, attribute_templates

**Migration 2:** `2026_07_01_000002_create_auth_tables.php`
— users, otp_verifications, user_devices, social_logins, user_sessions, push_tokens

**Migration 3:** `2026_07_01_000003_create_businesses_tables.php`
— businesses, business_social_links, business_gallery, business_documents, business_certifications, business_awards, business_tags, business_views, saved_businesses, business_contact_submissions, verification_applications, verification_documents

**Migration 4:** `2026_07_01_000004_create_products_tables.php`
— products, product_images, product_documents, product_attributes, product_videos, saved_products, product_views, product_reports

**Migration 5:** `2026_07_01_000005_create_messaging_tables.php`
— conversations, messages, message_attachments, quote_requests

**Migration 6:** `2026_07_01_000006_create_notification_tables.php`
— notification_templates, notification_logs, notification_preferences

**Migration 7:** `2026_07_01_000007_create_cms_tables.php`
— cms_pages, cms_posts, cms_faqs, cms_faq_categories, cms_announcements

**Migration 8:** `2026_07_01_000008_create_analytics_tables.php`
— search_queries, platform_events, popular_searches_cache, admin_dashboard_snapshots

**Migration 9:** `2026_07_01_000009_create_api_tables.php`
— api_consumers, api_keys, api_usage_logs, webhook_subscriptions

**Migration 10:** `2026_07_01_000010_create_support_tables.php`
— support_categories, support_tickets, support_ticket_replies, help_articles, help_categories

**Migration 11:** `2026_07_01_000011_create_admin_tables.php`
— audit_logs, system_settings, feature_flags

- [ ] Create each migration file (see Phase 1 execution for full SQL)
- [ ] Run: `php artisan migrate:fresh`
- [ ] Verify: `php artisan migrate:status` — all 11 show "Yes"

### Task 1.7 — Publish and configure Sanctum

- [ ] Run: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
- [ ] Update `config/sanctum.php` stateful domains if needed
- [ ] Update `bootstrap/app.php` middleware — add Sanctum to `api` group

### Task 1.8 — Install and configure Reverb

- [ ] Run: `php artisan reverb:install`
- [ ] Add `REVERB_*` env vars to `.env`

### Task 1.9 — Publish Spatie Permission

- [ ] Run: `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- [ ] Verify: `config/permission.php` exists

### Task 1.10 — Create Auth module (User model + Sanctum)

**Files:**
- Create: `app/Modules/Auth/Models/User.php`
- Create: `app/Modules/Auth/Providers/AuthServiceProvider.php`
- Create: `app/Modules/Auth/Routes/api.php`
- Create: `app/Modules/Auth/Controllers/RegisterController.php`
- Create: `app/Modules/Auth/Controllers/LoginController.php`
- Create: `app/Modules/Auth/Controllers/LogoutController.php`
- Create: `app/Modules/Auth/Controllers/OtpController.php`
- Create: `app/Modules/Auth/Controllers/ProfileController.php`
- Create: `app/Modules/Auth/Services/AuthService.php`
- Create: `app/Modules/Auth/Services/OtpService.php`
- Create: `app/Modules/Auth/Requests/RegisterRequest.php`
- Create: `app/Modules/Auth/Requests/LoginRequest.php`
- Create: `app/Modules/Auth/Resources/UserResource.php`
- Modify: `bootstrap/providers.php` — add AuthServiceProvider
- Modify: `config/auth.php` — set default guard to `sanctum`

User model uses `HasApiTokens` (Sanctum), `HasRoles` (Spatie), `SoftDeletes`, `HasUuids`.

- [ ] Write User model with fields: uuid, name, email, phone, password, avatar, language_preference (fr|en), is_email_verified, is_phone_verified, status, last_login_at, last_login_ip
- [ ] Write AuthService: register(), login(), logout(), me()
- [ ] Write OtpService: generate(6-digit), send(channel), verify()
- [ ] Write RegisterController, LoginController, LogoutController, OtpController, ProfileController
- [ ] Write RegisterRequest (validate name, email, phone, password)
- [ ] Write LoginRequest (validate email|phone + password)
- [ ] Write UserResource (id, name, email, phone, avatar, language_preference, roles, business)
- [ ] Write AuthServiceProvider (loadRoutesFrom)
- [ ] Write api.php routes (22 auth endpoints)
- [ ] Register provider in `bootstrap/providers.php`

- [ ] Test: `php artisan test --filter=AuthTest` — PASS
- [ ] Run: `php artisan serve` — `POST /api/v1/auth/register` returns 201 with token

---

## Phase 2: Taxonomy + Businesses

### Task 2.1 — Taxonomy module

**Files:**
- Create: `app/Modules/Taxonomy/Models/Industry.php`
- Create: `app/Modules/Taxonomy/Models/Sector.php`
- Create: `app/Modules/Taxonomy/Models/ProductCategory.php`
- Create: `app/Modules/Taxonomy/Models/AttributeTemplate.php`
- Create: `app/Modules/Taxonomy/Models/Certification.php`
- Create: `app/Modules/Taxonomy/Models/Region.php`
- Create: `app/Modules/Taxonomy/Models/City.php`
- Create: `app/Modules/Taxonomy/Controllers/TaxonomyController.php`
- Create: `app/Modules/Taxonomy/Resources/IndustryResource.php`
- Create: `app/Modules/Taxonomy/Resources/CategoryResource.php`
- Create: `app/Modules/Taxonomy/Resources/RegionResource.php`
- Create: `app/Modules/Taxonomy/Routes/api.php`
- Create: `app/Modules/Taxonomy/Providers/TaxonomyServiceProvider.php`
- Create: `database/seeders/TaxonomySeeder.php`

- [ ] Write all 7 models with relationships
- [ ] Write TaxonomyController: industries(), sectors(), categories(), regions(), certifications(), attributeTemplates()
- [ ] All endpoints public (no auth required)
- [ ] Write TaxonomySeeder — seed 10 industries, all SIAC sectors under Aquaculture, all 10 Cameroon regions, 50+ cities, all SIAC attribute templates, certifications
- [ ] Run: `php artisan db:seed --class=TaxonomySeeder`
- [ ] Test: `GET /api/v1/industries` returns 10 industries

### Task 2.2 — Businesses module

**Files:**
- Create: `app/Modules/Businesses/Models/Business.php`
- Create: `app/Modules/Businesses/Models/BusinessGallery.php`
- Create: `app/Modules/Businesses/Models/BusinessDocument.php`
- Create: `app/Modules/Businesses/Models/BusinessCertification.php`
- Create: `app/Modules/Businesses/Models/BusinessSocialLink.php`
- Create: `app/Modules/Businesses/Models/BusinessView.php`
- Create: `app/Modules/Businesses/Models/VerificationApplication.php`
- Create: `app/Modules/Businesses/Models/VerificationDocument.php`
- Create: `app/Modules/Businesses/Controllers/PublicBusinessController.php`
- Create: `app/Modules/Businesses/Controllers/MyBusinessController.php`
- Create: `app/Modules/Businesses/Controllers/BusinessGalleryController.php`
- Create: `app/Modules/Businesses/Controllers/BusinessDocumentController.php`
- Create: `app/Modules/Businesses/Controllers/VerificationController.php`
- Create: `app/Modules/Businesses/Services/BusinessService.php`
- Create: `app/Modules/Businesses/Services/VerificationService.php`
- Create: `app/Modules/Businesses/Services/ImageUploadService.php`
- Create: `app/Modules/Businesses/Requests/CreateBusinessRequest.php`
- Create: `app/Modules/Businesses/Requests/UpdateBusinessRequest.php`
- Create: `app/Modules/Businesses/Requests/VerificationApplicationRequest.php`
- Create: `app/Modules/Businesses/Resources/BusinessResource.php`
- Create: `app/Modules/Businesses/Resources/BusinessListResource.php`
- Create: `app/Modules/Businesses/Policies/BusinessPolicy.php`
- Create: `app/Modules/Businesses/Routes/api.php`
- Create: `app/Modules/Businesses/Providers/BusinessesServiceProvider.php`

Business model fields: uuid, slug (auto-generated from name), user_id, industry_id, region_id, city_id, name_fr, name_en, tagline_fr, tagline_en, description_fr, description_en, logo, cover_image, phone, whatsapp, email, website, address_fr, address_en, gps_lat, gps_lng, year_established, employee_count, ownership_type, export_countries (json), languages_spoken (json), is_featured, featured_until, verification_tier (unverified|basic|verified|certified), status (draft|published|suspended), views_count, response_time_hours.

ImageUploadService: resize to max 1200px, convert to WebP, strip EXIF, upload to S3.

- [ ] Write all 8 models with relationships and casts
- [ ] Write BusinessService: create(), update(), publish(), uploadLogo(), uploadCover()
- [ ] Write VerificationService: apply(), getRequiredDocuments(tier)
- [ ] Write ImageUploadService using intervention/image
- [ ] Write PublicBusinessController: index() with filters, show() with view tracking
- [ ] Write MyBusinessController: store(), show(), update(), uploadLogo(), uploadCover()
- [ ] Write VerificationController: show(), apply(), uploadDocument()
- [ ] Write BusinessPolicy: only business owner can edit
- [ ] Write BusinessResource (full detail) and BusinessListResource (card data only)
- [ ] Write all requests with FR+EN validation messages

- [ ] Test: `POST /api/v1/my/business` creates business, returns slug
- [ ] Test: `GET /api/v1/businesses` returns paginated list
- [ ] Test: `GET /api/v1/businesses/{slug}` returns full profile + increments views_count

---

## Phase 3: Products + Search

### Task 3.1 — Products module

**Files:**
- Create: `app/Modules/Products/Models/Product.php`
- Create: `app/Modules/Products/Models/ProductImage.php`
- Create: `app/Modules/Products/Models/ProductDocument.php`
- Create: `app/Modules/Products/Models/ProductAttribute.php`
- Create: `app/Modules/Products/Models/ProductVideo.php`
- Create: `app/Modules/Products/Models/ProductView.php`
- Create: `app/Modules/Products/Models/ProductReport.php`
- Create: `app/Modules/Products/Controllers/PublicProductController.php`
- Create: `app/Modules/Products/Controllers/MyProductController.php`
- Create: `app/Modules/Products/Controllers/ProductImageController.php`
- Create: `app/Modules/Products/Controllers/ProductDocumentController.php`
- Create: `app/Modules/Products/Services/ProductService.php`
- Create: `app/Modules/Products/Services/AttributeService.php`
- Create: `app/Modules/Products/Requests/CreateProductRequest.php`
- Create: `app/Modules/Products/Requests/UpdateProductRequest.php`
- Create: `app/Modules/Products/Resources/ProductResource.php`
- Create: `app/Modules/Products/Resources/ProductListResource.php`
- Create: `app/Modules/Products/Policies/ProductPolicy.php`
- Create: `app/Modules/Products/Routes/api.php`
- Create: `app/Modules/Products/Providers/ProductsServiceProvider.php`

Product model fields: uuid, business_id, category_id, name_fr, name_en, description_fr, description_en, quantity_available, quantity_unit, moq, moq_unit, is_available, is_export_ready, is_custom_order, is_wholesale, is_retail, is_organic, is_certified, origin_region_id, status (draft|published|rejected), views_count, sort_order.

AttributeService: given a category_id, fetch attribute_templates for that industry and return them with the product's values merged in.

- [ ] Write all 7 models
- [ ] Write ProductService: create(), update(), publish(), reorder()
- [ ] Write AttributeService: getTemplatesForCategory(), saveAttributes()
- [ ] Write PublicProductController: index() with filters (industry, category, region, is_export_ready, is_organic, is_certified, is_available), show() with view tracking
- [ ] Write MyProductController: index(), store(), show(), update(), destroy(), publish()
- [ ] Write ProductPolicy: product belongs to authenticated user's business
- [ ] Write ProductResource (full with attributes, images, documents, business card) and ProductListResource (card: cover image, name, business name+badge, category, tags)

- [ ] Test: `POST /api/v1/my/products` creates product linked to user's business
- [ ] Test: `GET /api/v1/products?industry=aquaculture` filters correctly
- [ ] Test: unauthenticated can read, cannot create

### Task 3.2 — Search module (Meilisearch)

**Files:**
- Create: `app/Modules/Search/Controllers/SearchController.php`
- Create: `app/Modules/Search/Services/SearchService.php`
- Create: `app/Modules/Search/Routes/api.php`
- Create: `app/Modules/Search/Providers/SearchServiceProvider.php`
- Modify: `app/Modules/Businesses/Models/Business.php` — add `Searchable` trait
- Modify: `app/Modules/Products/Models/Product.php` — add `Searchable` trait

Business searchable: name_fr, name_en, description_fr, description_en, industry.name_fr, region.name_fr, tags
Product searchable: name_fr, name_en, description_fr, description_en, business.name_fr, category.name_fr

- [ ] Add `Searchable` trait to Business and Product models
- [ ] Define `toSearchableArray()` on each with filterable attributes
- [ ] Write SearchService: search(query, filters) — searches both businesses and products, returns merged results with type label
- [ ] Write SearchController: search() (unified), suggestions() (autocomplete)
- [ ] Log every search to `search_queries` table
- [ ] Run: `php artisan scout:import "App\Modules\Businesses\Models\Business"`
- [ ] Run: `php artisan scout:import "App\Modules\Products\Models\Product"`
- [ ] Test: `GET /api/v1/search?q=tilapia` returns matching products and businesses

---

## Phase 4: Messaging + Saved

### Task 4.1 — Messaging module

**Files:**
- Create: `app/Modules/Messaging/Models/Conversation.php`
- Create: `app/Modules/Messaging/Models/Message.php`
- Create: `app/Modules/Messaging/Models/MessageAttachment.php`
- Create: `app/Modules/Messaging/Models/QuoteRequest.php`
- Create: `app/Modules/Messaging/Controllers/ConversationController.php`
- Create: `app/Modules/Messaging/Controllers/MessageController.php`
- Create: `app/Modules/Messaging/Controllers/QuoteRequestController.php`
- Create: `app/Modules/Messaging/Controllers/ContactController.php`
- Create: `app/Modules/Messaging/Services/MessagingService.php`
- Create: `app/Modules/Messaging/Events/MessageSent.php`
- Create: `app/Modules/Messaging/Resources/ConversationResource.php`
- Create: `app/Modules/Messaging/Resources/MessageResource.php`
- Create: `app/Modules/Messaging/Routes/api.php`
- Create: `app/Modules/Messaging/Providers/MessagingServiceProvider.php`

One conversation per buyer+business pair (find-or-create). Messages broadcast via Laravel Reverb.

- [ ] Write Conversation model: buyer_id, business_id, product_id, status, last_message_at
- [ ] Write Message model: conversation_id, sender_id, body, type, read_at — with Broadcastable
- [ ] Write MessageSent event (broadcasts on `conversation.{uuid}` channel)
- [ ] Write MessagingService: findOrCreateConversation(), sendMessage(), markRead()
- [ ] Write ConversationController: index(), store(), show(), markRead()
- [ ] Write MessageController: index(), store()
- [ ] Write QuoteRequestController: store() (public, no auth needed for contact form)
- [ ] Write ContactController: store() (public contact form per business)
- [ ] All conversation endpoints require auth. Contact/quote endpoints are public.

- [ ] Test: auth user can start conversation with a business
- [ ] Test: unauthenticated user gets 401 on GET /api/v1/conversations
- [ ] Test: quote request succeeds without auth

### Task 4.2 — Saved module

**Files:**
- Create: `app/Modules/Saved/Controllers/SavedBusinessController.php`
- Create: `app/Modules/Saved/Controllers/SavedProductController.php`
- Create: `app/Modules/Saved/Routes/api.php`
- Create: `app/Modules/Saved/Providers/SavedServiceProvider.php`

saved_businesses and saved_products tables have composite unique(user_id, business_id/product_id). Toggle pattern: POST adds if not exists, returns 201. DELETE removes, returns 204. GET lists all saved items.

- [ ] Write SavedBusinessController: index(), toggle()
- [ ] Write SavedProductController: index(), toggle()
- [ ] Both require auth

- [ ] Test: toggle adds, second toggle removes

---

## Phase 5: Notifications + CMS + Support

### Task 5.1 — Notifications module

**Files:**
- Create: `app/Modules/Notifications/Models/NotificationTemplate.php`
- Create: `app/Modules/Notifications/Models/NotificationLog.php`
- Create: `app/Modules/Notifications/Models/NotificationPreference.php`
- Create: `app/Modules/Notifications/Notifications/NewMessageNotification.php`
- Create: `app/Modules/Notifications/Notifications/QuoteReceivedNotification.php`
- Create: `app/Modules/Notifications/Notifications/VerificationApprovedNotification.php`
- Create: `app/Modules/Notifications/Notifications/VerificationRejectedNotification.php`
- Create: `app/Modules/Notifications/Notifications/ProductApprovedNotification.php`
- Create: `app/Modules/Notifications/Services/NotificationService.php`
- Create: `app/Modules/Notifications/Controllers/NotificationPreferenceController.php`
- Create: `app/Modules/Notifications/Routes/api.php`
- Create: `app/Modules/Notifications/Providers/NotificationsServiceProvider.php`

Each notification class sends via: email (Mailable), push (FCM via curl to Firebase), SMS (Infobip API). Language resolved from user.language_preference. All log to notification_logs.

- [ ] Write 5 notification classes (NewMessage, QuoteReceived, VerificationApproved, VerificationRejected, ProductApproved) — each sends email + push, logs to notification_logs
- [ ] Write NotificationService: send(user, notification), getUnread(user)
- [ ] Write NotificationPreferenceController: index(), update()

### Task 5.2 — CMS module

**Files:**
- Create: `app/Modules/Cms/Models/CmsPage.php`
- Create: `app/Modules/Cms/Models/CmsPost.php`
- Create: `app/Modules/Cms/Models/CmsFaq.php`
- Create: `app/Modules/Cms/Models/CmsFaqCategory.php`
- Create: `app/Modules/Cms/Models/CmsAnnouncement.php`
- Create: `app/Modules/Cms/Controllers/PublicCmsController.php`
- Create: `app/Modules/Cms/Controllers/Admin/AdminCmsController.php`
- Create: `app/Modules/Cms/Routes/api.php`
- Create: `app/Modules/Cms/Providers/CmsServiceProvider.php`

- [ ] Write all 5 models
- [ ] Write PublicCmsController: pages(), page(slug), posts(), post(slug), faqs(), announcements()
- [ ] Write AdminCmsController: CRUD for all CMS content (admin role required)
- [ ] All public endpoints: no auth. Admin endpoints: `role:admin` middleware.

### Task 5.3 — Support module

**Files:**
- Create: `app/Modules/Support/Models/SupportTicket.php`
- Create: `app/Modules/Support/Models/SupportTicketReply.php`
- Create: `app/Modules/Support/Models/SupportCategory.php`
- Create: `app/Modules/Support/Models/HelpArticle.php`
- Create: `app/Modules/Support/Models/HelpCategory.php`
- Create: `app/Modules/Support/Controllers/TicketController.php`
- Create: `app/Modules/Support/Controllers/HelpController.php`
- Create: `app/Modules/Support/Routes/api.php`
- Create: `app/Modules/Support/Providers/SupportServiceProvider.php`

- [ ] Write all 5 models
- [ ] Write TicketController: index(), store(), show(), reply() — auth required
- [ ] Write HelpController: categories(), articles(), article(slug), feedback() — public read

---

## Phase 6: Analytics + API + Admin

### Task 6.1 — Analytics module

**Files:**
- Create: `app/Modules/Analytics/Models/SearchQuery.php`
- Create: `app/Modules/Analytics/Models/PlatformEvent.php`
- Create: `app/Modules/Analytics/Models/AdminDashboardSnapshot.php`
- Create: `app/Modules/Analytics/Services/AnalyticsService.php`
- Create: `app/Modules/Analytics/Jobs/TakeDashboardSnapshot.php`
- Create: `app/Modules/Analytics/Controllers/BusinessAnalyticsController.php`
- Create: `app/Modules/Analytics/Routes/api.php`
- Create: `app/Modules/Analytics/Providers/AnalyticsServiceProvider.php`

- [ ] Write AnalyticsService: trackView(entity, request), trackSearch(query, filters, count, request), trackEvent(type, entity, request)
- [ ] Write BusinessAnalyticsController: summary(), views(), searchAppearances() — business_owner auth, own business only
- [ ] Write TakeDashboardSnapshot job — runs nightly via scheduler, aggregates counts into admin_dashboard_snapshots

### Task 6.2 — API module (API as a Product)

**Files:**
- Create: `app/Modules/Api/Models/ApiConsumer.php`
- Create: `app/Modules/Api/Models/ApiKey.php`
- Create: `app/Modules/Api/Models/ApiUsageLog.php`
- Create: `app/Modules/Api/Models/WebhookSubscription.php`
- Create: `app/Modules/Api/Controllers/DeveloperController.php`
- Create: `app/Modules/Api/Controllers/Admin/AdminApiController.php`
- Create: `app/Modules/Api/Middleware/LogApiUsage.php`
- Create: `app/Modules/Api/Middleware/CheckApiKey.php`
- Create: `app/Modules/Api/Routes/api.php`
- Create: `app/Modules/Api/Providers/ApiServiceProvider.php`

- [ ] Write ApiKey model with `key_hash` (hashed for storage) and `key_prefix` (first 8 chars, shown in UI)
- [ ] Write DeveloperController: apply(), listKeys(), createKey(), revokeKey(), usage()
- [ ] Write CheckApiKey middleware: reads `X-API-Key` header, validates hash, applies rate limit, attaches consumer to request
- [ ] Write LogApiUsage middleware: logs endpoint, method, status_code, response_time_ms after response
- [ ] Register both middleware in `bootstrap/app.php`

### Task 6.3 — Admin module

**Files:**
- Create: `app/Modules/Admin/Controllers/AdminDashboardController.php`
- Create: `app/Modules/Admin/Controllers/AdminBusinessController.php`
- Create: `app/Modules/Admin/Controllers/AdminProductController.php`
- Create: `app/Modules/Admin/Controllers/AdminVerificationController.php`
- Create: `app/Modules/Admin/Controllers/AdminUserController.php`
- Create: `app/Modules/Admin/Controllers/AdminSettingsController.php`
- Create: `app/Modules/Admin/Controllers/AdminReportController.php`
- Create: `app/Modules/Admin/Models/AuditLog.php`
- Create: `app/Modules/Admin/Models/SystemSetting.php`
- Create: `app/Modules/Admin/Models/FeatureFlag.php`
- Create: `app/Modules/Admin/Services/ModerationService.php`
- Create: `app/Modules/Admin/Routes/api.php`
- Create: `app/Modules/Admin/Providers/AdminServiceProvider.php`

ModerationService: approveBusiness(), rejectBusiness(reason), approveProduct(), rejectProduct(reason), approveVerification(tier), rejectVerification(reason) — each action fires notification + logs to audit_logs.

- [ ] Write all admin controllers (all require `role:super_admin|admin` middleware)
- [ ] Write ModerationService with notification dispatch
- [ ] Write AuditLog model with `log(user, action, entity, old, new)` static method
- [ ] AdminDashboardController: returns latest snapshot + pending counts (businesses, products, verifications, reports)

---

## Phase 7: Seeding + Frontend + Polish

### Task 7.1 — DatabaseSeeder and SIAC seed data

**Files:**
- Create: `database/seeders/DatabaseSeeder.php`
- Create: `database/seeders/TaxonomySeeder.php`
- Create: `database/seeders/SiacSeeder.php` (30 fake businesses + 80 fake products for demo)
- Create: `database/seeders/AdminSeeder.php` (super_admin user)
- Create: `database/seeders/RolesSeeder.php`

Roles to seed: `super_admin`, `admin`, `moderator`, `business_owner`, `buyer`

SIAC businesses include: fish farms, feed companies, equipment suppliers, hatcheries, processing companies, cold chain companies, research institutes.

- [ ] Write TaxonomySeeder (all industries, all SIAC sectors, 10 Cameroon regions, 50+ cities, SIAC attribute templates, certifications)
- [ ] Write RolesSeeder (5 roles + permissions)
- [ ] Write AdminSeeder (admin@siac.cm / password, super_admin role)
- [ ] Write SiacSeeder (30 businesses, 80 products — all published, 10 with Basic tier, 5 with Verified)
- [ ] Run: `php artisan db:seed`
- [ ] Run: `php artisan scout:import "App\Modules\Businesses\Models\Business"`
- [ ] Run: `php artisan scout:import "App\Modules\Products\Models\Product"`

### Task 7.2 — OpenAPI documentation

**Files:**
- Modify: `config/scramble.php`

- [ ] Configure Scramble: set API title, version, description
- [ ] Add `@tags` PHPDoc to every controller (Scramble reads these for grouping)
- [ ] Add `@response` PHPDoc to key endpoints
- [ ] Verify: `GET /docs/openapi.json` returns valid OpenAPI 3.1 spec
- [ ] Verify: `GET /docs` (staging only) shows Swagger UI with all 226 endpoints

### Task 7.3 — Blade frontend (public-facing)

**Files:**
- Create: `resources/views/layouts/app.blade.php` (main layout with Tailwind, Inter font, Lucide)
- Create: `resources/views/layouts/dashboard.blade.php` (sidebar layout)
- Create: `resources/views/pages/home.blade.php`
- Create: `resources/views/pages/businesses/index.blade.php`
- Create: `resources/views/pages/businesses/show.blade.php`
- Create: `resources/views/pages/products/index.blade.php`
- Create: `resources/views/pages/products/show.blade.php`
- Create: `resources/views/pages/search.blade.php`
- Create: `resources/views/dashboard/home.blade.php`
- Create: `resources/views/dashboard/products/index.blade.php`
- Create: `resources/views/dashboard/messages/index.blade.php`
- Create: `resources/views/auth/login.blade.php`
- Create: `resources/views/auth/register.blade.php`
- Create: `resources/css/app.css` (Tailwind v4 design tokens)
- Create: `resources/js/app.js` (Alpine.js for interactivity, Axios for API calls)
- Create: `routes/web.php` (web routes serving Blade views, API calls done via Axios)

Design system implemented in Tailwind custom properties:
```css
:root {
  --color-primary-700: #0B6E4F;
  --color-primary-600: #0D8A62;
  --color-primary-100: #D4EFE5;
  --color-primary-50: #EAF7F1;
  --color-teal-700: #1A7A8A;
  --color-gold-600: #C8963E;
  --color-danger-600: #C0392B;
  --color-bg: #F5F6F4;
  --color-surface: #FFFFFF;
  --color-border: #DDE3DF;
  --color-text-primary: #1A2E25;
  --color-text-secondary: #4A6358;
  --color-text-muted: #8FA99A;
  --color-navy: #0F1D16;
}
```

- [ ] Install Tailwind CSS v4 + Alpine.js + Axios via npm
- [ ] Write `app.css` with design tokens and component classes
- [ ] Write `app.blade.php` layout: sticky top nav, FR|EN toggle, login/register CTAs, mobile bottom nav
- [ ] Write `dashboard.blade.php` layout: navy sidebar 256px, content area, mobile hamburger
- [ ] Write homepage: hero + search + industry grid + featured businesses + featured products + partners bar + footer
- [ ] Write businesses/index: filters sidebar + card grid + pagination
- [ ] Write businesses/show: cover + tabs (Overview/Products/Gallery/Documents/Contact) + sticky contact panel
- [ ] Write products/index: filter grid
- [ ] Write products/show: image gallery + specs table + sticky contact panel
- [ ] Write search results page: unified results with type tags
- [ ] Write dashboard home: KPI cards + quick actions + recent messages + completeness bar
- [ ] Write dashboard products: table with status chips + add button
- [ ] Write dashboard messages: conversation list + message thread
- [ ] Write auth/login and auth/register pages
- [ ] Run: `npm run build`
- [ ] Verify: homepage loads at `http://artisanatcameroun.test` with correct colors and fonts

### Task 7.4 — Admin frontend (Blade)

**Files:**
- Create: `resources/views/admin/dashboard.blade.php`
- Create: `resources/views/admin/businesses/index.blade.php`
- Create: `resources/views/admin/businesses/show.blade.php`
- Create: `resources/views/admin/products/index.blade.php`
- Create: `resources/views/admin/verifications/index.blade.php`
- Create: `resources/views/admin/users/index.blade.php`
- Create: `resources/views/admin/settings/index.blade.php`

Admin uses `dashboard.blade.php` layout with `neutral-900` sidebar.

- [ ] Write admin dashboard: KPI grid + action queues (pending businesses, products, verifications) + activity feed
- [ ] Write admin businesses: sortable table + status chips + approve/reject inline actions
- [ ] Write admin products: same pattern
- [ ] Write admin verifications: document viewer + approve/reject with notes modal
- [ ] Write admin users: table with role badge + suspend action
- [ ] Write admin settings: key-value form for system_settings

### Task 7.5 — Rate limiting + security headers

**Files:**
- Modify: `bootstrap/app.php`
- Modify: `app/Providers/AppServiceProvider.php`

- [ ] Define named rate limiters in AppServiceProvider: `public-api` (60/min), `authenticated-api` (120/min), `search` (30/min), `auth` (5/15min), `contact-form` (5/10min)
- [ ] Apply security headers middleware (HSTS, X-Content-Type-Options, X-Frame-Options, CSP, Referrer-Policy)
- [ ] Apply `ForceJson` middleware on all `/api/*` routes

### Task 7.6 — Final checks and smoke test

- [ ] Run: `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Run: `php artisan test` — all tests pass
- [ ] Verify: `GET /api/v1/businesses` returns 200 with paginated businesses
- [ ] Verify: `GET /api/v1/products` returns 200 with paginated products
- [ ] Verify: `GET /api/v1/search?q=catfish` returns results
- [ ] Verify: `POST /api/v1/auth/register` creates user and returns token
- [ ] Verify: `GET /docs/openapi.json` returns valid spec
- [ ] Verify: homepage renders correctly on 390px viewport (Chrome DevTools)
- [ ] Verify: business profile renders on mobile with bottom nav
- [ ] Verify: admin dashboard accessible at `/admin` for super_admin only

---

## Appendix: Standard API Response Envelope

All API responses use this envelope:

```json
{
  "data": {},
  "meta": { "page": 1, "per_page": 20, "total": 450, "last_page": 23 },
  "links": { "first": "...", "prev": null, "next": "...", "last": "..." }
}
```

Errors:
```json
{
  "message": "The given data was invalid.",
  "errors": { "email": ["The email field is required."] }
}
```

## Appendix: Module Service Provider Pattern

Every module uses this pattern:

```php
namespace App\Modules\{Module}\Providers;

use Illuminate\Support\ServiceProvider;

class {Module}ServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        // $this->loadMigrationsFrom(...) — NOT used; all migrations in database/migrations/
    }
}
```

## Appendix: Middleware Guard

All authenticated routes use `auth:sanctum`. All admin routes additionally use `role:super_admin|admin` (Spatie middleware alias).
