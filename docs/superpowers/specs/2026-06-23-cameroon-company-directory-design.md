# Galerie virtuelle de l'artisanat du Cameroun Platform — Design Specification

**Date:** 2026-06-23
**Status:** Approved for Implementation
**Author:** Claude (AI Engineer)
**Stack:** Laravel + MySQL, API-first, OpenAPI 3.1, OAuth 2.0

---

## 1. Executive Summary

A government-partnered, API-first platform for Cameroon that serves as the authoritative directory of small and medium-sized companies. The platform enables company registration, multi-tier government-backed verification (cross-referenced against RCCM, MINFI/NIU, ANOR, CNPS, and CMF registries), share offering listings, investor pledging, secondary market trading, and full regulatory compliance with Cameroonian law (OHADA, CMF, ANIF).

**Key constraints:**
- Bilingual: French (primary) + English
- Mobile-money first: MTN MoMo + Orange Money
- Government partnership: legally binding verifications via live registry sync
- CMF regulatory approval required for all share offerings
- OHADA law governs all business structures and shareholder rights
- VAT: 19.25% on platform fees

---

## 2. Architecture Decision

**Chosen:** Modular Monolith (Laravel)

Single Laravel application structured as isolated domain modules. Each module owns its controllers, services, models, and routes. Shared database, shared OAuth layer, single OpenAPI 3.1 spec.

**Rejected alternatives:**
- Simple monolith: insufficient separation for a 514-endpoint platform
- Microservices: too complex for initial delivery timeline

---

## 3. Module Structure

```
app/
├── Modules/
│   ├── Auth/
│   ├── Directory/
│   ├── Verification/
│   ├── Trading/
│   ├── Investors/
│   ├── Payments/
│   ├── Compliance/
│   ├── Notifications/
│   ├── Support/
│   ├── Cms/
│   ├── Api/
│   └── Admin/
├── Shared/
└── Console/
```

**Standards:**

| Concern | Standard |
|---|---|
| Authentication | OAuth 2.0 — Laravel Passport |
| API versioning | URI: `/api/v1/` |
| API documentation | OpenAPI 3.1 — dedoc/scramble |
| Authorization | Spatie Laravel Permission |
| File storage | AWS S3 (3 buckets: public, private, backups) |
| Queue | Laravel Horizon + Redis |
| Audit logging | Spatie Activity Log |
| Search | Meilisearch |
| Localisation | `Accept-Language: fr|en` header |

---

## 4. Roles

| Role | Capabilities |
|---|---|
| `super_admin` | Full platform access |
| `govt_reviewer` | Review and approve verifications |
| `cmf_reviewer` | Review and approve share offerings |
| `company_owner` | Manage own company, submit verification, create offerings |
| `company_member` | View company dashboard (read-only) |
| `investor` | Browse, KYC, pledge, trade |
| `public` | Browse directory (no auth required) |

---

## 5. Database Design

**Total tables: 156** across 12 modules.

### 5.1 Auth Module (13 tables)

```
users                    — Core user accounts
two_factor_settings      — TOTP/SMS 2FA configuration
otp_verifications        — OTP codes for phone/email/login
login_attempts           — Brute-force protection log
user_devices             — Push notification device tokens
user_preferences         — Notification + display preferences
social_logins            — OAuth provider links (Google, LinkedIn)
password_reset_tokens    — Password reset flow
user_sessions            — Active session tracking
roles                    — Spatie roles
permissions              — Spatie permissions
model_has_roles          — Spatie pivot
api_keys                 — Personal + consumer API keys
```

### 5.2 Directory Module (20 tables)

```
companies                — Core company registry (uuid, slug, legal_form, RCCM,
                           NIU, ANOR, CNPS, CMF, industry, region, verification_status,
                           trust_score, is_featured, claimed_by_user_id)
industries               — Industry taxonomy
sectors                  — Sector taxonomy (belongs to industry)
sub_sectors              — Sub-sector taxonomy
regions                  — Cameroon regions
cities                   — Cities per region
company_directors        — Directors/officers with ID documents
company_branches         — Physical branch locations with hours
company_opening_hours    — Per-branch operating hours
company_products_services — Product and service listings
company_gallery          — Photo/video gallery
company_awards           — Awards and certifications
company_relationships    — Parent/subsidiary/partner relationships
company_members          — Team members with roles
company_social_links     — Social media links
company_tags             — Free-form tags
company_claim_requests   — Workflow for claiming unclaimed companies
company_import_history   — Record of government registry imports
company_reports          — Fraud/misinformation reports
company_views            — Profile view analytics
company_contacts         — Contact form submissions
saved_companies          — User watchlists
featured_listings        — Paid featured placements
company_reviews          — Client/partner/investor reviews
review_votes             — Helpful/not helpful votes
review_responses         — Company replies to reviews
search_queries           — Search analytics log
saved_searches           — Saved searches with alert settings
```

### 5.3 Verification Module (11 tables)

```
verification_tiers           — Tier definitions (basic/verified/certified)
                               with required docs, fees, validity
verification_applications    — Verification requests per company
verification_documents       — Uploaded documents per application
                               (RCCM, NIU, ANOR cert, CNPS cert, CMF license,
                               articles of incorporation, director IDs,
                               audited financials, bank statements)
verification_checklist_items — Per-application checklist tracking
govt_registry_checks         — Results of live registry API calls
                               (RCCM, MINFI, ANOR, CNPS, CMF)
govt_sync_schedule           — Per-company, per-registry sync frequency
verification_badges_history  — Badge tier change log
verification_renewals        — Renewal tracking with reminder dates
verification_appeals         — Rejected application appeals
verification_fee_schedule    — Fees by tier and company size
verification_audit_log       — Every action on every application
```

### 5.4 Trading Module (20 tables)

```
share_offerings              — Offerings (private_sale, crowdfunding, ipo_preparation,
                               bond, convertible_note, rights_issue)
offering_documents           — Pitch decks, financials, prospectus per offering
offering_milestones          — Funding milestone targets
offering_updates             — Company news posted during offering
offering_qa                  — Investor Q&A per offering
offering_views               — Offering view analytics
offering_eligibility_criteria — Investor eligibility rules
cmf_review_workflows         — CMF regulatory approval workflow
cmf_review_steps             — Individual steps in CMF review
cmf_information_requests     — Requests for additional info from CMF
stock_listings               — Listed stocks (ticker, OHLCV, market cap)
stock_price_history          — OHLCV price history per interval
buy_orders                   — Buy order book (market/limit/stop)
sell_orders                  — Sell order book
trade_executions             — Matched and executed trades
escrow_accounts              — Funds held during offering period
escrow_transactions          — Deposits/releases/refunds per escrow
escrow_releases              — Disbursement to company after funding
shareholder_register         — Legal OHADA shareholder register
share_certificates           — Digital share certificates
corporate_actions            — Dividends, bonus shares, splits, buybacks
dividend_declarations        — Declared dividends
dividend_payments            — Per-investor dividend payments
lock_up_periods              — Post-investment lock-up rules
subscription_agreements      — Signed investor subscription agreements
```

### 5.5 Investors Module (14 tables)

```
investor_profiles            — KYC status, investor type, accreditation,
                               risk tolerance, experience, onboarding progress
kyc_documents                — National ID, passport, proof of address, selfie
kyc_audit_log                — KYC status change history
investor_preferences         — Preferred industries, regions, offering types
investor_onboarding_steps    — Step completion tracking
investment_pledges           — Pledge lifecycle (draft→payment→allocated)
investment_allocations       — Confirmed share allocations
portfolio_holdings           — Current holdings with P&L
exit_transactions            — Realised gains on sold positions
investor_watchlist           — Saved offerings/companies/stocks
investor_messages            — Investor ↔ company messaging threads
tax_documents                — Annual statements, dividend certs, CGT summaries
investor_referrals           — Referral program tracking
investor_notifications       — In-app notifications per investor
```

### 5.6 Payments Module (16 tables)

```
subscription_plans           — Free/Pro/Enterprise plan definitions
subscriptions                — Company subscription state
payment_transactions         — All payments (pledge, subscription, fee, payout, refund)
                               with full provider response logging
payment_provider_callbacks   — MTN MoMo / Orange Money async callbacks
platform_wallet              — Platform revenue balance
wallet_transactions          — Platform wallet credits/debits
payout_requests              — Company requests to disburse raised funds
payout_batches               — Batch processing of payouts
payout_batch_items           — Individual payouts per batch
invoices                     — Formal invoices per transaction
invoice_items                — Line items per invoice
tax_lines                    — VAT (19.25%) and other tax tracking
credit_notes                 — Refund credit notes
platform_fees                — Platform fee records per transaction
refunds                      — Refund request workflow
discount_codes               — Promo/discount codes
discount_code_uses           — Usage tracking per code
```

### 5.7 AML / Compliance Module (10 tables)

```
aml_screenings               — AML checks (onboarding, periodic, triggered)
pep_checks                   — Politically Exposed Person checks
sanctions_screenings         — Sanctions list checks
suspicious_activity_reports  — SARs filed to ANIF
sar_notes                    — Notes on SARs
compliance_cases             — Case management (fraud, AML, disputes)
compliance_case_notes        — Case notes and attachments
compliance_case_activities   — Case activity log
regulatory_filings           — CMF/ANIF/MINFI submissions
```

### 5.8 Notifications Module (7 tables)

```
notification_templates       — Email/SMS/push/WhatsApp templates (FR+EN)
notification_logs            — Delivery log per channel
email_logs                   — Email open/click/bounce tracking
sms_logs                     — SMS delivery + cost tracking
whatsapp_logs                — WhatsApp delivery + read receipts
push_notification_logs       — FCM/APNs delivery tracking
notification_preferences     — Per-user, per-channel, per-category toggles
```

### 5.9 Support Module (8 tables)

```
support_categories           — Ticket categories
support_tickets              — Tickets with SLA tracking
support_ticket_replies       — Staff and user replies
support_ticket_attachments   — File attachments per reply
support_ticket_history       — Status change log
help_articles                — Knowledge base articles
help_categories              — Knowledge base categories
help_article_feedback        — Helpful/not helpful per article
```

### 5.10 CMS Module (9 tables)

```
cms_posts                    — Blog, news, press releases, guides
cms_categories               — Content categories (nested)
cms_tags                     — Content tags
cms_post_tags                — Post-tag pivot
cms_pages                    — Static pages (About, Terms, Privacy)
cms_announcements            — Platform-wide banners (targeted by audience)
cms_faqs                     — FAQ entries
cms_faq_categories           — FAQ categories
cms_media_library            — Uploaded media assets
```

### 5.11 API / Webhooks Module (9 tables)

```
api_consumers                — Third-party API consumers (banks, govt, partners)
api_consumer_keys            — API keys per consumer
api_usage_logs               — Request log per consumer
webhook_subscriptions        — Webhook endpoint registrations
webhook_events               — Platform events that trigger webhooks
webhook_deliveries           — Delivery status per subscription+event
webhook_delivery_attempts    — Per-attempt request/response log
govt_api_integrations        — Govt registry API credentials + health status
```

### 5.12 Admin & Analytics Module (11 tables)

```
admin_dashboard_snapshots    — Daily KPI snapshots
platform_analytics_events    — Pageview and event tracking
report_configurations        — Saved report definitions with schedules
data_export_jobs             — Async export job tracking
audit_logs                   — Full platform audit trail
activity_log                 — Spatie Activity Log
system_settings              — Platform configuration key/value store
feature_flags                — Feature toggles with rollout %
admin_impersonations         — Admin impersonation audit log
search_analytics             — Search query analytics
popular_searches_cache       — Aggregated popular search terms
```

---

## 6. API Design

**Total endpoints: 514** across 12 modules.

### 6.1 Base URL & Versioning
```
Production:  https://api.cameroon-directory.cm/api/v1
Staging:     https://staging-api.cameroon-directory.cm/api/v1
Local:       http://localhost:8000/api/v1
```

### 6.2 Authentication
- OAuth 2.0 Authorization Code + PKCE (web SPA)
- OAuth 2.0 Client Credentials (server-to-server)
- Resource Owner Password (mobile app)
- Personal Access Tokens (developer API keys)
- Access token TTL: 15 minutes
- Refresh token TTL: 30 days

### 6.3 OAuth Scopes
```
directory:read      directory:write
verification:read   verification:write
trading:read        trading:write
investors:read      investors:write
payments:read       payments:write
admin:read          admin:write
govt:read           cmf:review
```

### 6.4 Response Envelope
```json
{
  "data": {},
  "meta": { "page": 1, "per_page": 20, "total": 450, "last_page": 23 },
  "links": { "first": "...", "prev": null, "next": "...", "last": "..." },
  "errors": []
}
```

### 6.5 Middleware Stack (every request)
```
ForceJsonResponse → SetLocale → ThrottleRequests → ValidateApiVersion
→ AuthenticateIfTokenPresent → RequireAuthentication → RequireEmailVerification
→ RequirePhoneVerification (payments) → CheckPermissions → CheckCompanyOwnership
→ CheckSuspension → ValidateRequest → SanitizeInput
→ [Controller] → StandardApiResponse → AddResponseHeaders → LogApiUsage
```

### 6.6 Rate Limits

| Endpoint Group | Limit | Window |
|---|---|---|
| Public browse | 120 req | / minute |
| Authenticated | 600 req | / minute |
| Login attempts | 5 | / 15 min / IP |
| OTP send | 3 | / 10 min / phone |
| OTP verify | 5 attempts | / 10 min |
| Payment initiation | 10 req | / minute / user |
| File upload | 20 req | / minute / user |

### 6.7 Endpoint Summary by Module

| Module | Endpoints |
|---|---|
| Auth | 52 |
| Directory | 74 |
| Verification | 40 |
| Trading | 72 |
| Investors | 52 |
| Payments | 44 |
| AML / Compliance | 22 |
| Notifications | 18 |
| Support | 30 |
| CMS | 40 |
| API / Webhooks | 28 |
| Admin & Analytics | 42 |
| **Total** | **514** |

---

## 7. User Interface Design

**7 portals, 60+ pages, 200+ distinct UI states.**

### 7.1 Portals

| Portal | Audience | Routes |
|---|---|---|
| Public Marketing Site | Anyone | `/`, `/directory`, `/offerings`, `/market`, `/verify`, `/pricing` |
| Auth Flows | All | `/register`, `/login`, `/forgot-password` |
| Company Owner Dashboard | Company owners/admins | `/dashboard/*` |
| Investor Dashboard | Investors | `/investor/*` |
| Government Reviewer Portal | Govt reviewers | `/reviewer/*` |
| CMF Portal | CMF regulators | `/cmf/*` |
| Super Admin Portal | Platform admins | `/admin/*` |

### 7.2 Public Site — Key Pages

**Homepage (`/`):** Hero with live stats counter, featured companies, open offerings, industry grid, verification explainer, news preview, government partner logos.

**Company Directory (`/directory`):** Left sidebar filters (industry, sector, region, legal form, verification status, employee count, capital range), company cards (grid/list toggle), sort options, save search with email alerts.

**Company Profile (`/directory/{slug}`):** Tabs — Overview, Products & Services, Gallery, Share Offerings, Reviews, Branches, Verify. Sticky contact sidebar. Verification certificate download.

**Offering Detail (`/offerings/{uuid}`):** Multi-tab layout (Overview, Documents, Updates, Q&A, Investors). Sticky investment panel with live progress bar, countdown timer, and state-dependent CTA (login / complete KYC / not eligible / invest now / view pledge / closed).

**Pledge Flow (`/invest/{uuid}`):** 6-step flow — Eligibility check → Amount → Sign agreement → Payment method → Processing → Confirmation.

**Instant Verify (`/verify`):** Search by company name or RCCM number, returns live registry status across all 5 registries with downloadable verification certificate.

**Stock Market (`/market`):** Market summary bar, stocks table with price/change/volume/market cap, mini sparklines. Stock detail page with candlestick chart (1D/1W/1M/3M/6M/1Y/All), tabs (Overview/Financials/Shareholders/News/Dividends), trade panel.

### 7.3 Company Dashboard — Key Sections

**Profile (11 tabs):** Basic Info, Description, Contact & Location, Logo & Cover, Social Links, Opening Hours, Products & Services, Awards, Branches, Directors, Related Companies.

**Verification (6 tabs):** Status + timeline, Application + tier selection, Documents checklist + upload, Registry check results, Appeals, Fee history.

**Offerings:** CRUD for offerings via 7-step form (Basics → Financial Terms → Eligibility → Risk Disclosure → Documents → Milestones → Review & Submit). Post-submission: investor list, analytics, Q&A management, CMF status tracking.

**Investor Relations (6 tabs):** Overview KPIs, Pledges table + allocation management, Allocations + certificates, Shareholder Register (OHADA-compliant export), Dividends (declare + pay), Communications inbox.

### 7.4 Investor Dashboard — Key Sections

**KYC (4 tabs):** Status + progress, Personal Information, Documents upload, Submission.

**Portfolio (6 tabs):** Overview with charts, Holdings, Certificates, Dividends received, Exits, Performance vs benchmark.

**Pledge Flow:** Full 6-step flow matching public offering flow.

### 7.5 Govt Reviewer Portal

Queue management, per-application review with inline document viewer, registry check results, per-document accept/reject, approve/reject application, request additional documents, appeal review.

### 7.6 CMF Portal

Offering review queue, per-offering full review (offering data + company data + documents + financials + workflow steps), information requests to companies, approve/reject offering, regulatory filing generation.

### 7.7 Admin Portal (12 sections)

Dashboard KPIs + charts, Users (manage + impersonate), Companies (all listings + bulk import + govt sync), Verification (manage tiers + fees + registry integrations), Trading (offerings + stocks + orders + payouts + dividends), Investors (KYC queue), Payments (transactions + refunds + wallet + callbacks), Compliance (AML + SAR + cases + filings), CMS (posts + pages + announcements + FAQs + media), Support (ticket queue + help articles), API (consumers + keys + usage + webhooks + govt integrations), Settings (12 tabs).

### 7.8 Universal UI States

Every page must handle: Loading (skeleton), Empty (illustration + CTA), API Error (retry), 401 (redirect to login), 403 (permission denied), 404 (branded), 500 (branded + support link), Offline (toast), Form validation (inline errors), Success (toast, auto-dismiss 4s), Destructive confirmation (type-to-confirm modal), Session expired (modal), Language switch (instant FR↔EN).

---

## 8. Security Architecture

### 8.1 Token Configuration
- Access token: 15-minute TTL, memory-only storage in browser
- Refresh token: 30 days, HttpOnly Secure SameSite=Strict cookie
- Token rotation on every refresh
- Revocation on password change

### 8.2 Security Headers (all responses)
```
Strict-Transport-Security: max-age=63072000; includeSubDomains; preload
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Content-Security-Policy: default-src 'self'; frame-ancestors 'none'
Referrer-Policy: strict-origin-when-cross-origin
```

### 8.3 OWASP Top 10 Coverage

| Threat | Mitigation |
|---|---|
| Broken Access Control | Spatie gates + Policy per model + UUID in URLs |
| Cryptographic Failures | TLS 1.3, bcrypt (cost 12), AES-256 for secrets, SSE-S3 |
| Injection | Eloquent ORM only, HTMLPurifier on rich text, UUID file names |
| Insecure Design | CMF approval gate, KYC gate, escrow model, signed agreements |
| Security Misconfiguration | APP_DEBUG=false enforced, no defaults, Swagger UI off in prod |
| Vulnerable Components | composer audit + npm audit in CI, Dependabot enabled |
| Auth Failures | Lockout after 5 attempts, 2FA, refresh rotation, signed URLs |
| Data Integrity | HMAC webhook signatures, payment callback verification, checksums |
| Logging & Monitoring | All auth + financial + admin actions logged, Sentry + alerting |
| SSRF | Webhook URL allowlist, no user-supplied server-side URLs |

### 8.4 Data Classification

| Class | Data | Controls |
|---|---|---|
| Public | Company names, logos, verification status | CDN cacheable |
| Internal | Emails, phones, amounts | TLS, masked in logs |
| Confidential | KYC docs, national IDs, agreements | S3 SSE, signed URLs, audit logged |
| Secret | API keys, OAuth secrets, govt credentials | Hashed/encrypted, shown once |

### 8.5 Document Retention
- KYC + verification documents: 7 years (OHADA + CMF)
- Subscription agreements: 10 years
- Transaction records: 10 years (tax law)
- AML/SAR documents: 10 years
- Exports: 48 hours then auto-deleted

---

## 9. Queue Jobs

**Total jobs: 67** across 5 priority queues.

| Queue | Workers | Timeout | Used for |
|---|---|---|---|
| critical | 3 | 30s | Payments, OTP, callbacks |
| high | 5 | 60s | Emails, SMS, document processing |
| default | 5 | 120s | Verification, KYC, allocations |
| low | 3 | 300s | Analytics, search index, webhooks |
| maintenance | 2 | 3600s | Bulk imports, reports, govt sync |

**Key job categories:**
- Auth: OTP delivery, session management
- Directory: company view logging, trust score recalculation, search indexing, govt import processing
- Verification: 5-registry checks, approval/rejection notifications, expiry reminders, renewal scheduling
- Trading: order matching engine (every minute), trade execution, stock price history recording, escrow release, dividend fan-out, certificate generation
- Investors: AML/PEP/sanctions screening on KYC submit, portfolio value recalculation (daily), tax document generation (yearly)
- Payments: MoMo payment polling (every 10s), callback deduplication, subscription renewal (daily), invoice generation (monthly), payout batch processing
- Maintenance: database backup (nightly), dashboard snapshot (nightly), govt registry full resync (nightly), health checks (every 15 minutes)

---

## 10. File Storage

### 10.1 Bucket Structure (AWS S3)

```
cameroon-directory-public   (CDN-served, public read)
  companies/{uuid}/logo.webp, cover.webp, gallery/
  cms/posts/{uuid}/cover.webp
  certificates/public/{uuid}/verification-badge.svg

cameroon-directory-private  (signed URLs only, no public access)
  verification/{company_uuid}/documents/
  kyc/{investor_uuid}/
  offerings/{offering_uuid}/documents/ + signed-agreements/
  certificates/shares/
  dividends/{declaration_uuid}/
  tax-documents/{investor_uuid}/
  compliance/sar/
  exports/{export_uuid}/

cameroon-directory-backups  (private, versioned, lifecycle policy)
  database/{date}/db-backup.sql.gz.enc
  logs/{date}/app.log.gz
```

### 10.2 File Security
- MIME type verified from file contents (not Content-Type header)
- Allowed: PDF, JPEG, PNG, WebP only
- File renamed to UUID on upload (no original name in filesystem)
- EXIF metadata stripped from images
- ClamAV malware scan triggered on every S3 upload
- Signed URLs: 60 minutes for documents, 24 hours for certificates
- All document access is audit-logged

---

## 11. OpenAPI 3.1 Configuration

**Tool:** `dedoc/scramble` (auto-generates from Laravel routes + PHPDoc)

**Security schemes:**
- `oauth2`: Authorization Code + PKCE, Client Credentials
- `apiKey`: `X-API-Key` header

**Standard parameters:** `Accept-Language` (fr|en), `page`, `per_page`

**Standard responses:** 200 data, 401 Unauthorized, 403 Forbidden, 404 Not Found, 422 Validation Error, 429 Too Many Requests, 500 Server Error

**Docs endpoints:**
```
GET /api/v1/health          → Service health check (all dependencies)
GET /docs                   → Swagger UI (staging only)
GET /docs/openapi.json      → Raw OpenAPI spec (JSON)
GET /docs/openapi.yaml      → Raw OpenAPI spec (YAML)
```

---

## 12. Deployment Architecture

### 12.1 Infrastructure
- 2x Nginx reverse proxy (load balancer + failover)
- 2x Laravel PHP-FPM application nodes
- MySQL 8.0 Primary + Read Replica
- Redis 7 Cluster (sessions, cache, queues)
- Meilisearch (full-text search)
- Laravel Horizon (queue workers + monitoring dashboard)
- AWS S3 (3 buckets)
- CloudFront CDN (public bucket + static assets)
- Cloudflare (DDoS, WAF, DNS, SSL termination)

### 12.2 CI/CD Pipeline (GitHub Actions)

Every push: lint → test → build
Every merge to main: lint → test → build → deploy-staging → smoke-tests
Every release tag: + manual approval gate → deploy-production → smoke-tests

**Deploy steps:** maintenance mode → pull → composer install (no-dev, optimized) → migrate → cache (config/routes/views/events) → Horizon restart → up → smoke tests

### 12.3 Environment Variables (key groups)
- App: APP_KEY, APP_ENV, APP_DEBUG=false
- Database: DB_HOST, DB_READ_HOST (replica)
- Redis: REDIS_HOST, REDIS_PASSWORD
- Storage: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, 3× bucket names
- Mail: SENDGRID_API_KEY
- SMS: INFOBIP_API_KEY (or Twilio)
- WhatsApp: WHATSAPP_ACCESS_TOKEN, WHATSAPP_PHONE_NUMBER_ID
- Push: FCM_SERVER_KEY, APNS_KEY_ID, APNS_TEAM_ID
- Payments: MTN_MOMO_* (3 vars), ORANGE_MONEY_* (3 vars)
- Govt APIs: RCCM_API_*, MINFI_API_*, ANOR_API_*, CNPS_API_*, CMF_API_*
- Compliance: VAT_RATE=19.25, PLATFORM_FEE_PERCENTAGE=2.5
- Monitoring: SENTRY_LARAVEL_DSN

### 12.4 Monitoring
- Error tracking: Sentry
- Log aggregation: daily rotating files → S3
- Uptime: BetterUptime (1-minute checks)
- Queue: Horizon dashboard + Slack alerts
- Payments: Slack alert on failure rate > 5% or callback timeout > 10 minutes
- Security: Slack + email alert on login brute-force, admin new-IP login, AML flag, SAR created
- Backups: nightly DB backup + automated daily restore test to staging

---

## 13. Government Integrations

| Registry | Purpose | Sync Frequency |
|---|---|---|
| RCCM (Registre du Commerce) | Business registration validation | On application + monthly re-check |
| MINFI / NIU | Taxpayer ID validation | On application + monthly re-check |
| ANOR | Standards/quality certification | On application + monthly re-check |
| CNPS | Social security registration | On application + monthly re-check |
| CMF | Financial markets license | On application + monthly re-check + on share offering |

All registry credentials stored AES-256 encrypted. Integration health checked every 15 minutes. Failures alert on-call. Manual override available to govt reviewers when registry is unavailable.

---

## 14. Payment Flows

### 14.1 MTN Mobile Money
1. Platform calls MTN MoMo API → `RequestToPay`
2. User receives push notification on phone → approves
3. MTN fires async callback to `/payments/momo/mtn/callback`
4. Platform verifies HMAC signature
5. Platform deduplicates (checks `provider_reference`)
6. On success: updates `payment_transactions.status = completed`
7. Downstream job triggered (pledge confirmation, subscription activation, etc.)

### 14.2 Orange Money
Same flow via Orange Money API.

### 14.3 Escrow for Share Offerings
- Investor pledge payment → deposited to `escrow_accounts`
- Funds held until offering closes
- If funded: `ReleaseEscrowToCompany` job → `payout_requests` → company receives net amount
- If cancelled/failed: `RefundEscrowToInvestors` job → refund per investor

### 14.4 Platform Fees
- Platform fee: configurable % (default 2.5%) on successful share raises
- Subscription: monthly/yearly XAF fee per plan
- Verification fee: per tier, per company size (from `verification_fee_schedule`)
- Featured placement: fixed XAF per placement type + duration
- VAT: 19.25% applied to all platform fees, tracked in `tax_lines`

---

## 15. Compliance & Regulatory

### 15.1 CMF (Commission des Marchés Financiers)
- All share offerings require CMF approval before going live
- CMF review workflow: submit → assign → review steps → information requests → approve/reject
- CMF case number recorded on approved offerings
- Monthly regulatory filing auto-generated

### 15.2 OHADA
- All company legal forms mapped to OHADA categories (SARL, SA, GIE, SNC, SCS, SCA, ETS, Cooperative)
- Shareholder register maintained per OHADA requirements
- Director information captured as required by OHADA
- Subscription agreements follow OHADA contract law

### 15.3 AML / FATF
- AML screening on all new investors at KYC submission
- PEP check on all investors and directors
- Sanctions screening on all investors and directors
- Periodic re-screening (monthly for active investors)
- Risk-based transaction monitoring with flag rules
- SAR filing workflow with ANIF (Agence Nationale d'Investigation Financière)
- Compliance case management for flagged entities

### 15.4 Data Protection
- Minimal data collection principle
- Data classification enforced (4 tiers)
- Right to erasure: soft-delete then legal hold check before hard delete
- Data retention schedules per document type (7–10 years)
- Cross-border data: hosted in af-south-1 (Cape Town) region

---

## 16. Localisation

- All user-facing text: French (primary) + English
- All database text fields: `_fr` + `_en` variants stored separately
- Language resolved from: `Accept-Language` header → user preference → default (fr)
- Date format: DD/MM/YYYY (fr), MM/DD/YYYY (en)
- Currency format: XAF 1 234 567 (fr), XAF 1,234,567 (en)
- All notification templates exist in both languages
- All CMS content managed in both languages
- All error messages localised

---

## 17. Implementation Phases (one-week delivery plan)

| Day | Focus |
|---|---|
| Day 1 | Laravel scaffold, module structure, database migrations, Auth module (OAuth, 2FA, OTP) |
| Day 2 | Directory module (companies, taxonomy, search), Verification module (applications, documents, registry checks) |
| Day 3 | Trading module (offerings, CMF workflow, escrow, order book, stock listings) |
| Day 4 | Investors module (KYC, pledges, portfolio, allocations, shareholder register) |
| Day 5 | Payments module (MTN MoMo, Orange Money, escrow release, payouts, invoicing) |
| Day 6 | Compliance (AML, SAR), Notifications (email/SMS/push/WhatsApp), Support, CMS, API/Webhooks |
| Day 7 | Admin portal, OpenAPI spec generation, security hardening, deployment pipeline, smoke tests |

---

## 18. Definition of Done

A feature is complete when:
- [ ] Database migration written and passing
- [ ] API endpoint implemented with Form Request validation
- [ ] OpenAPI annotation added (auto-picked by Scramble)
- [ ] Permission gate applied
- [ ] Relevant queue jobs implemented
- [ ] FR + EN localisation strings added
- [ ] Audit logging added for sensitive actions
- [ ] Feature test written (happy path + auth failure + validation failure)
- [ ] Rate limit applied to endpoint

---

## 20. UI/UX Design System

### 20.1 Design Tokens

**Colour Palette**

| Token | Hex | Usage |
|---|---|---|
| `primary-600` | `#16a34a` | Primary actions, badges, progress bars, success states |
| `primary-700` | `#15803d` | Hover states for primary |
| `primary-50` | `#f0fdf4` | Light green backgrounds |
| `accent-600` | `#d97706` | Accent CTAs, warning states, gold elements |
| `danger-600` | `#dc2626` | Errors, destructive actions, AML alerts |
| `info-600` | `#2563eb` | Informational badges, links, investor tier |
| `purple-600` | `#7c3aed` | Featured listings, premium tier badge |
| `navy` | `#1a1a2e` | Sidebar, dark navigation, investor portfolio header |
| `neutral-900` | `#111827` | Admin sidebar |
| `neutral-50` | `#f9fafb` | Page backgrounds |
| `neutral-100` | `#f3f4f6` | Card backgrounds, table rows |
| `neutral-300` | `#d1d5db` | Borders, dividers |
| `neutral-500` | `#6b7280` | Secondary text, labels |
| `white` | `#ffffff` | Cards, inputs, panels |

**Typography — Inter**

| Level | Size | Weight | Usage |
|---|---|---|---|
| Display | 32px / 2rem | 800 | Hero headings |
| H1 | 28px / 1.75rem | 800 | Page titles |
| H2 | 22px / 1.375rem | 700 | Section headers |
| H3 | 16px / 1rem | 700 | Card headers |
| Body Large | 14px / 0.875rem | 400 | Primary body text |
| Body | 13px / 0.8125rem | 400 | Standard body, form labels |
| Body Small | 12px / 0.75rem | 400 | Secondary text, descriptions |
| Caption | 11px / 0.6875rem | 500 | Table data, metadata |
| Micro | 10px / 0.625rem | 600 | Chips, tags, badges |

**Currency Format:** XAF amounts always display with space-thousands separator (FR standard): `XAF 2 500 000`. Avoid commas. Negative amounts in red.

**Spacing Scale (base-4):** 4 / 8 / 12 / 16 / 20 / 24 / 32 / 40 / 48 / 64px

**Border Radius:** 4px (tight), 6px (inputs/buttons), 8px (cards), 10px (badges/pills), 12px (modals)

**Shadows:** `0 1px 3px rgba(0,0,0,0.08)` (cards), `0 4px 12px rgba(0,0,0,0.12)` (dropdowns/modals)

---

### 20.2 Grid & Breakpoints

| Breakpoint | Min Width | Columns | Sidebar |
|---|---|---|---|
| `sm` | 640px | 4 | Hidden (hamburger) |
| `md` | 768px | 8 | Hidden (hamburger) |
| `lg` | 1024px | 12 | 200px fixed |
| `xl` | 1280px | 12 | 240px fixed |
| `2xl` | 1536px | 12 | 240px fixed + max-w-7xl |

Container: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`

---

### 20.3 Component Library

**Buttons**

| Variant | Background | Text | Border | Use |
|---|---|---|---|---|
| Primary | `primary-600` | white | none | Main CTA |
| Secondary | white | `primary-600` | `primary-600` | Secondary action |
| Danger | `danger-600` | white | none | Destructive |
| Ghost | white | `neutral-700` | `neutral-300` | Tertiary |
| Gold | `accent-600` | white | none | Featured/upgrade |

Sizes: `sm` (py-1 px-3 text-xs), `md` (py-2 px-4 text-sm), `lg` (py-3 px-6 text-base). All have loading spinner state.

**Form Inputs**

- Default: `border-neutral-300` → focus: `border-primary-600 ring-1 ring-primary-600`
- Valid: `border-primary-600` + green tick icon
- Error: `border-danger-600` + red X icon + error message below
- XAF Amount input: `XAF` prefix label + quick-select buttons (50k / 100k / 500k)
- File upload: dashed border dropzone, drag-and-drop, MIME validation client-side

**Cards**

- Base: `bg-white border border-neutral-200 rounded-lg`
- Company card: logo avatar (36×36, coloured initial fallback) + name + badges + sector/region + rating + footer with legal form/year + CTA
- Offering card: company name + badge + title + progress bar (green fill) + funded% + days remaining + Invest CTA
- KPI card: large metric + label + trend delta with arrow (green=up, red=down)

**Verification Badges**

| Level | Icon | Style | Description |
|---|---|---|---|
| Unverified | ○ | grey bg | No documents submitted |
| Basic | ◎ | amber bg | RCCM + NIU confirmed |
| Verified | ✓ | blue bg | +ANOR +CNPS confirmed |
| Certified | ✓✓ | green bg | All 5 registries + govt-signed |

**Status Chips:** `badge-open` (green), `badge-pending` (amber), `badge-closed` (neutral), `badge-rejected` (red). Font-size 9-10px, border-radius 20px, bold.

**Alerts:** 4 variants — success/warning/error/info — with left border accent, icon, message, optional dismiss button.

**Modals:** Centre-screen on overlay (`bg-black/50`). Title + body + footer (Cancel + Confirm). Destructive confirm uses red button. Press Escape or click overlay to close.

---

### 20.4 Navigation Architecture

**Public Top Navigation (sticky)**
- Logo left, nav links centre, FR|EN toggle + Login + Register CTA right
- Mobile: hamburger → full-screen overlay drawer

**Authenticated Sidebar (200px, `bg-navy`)**
- Logo + company name + verification tier badge at top
- Nav items: icon (16px) + label (11px) + optional notification badge
- Active item: left border `primary-600`, `bg-primary-600/15`, white text
- Hover: `bg-white/5`, `text-primary-100`

**Dashboard Top Bar**
- Page title left, date + notifications bell + avatar right
- Notification bell shows red badge count; click → dropdown list of recent alerts

**Tab Bars:** 12px bold text, inactive `text-neutral-400`, active `text-primary-600` + 2px bottom border. Used on: Company Profile, Offering Detail.

**Multi-Step Progress Indicator**
- Circles (26×26px): done = green filled + checkmark, active = green + ring shadow, todo = grey
- Connector lines: done = green, pending = grey
- Labels below circles (9px, green if done/active, grey if todo)

**Mobile Bottom Navigation (≤md)**
- Fixed bottom bar, 5 icons: Home / Search / Invest / Portfolio / Account
- Active icon: `primary-600` colour, filled

---

### 20.5 Page Layouts

#### Public Homepage
```
TopNav (sticky)
Hero (gradient #052e16→#166534, search bar, stat pills)
Stats Bar (4 metrics: companies / verified / capital raised / investors)
Sector Grid (6 sectors, icon + name + count, hover green border)
Featured Offerings (3-column card grid)
Government Partners Bar (MINFI / CCIMA / CMF / ANOR / CNPS logos)
Footer
```

#### Company Directory
```
TopNav (with search pre-filled)
Body:
  ├── Filter Sidebar (220px): verification status checkboxes, sector select,
  │   region select, legal form checkboxes, Apply/Clear buttons
  └── Results Area:
        Sort bar (count + sort dropdown + view toggle)
        Card Grid (3 col lg, 2 col md, 1 col sm) — 12 per page
        Pagination
```

#### Company Profile
```
TopNav
Breadcrumb (Home › Directory › Sector › Company Name)
Body:
  ├── Cover image (100px gradient)
  ├── Profile Meta: logo (56px, -28px margin-top), name + badges, key facts row
  ├── Tab Bar: Overview / Products & Services / Gallery / Share Offerings /
  │            Reviews / Branches / Verify ✓
  └── Tab Content + Contact Sidebar (240px):
        Contact form, profile stats, social links
Overview Tab:
  About card, Key Info card (legal details), Registry Checks card
```

#### Share Offering Detail
```
TopNav
2-column layout (1fr 280px):
  Main:
    Company header (logo, name, badge, status chip)
    Offering title + description
    Tab Bar: Overview / Documents / Updates / Q&A / Milestones
    Use of Funds breakdown (progress bars per line item)
    Risk Warning (amber left border)
  Panel (sticky):
    Amount raised + target
    Progress bar (10px height)
    Funded% + days remaining
    Stats grid (investors / price/share / min / max)
    Invest Now button (primary full-width)
    Save Offering button (ghost)
    Payment methods row (MTN MoMo / Orange Money / Bank)
    Trust signals (CMF approved / funds in escrow)
```

#### Investment Pledge Flow (5 steps)
```
Step 1: Eligibility check (KYC status, investment limits)
Step 2: Amount selection (quantity input + XAF total)
Step 3: Subscription agreement (PDF viewer + checkbox)
Step 4: Payment method (MTN MoMo / Orange Money / Bank Transfer)
Step 5: Confirmation (summary + pledge ID + escrow confirmation)
```
Progress indicator shown above each step. Back/Continue navigation. No sidebar.

#### Company Owner Dashboard
```
Sidebar (navy, 200px) + Content Area
Topbar: page title + date + notifications + avatar
Alert banner (amber): if verification expiring < 30 days
KPI Grid (4 cols): Capital raised / Investors / Goal% / Days remaining
Quick Actions: Edit profile / Submit docs / New offering / Invite member
Activity Feed + Offering Progress (2-col grid)
```

#### Investor Dashboard
```
Sidebar (navy, 200px) + Content Area
Portfolio Value card (dark navy bg, green total, invested / gain / dividends)
Holdings Table: company / shares / avg cost / current value / +/- %
Watchlist Alerts: countdown banners with Invest CTA
```

#### Verification Center (Company Owner)
```
Sidebar + Content
4-tier selector cards: Unverified / Basic / Verified / Certified
  Current tier highlighted (amber border), recommended (green border + badge)
Document checklist: accepted (green) / rejected (amber + Replace btn) / missing (dashed)
Registry Checks table: 5 rows (RCCM/NIU/ANOR/CNPS/CMF) with status + last checked date
```

#### Government Reviewer Portal
```
Admin-style sidebar (dark bg, MINFI branding)
Verification Queue table: company / tier requested / submitted / days waiting /
  doc status / registry status / Revise button
  Rows >7 days: red day count + "urgent" styling
My Stats this month: Approved / Rejected / Pending / Avg days
```

#### Super Admin Portal
```
Admin sidebar (neutral-900):
  Dashboard / Users / Companies / Verification / Trading / Investors
  Payments / Platform Wallet
  AML-KYC (badge: open alerts count) / Compliance Files
  CMS / Support (badge: unresolved count)
  API & Webhooks / Settings / Analytics
Topbar: title + date range picker + Export + avatar
KPI Grid (6 cols): Companies / Verified / Investors / Capital / Revenue / AML alerts
Charts: Registrations bar chart (30 days) + Revenue by type (progress bars)
Action Queue: AML alerts / overdue verifications / unresolved support / pending refunds
Activity Feed
```

---

### 20.6 UX Flows

**Registration → Verification → Offering**
1. `Register` button → email/phone + company name → OTP verification
2. Company profile wizard (4 steps: basic info → address → sector/size → contacts)
3. Auto-redirected to Verification Center — prompted to upload RCCM + NIU
4. Govt reviewer assigns to queue; company owner sees "Under Review" badge
5. Approval → email + SMS notification → badge upgrades to Basic/Verified/Certified
6. If Certified, "Create Share Offering" CTA unlocks in dashboard
7. Offering wizard → CMF review workflow → approval → offering goes live

**Investment Flow**
1. Investor browses offerings or follows direct link
2. Clicks "Invest" → prompted to log in if not authenticated
3. KYC check (if not done → KYC wizard inline)
4. 5-step pledge flow (above)
5. MTN MoMo / Orange Money: user receives USSD push, confirms on phone
6. Platform receives async callback → marks pledge as paid → escrow holds funds
7. On offering close: escrow released to company (success) or refunded (failure)

**Bilingual Switching**
- `FR | EN` toggle in top nav (all screens)
- Preference persists in `Accept-Language` cookie
- All currency, date formats, and form labels switch
- Company descriptions show `_fr` or `_en` column per preference

---

### 20.7 Accessibility (WCAG 2.1 AA)

- Colour contrast ≥ 4.5:1 for all text (green #16a34a on white = 4.54:1 ✓)
- All interactive elements keyboard-navigable, visible focus ring (`ring-2 ring-primary-600`)
- Touch targets ≥ 44×44px (mobile)
- All images have `alt` text; decorative images use `aria-hidden="true"`
- Form fields have associated `<label>` elements
- Error messages linked to inputs via `aria-describedby`
- Sidebar and modals trap focus correctly
- Skip-to-main link at page top (visible on focus)
- Progress steps use `aria-current="step"`
- Status badges use `role="status"` where dynamic

---

*Spec written by Claude (AI Engineer) — 2026-06-23*
*Government-partnered platform. All financial features subject to CMF regulatory approval.*
