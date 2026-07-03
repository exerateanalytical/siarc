# Design Replica Handoff — Galerie Virtuelle Nationale de l'Artisanat du Cameroun

Status as of 2026-07-03. This documents the pixel-replica work done so far so a fresh
Claude Code session (or any developer, on any account) can continue without prior context.

**CURRENT STATE: ALL 17 design PNGs are replicated and committed (all 34 tests
green) — 12 public pages + seller dashboard (desktop & mobile) + buyer dashboard
+ certificate verification + membership certificate. Nothing is pending. If new
design PNGs appear in the repo root, follow THE OVERRIDING RULE and the process
below.**

## THE OVERRIDING RULE — 100% pixel-perfect fidelity (user mandate, 2026-07-02)

The user's explicit instruction: **every image they provide must be reproduced 100%
pixel-perfect. Not a single letter, spacing, image, section, icon or color may be left
out — even if the content (a category, a count, a product) does not exist in the app.**

Concretely, this means:

- Reproduce ALL design content **verbatim**: exact text strings, exact numbers
  (even mock/marketing numbers like "124 produits" or "1 245 résultats trouvés"),
  exact section order, all sections.
- If the design shows entities that don't exist in the DB (categories, products…),
  **create them**: seed them as admin-editable rows where they are functional taxonomy
  (see the industries seeded below), AND render the design's names/numbers as static
  view data so the display matches the PNG regardless of DB state.
- For custom artwork (illustrated icons, patterns, photos, maps): **crop the actual
  pixels from the design PNG** into `public/images/landing/` and use `<img>` — do not
  approximate with lucide icons unless the design itself clearly uses a generic icon.
- Links still map onto real routes (never dead handlers / 404s) — pick the closest
  real route for each design element.
- This mandate arrived AFTER the landing/about/auth pages were built. Those pages
  contain all their design content, but auth additionally shows a few small
  preserved-logic extras below the card content (collapsed "Comptes de démonstration",
  a passkey button, "Continuer sans compte"). If the user asks for strict pixel purity
  on auth, hide those behind a keyboard shortcut or remove them after confirming.

## What is done

| Page | Design source (repo root) | View | Route | Commit |
|------|--------------------------|------|-------|--------|
| Landing | `Official landing page.png` (1024×1536) | `resources/views/pages/home.blade.php` | `/` (`home`) | `90c3735`, `1fbf5b8` |
| About | `about page.png` (884×1779) | `resources/views/about.blade.php` | `/about` (`about`) | `3b446ba` |
| Auth (login + signup) | `auth page.png` (1536×1024, both mockups side by side) | `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, shared `resources/views/auth/partials/replica-bottom.blade.php` | `/login` (`login`), `/inscription` (`inscription`) | `fad3992` |
| Categories | `categories page.png` (1536×1024) | `resources/views/pages/industries/index.blade.php` + shared `resources/views/pages/partials/gallery-header.blade.php` / `gallery-footer.blade.php` | `/galerie/secteurs` (`industries.index`) | `c1645fc` + fidelity rework (see git log) |
| Contact | `contact page.png` (1024×1536) | `resources/views/pages/contact.blade.php` (standalone — does NOT use the gallery partials, see notes) | `/contact` (`contact`), POST `/contact` (`contact.store`) | `27e4a92` |
| Product directory | `Product diretory.png` [sic] (1536×1024) | `resources/views/pages/products/index.blade.php` + NEW shared `pages/partials/directory-header.blade.php` / `directory-footer.blade.php` | `/galerie/produits` (`products.index`) | `a71e7d2` |
| Vendors directory | `vendors directory.png` (1536×1024) | `resources/views/pages/businesses/index.blade.php` (REPLACED the legacy layouts/app listing) using the directory partials with options | `/galerie/entreprises` (`businesses.index`, controller unchanged) | `d479146` |
| Product detail | `Product detail page.png` (1536×1024, canvas cut before footer) | `resources/views/pages/products/show.blade.php` (REPLACED the legacy layouts/app template) | `/galerie/produits/{slug}` (`products.show`) | `bfd3cd4` |
| Vendor detail | `vendors detail page.png` (1536×1024) | `resources/views/pages/businesses/show.blade.php` (REPLACED the legacy layouts/app template) | `/galerie/entreprises/{slug}` (`businesses.show`) | `fef96fb` |
| Events | `events page.png` (979×1606) | `resources/views/pages/events/index.blade.php` (REPLACED the legacy layouts/app listing) | `/evenements` (`events.index`, controller lang() now honours ?lang=) | `f915dcf` |
| Event detail | `events detail page.png` (984×1599) | `resources/views/pages/events/show.blade.php` (REPLACED the legacy layouts/app template) | `/evenements/{slug}` (`events.show`) | `7ace734` |
| Event ticket | `events ticket.png` (1536×1024) | `resources/views/pages/events/ticket.blade.php` (NEW standalone printable page, no site chrome per design) | `/evenements/{slug}/billet` (`events.ticket`, NEW route) | `846076f` |
| Default product images | `default product images by ategory.png` [sic] (1536×1024, asset sheet not a page) | 10 crops `default-product-{industry-slug}.png` wired as product-image fallbacks in `products/show` (gallery + related) and `businesses/show` (featured strip); legacy industries map artisanat→arts-decoration, aquaculture/agriculture→produits-naturels | — | `46f6ab4` |
| Seller dashboard | `seller dashbaord.png` [sic] (1536×1024) | `resources/views/pages/dashboard/entrepreneur.blade.php` (REPLACED the legacy layouts/dashboard view; standalone template) | `/tableau-de-bord/entrepreneur` (`dashboard.entrepreneur`; route now honours `?lang=`) | see git log |

### Seller-dashboard notes

- TEMPLATE page for every business_owner: real data threads through (shop name,
  logo w/ `sd-avatar-shop.png` fallback, member-since month, verified badge by
  real tier → links to `verification.show`, real Produits/Messages/Événements
  badge counts, real shop visits in KPI 3, real products in "Produits les plus
  populaires" → `products.web-edit` links preserve the product-edit flow, design's
  5 rows shown when the business has no products). Everything with no backing
  system is design-static verbatim: KPI numbers (356 000 FCFA / 28 / 3.6% / 96%),
  the 4 Commandes récentes rows (#GVN-2025-0009..0012), the activity feed, wallet
  amounts (156 500 / 45 200 / 890 750), region stats, "Vendeur Gold" pill,
  bell badge 5.
- Sidebar nav maps design items onto real routes: Commandes/Messages →
  messages.inbox, Produits/Avis & Clients → own storefront, Événements →
  events.index, Collections → saved.index, Statistiques → #performances,
  Revenus & Portefeuille → #portefeuille, Promotions → contact, Expéditions →
  support.index, Paramètres boutique → business.edit, Mon compte → profile.show,
  Aide & Support → support.index. Logout lives in the header profile dropdown
  (POST route('logout')) with Mon profil + Sécurité.
- Design artwork cropped: `sd-nav-1..14.png` (sidebar icons), `sd-kpi-icon-1..5` +
  `sd-kpi-spark-1..5`, `sd-chart.png` (whole plot incl axis labels),
  `sd-order-1..4` + `sd-pop-1..5` (product thumbs), `sd-avatar-shop`,
  `sd-user-avatar`, `sd-promo-art`, `sd-region-map`, `sd-event-art`,
  `sd-wallet-icon`. Quick-action + activity icons are generic → lucide.
- Palette: sidebar `#002714`, brand band `#031E12`, active row `#14391E` + gold
  bar/text `#FCB806`, badge red `#DC0508`, tricolor `#014D25`/`#CA0107`/`#F3AA02`
  (ONE star centered in red), search button `#052912`, wallet card `#07271A`,
  gold buttons `#FEBF00` (text `#3A2A03`), main bg `#FCFCFC`, KPI tint gradients
  white→`#F1F8EF`/`#FEF7EC`/`#F2F6FE`/`#FEF3F3`.
- The old dashboard's verification-progress card and event-participation list
  have no design equivalent; those flows remain reachable (verification via the
  sidebar badge link, events via the nav item). The old "no business yet" branch
  is kept inside the new chrome.
- **Preview-tool gotcha discovered here**: the preview browser's animation clock
  can freeze — CSS transitions stay `running` forever (breaking translate-based
  slide-overs) and preview_screenshot times out. Avoid `transition-transform` on
  the mobile sidebar (state is applied instantly via a plain `#dash-sidebar.open`
  media-query rule); verify via preview_eval/inspect instead of screenshots.

### Categories-page notes

- The page renders the design's 10 categories **verbatim as static view data**
  (`$designCategories` in the view): Arts & Décoration 124, Mode & Textile 112,
  Bois & Sculpture 96, Poterie & Céramique 88, Bijouterie & Accessoires 76,
  Cuir & Maroquinerie 65, Musique & Instruments 58, Produits Naturels 73,
  Agroalimentaire 59, Technologies & Innovation 42; sidebar pill "1245";
  results line "1 245 résultats trouvés".
- All category artwork is cropped from the PNG: `cat-icon-1..10.png` (card icon
  circles), `cat-side-0..10.png` (sidebar row icons), `cat-trust-1..5.png` (trust
  strip icons), plus `cat-region-map.png`, `cat-footer-kente-left/right.png`,
  `cat-footer-map.png`, `cat-sidebar-icon.png`.
- 8 industries were **seeded** so every design category filters a real route
  (slugs: `arts-decoration`, `bois-sculpture`, `poterie-ceramique`,
  `bijouterie-accessoires`, `cuir-maroquinerie`, `musique-instruments`,
  `produits-naturels`, `technologies-innovation`, sort_order 6–13, is_active 1).
  "Mode & Textile" links to the pre-existing `textile-mode`, "Agroalimentaire" to
  the pre-existing `agroalimentaire`. Do NOT rename the pre-existing industries.
- `FrontendController::industriesIndex` also computes live public product counts
  (published product + published business) and passes `$sort` — the view currently
  displays the design's static numbers (per the fidelity mandate) but the live
  counts remain available if the user ever asks to switch.
- Sort select works on the static array (`Populaires` = design order, `Nom (A–Z)`,
  `Produits`); grid/list toggle is client-side (localStorage `catView`).
- The `gallery-header` / `gallery-footer` partials are reusable for the upcoming
  product/vendor/events pages (`$galleryActive` picks the underlined nav item;
  expects `$lang`, `$isFr`, `$siacUser` in scope). Header includes the tricolor bar
  (green 28% / red 33% with two gold stars at 51.4% and 67% / gold gradient
  `#F2B01C→#E6C89A` with the heritage tagline right-aligned), search box wired to
  `gallery.search`, language dropdown, Connexion/Dashboard button (`#0A3020`).

### Contact-page notes

- The contact design's header and footer are DIFFERENT from both the gallery
  partials and the home page — they were built inline in the view:
  - Header: landing-style (logo + "Notre héritage…" tagline) but nav is
    Explorer / Collections / Artisans / Régions / **Secteurs** / Événements /
    À propos, a search **icon** (links to `gallery.search`, no input box), and
    the mockup shows a pale-gold underline under "Événements" — replicated
    verbatim (`$navLinks` third tuple element).
  - Tricolor bar: green 41% / red 18.6% with ONE star centered / gold —
    (`#015D38`/`#C10B1B`/`#EBAC23`), 18px tall.
  - Footer: near-black `#131110` with faint pattern texture
    (`contact-footer-tile.png` bg-repeat), kente strip image on top
    (`contact-kente.png`), columns EXPLORER (…, Secteurs, Entreprises,
    Actualités) / RESSOURCES (Guide de l'artisan, Formations, Financements,
    Documents utiles, FAQ) / À PROPOS (…, Carrières, Presse) / RESTEZ INFORMÉ
    (Votre email + green `#1B5B3C` S'inscrire → `/inscription`). Legal bar is
    hardcoded "© 2025" per the fidelity mandate.
- Hero: `contact-hero.png` is the design hero with the baked-in text patched
  out (per-row horizontal-gradient fill between clean left/right samples —
  flat fill showed seams against the photo bokeh). The gold diamond ornament
  was cropped separately (`contact-ornament.png`) and sits in the text flow.
- Map: `contact-map.png` includes the baked-in "Notre emplacement" card
  (crisp design pixels). A transparent `<a>` positioned at 58.6%/51.1%
  covers the "Itinéraire" button and opens Google Maps directions in a new
  tab — the one external link on the page (offline rule concerns assets,
  not hrefs).
- Icons cropped: `contact-info-1..5.png` (info strip), `contact-help-1..5.png`
  (help section), pattern edges `contact-help-left/right.png`,
  `contact-cta-left/right.png`.
- Form wiring (`support_tickets.user_id` is NOT nullable, so no guest
  tickets): POST `/contact` validates (name/email/subject/message/consent
  `accepted`), rate-limited 5/5min per IP. Logged-in → real `SupportTicket`
  + first reply (message + "— name <email>" appended). Guest → `Mail::raw`
  to contact@gvnac.cm in try/catch (non-fatal; .env uses smtp→127.0.0.1:1025
  Mailpit, usually down in dev — same pattern as password-reset mail).
  Both redirect back with a success flash rendered above the form.
- "Nous contacter" links elsewhere now point at `route('contact')`:
  home footer + categories sidebar help card (were `support.index`).

### Product-directory notes

- The directory design family (product + vendors directories) has ITS OWN header
  and footer, different again from gallery-header/footer and contact:
  - `directory-header.blade.php`: thin tricolor (h-5, green 37.5% / red 27.6%
    with ONE star at 45.2% of the red block = page center / gold `#FBB604`),
    search input + "Toutes les catégories" select + dark-green search button in
    one group, Favoris (heart → saved/login), Demandes (bag → messages/login),
    globe FR dropdown, "Se connecter" `#02301B`. Accepts optional
    `$dirSearchCategories`; search posts to `gallery.search` with `categorie`.
  - `directory-footer.blade.php`: deep green `#012B1C`, kente side strips
    (`product-kente-left/right.png`), WHITE-FILLED social circles (FB IG LI YT X),
    RESSOURCES = Guide de l'artisan / FAQ / Centre d'aide / Blog / Conditions
    d'utilisation, map `product-footer-map.png` + caption "Cameroun, terre de
    créativité et d'innovation", 2-link legal bar (© 2025 hardcoded). À propos
    includes "Nous contacter" (user request, added on top of the design's 5).
- Design content verbatim as static view data (`$designSideCats`,
  `$designProducts` in the view): sidebar counts 5248/642/918/567/487/713/398/
  296/621/834/172, vendor type counts Artisan 3421 / Entreprise 1642 /
  Coopérative 185, results line "5 248 produits disponibles", pagination
  1 2 3 4 5 … 175, badges NOUVEAU (cards 1+5) / BEST-SELLER (card 2).
- **All 12 design products were SEEDED as real published products**
  (`database/seeders/DesignProductsSeeder.php`, idempotent, run with
  `artisan db:seed --class=DesignProductsSeeder`): slugs panier-africain-tresse,
  sculpture-en-bois-sawa, sac-a-main-traditionnel, vase-en-terre-cuite,
  collier-perles-africaines, sac-en-cuir-veritable, djembe-traditionnel,
  miel-naturel-du-cameroun, feves-de-cacao-premium, savon-naturel-artisanal,
  lampe-solaire-artisanale, beurre-de-karite-pur — attached to existing
  published businesses, cover images copied from the design crops into
  `storage/app/public/products/{slug}/images/design.png`. Every card, title
  and ENQUÉRIR button links to the real `products.show` page.
- Functional bits: `?categorie=` filters the static grid (sidebar links),
  `?sort=` (recents = design order, name), region/vendor/dispo filter form
  round-trips via GET, grid/list toggle persists in localStorage `prodView`.
- Assets: `product-1..12.png` (card photos, also used as seeded covers),
  `product-side-0..10.png` (sidebar icons), `product-trust-1..5.png`,
  `product-stamp.png` (AUTHENTICITÉ GARANTIE circular badge),
  `product-footer-map.png`, `product-kente-left/right.png`.

### Vendors-directory notes

- **Warning: the vendors PNG has non-96 DPI metadata** — GDI+ `DrawImage` scaling
  silently blits 1:1 unless you call `$bmp.SetResolution(96,96)` after loading.
  Also its mockup is DENSER (~0.65× the product page's scale); chrome was kept at
  product-directory scale for cross-page consistency, content structure verbatim.
- Reuses `directory-header` (options: `$dirIconVariant='vendors'` → Favoris +
  Messages + Panier with badge "3"; `$dirSearchPlaceholder`; `$dirNavActive`
  renders the secondary icon nav bar: Accueil/Catégories/Artisans/Entreprises/
  Régions/Collections/Événements/À propos, active = gold underline) and
  `directory-footer` (options: `$dfExplorer`/`$dfRessources` arrays,
  `$dfNewsletterText`, `$dfShowHelp` → BESOIN D'AIDE ? column with
  +237 670 416 238 / contact@galerieartisanat.cm / Lun - Ven : 8h00 - 17h00 /
  gold-outline "Nous contacter →" → route('contact'); `$dfSocialStyle='outline'`,
  `$dfShowLegalLinks=false`).
- View REPLACED `pages/businesses/index.blade.php` (was a layouts/app page);
  `FrontendController::businessIndex` unchanged — the sidebar search/category/
  region filters post REAL params (q, industry, region by code) the controller
  already supports. Design counts static: 2,548 found / stats box 2,548 · 10+ ·
  58 · 100% / profile types (1,842)/(542)/(164) / pagination 1-5 … 64.
- **All 8 design vendors SEEDED as real published businesses**
  (`DesignVendorsSeeder`, idempotent): ceramiques-du-noun (Foumban),
  afrik-cuir-excellence (Douala), sawa-wood-art (Kribi), tressage-bamenda
  (Bamenda, cooperative), perles-du-sahel (Maroua), tissus-racines (Yaoundé),
  rythmes-dafrique (Douala), nature-bienfaits (Bafoussam) — verification_tier
  'verified', owner borrowed from the first existing business, cover images =
  design crops in `storage/app/public/businesses/{slug}/cover.png`. Cards and
  "Voir le profil" link to real `businesses.show` pages.
- Card artwork (`vendor-1..8.png`) keeps the BAKED badges (gold pills — note the
  design's own spellings "ARTISAN"/"ENTERPRISE"/"COOPÉRATIVE") and baked heart
  buttons (a transparent link overlays the heart for favorites). Avatar strips
  `vendor-av-1..8.png` + "+N" as HTML text. Other assets: `vendor-hero-map.png`,
  `vendor-cta-mask.png`, `vendor-cert-icon.png`, `vendor-trust-1..5.png`,
  `vendor-margin.png` (subtle right page-margin kente watermark, repeat-y).

### Product-detail notes

- This is a TEMPLATE page (serves every product), rebuilt so the design's product
  renders pixel-true while other products get the same layout with their own data.
  The old 1020-line layouts/app template was replaced.
- **Seeded (`DesignProductDetailSeeder`)**: the product
  `vase-en-terre-cuite-grave-a-la-main` (Céramiques du Noun) with the design's
  exact name/description, a 5-image gallery (`pdetail-main.png` +
  `pdetail-thumb-2..5.png`, copied into storage), the taxonomy chain the
  breadcrumb shows (NEW sector `poterie-ceramique-arts` under the
  arts-decoration industry + NEW category `poterie-ceramique-design` named
  "Poterie & Céramique"), and NEW attribute templates with the design's exact
  spec labels (Matière/Technique/Origine/Couleur/Dimensions/Poids, scoped to
  the business's industry) with verbatim values. Céramiques du Noun also got
  the circular logo crop (`pdetail-artisan-logo.png`) as its real logo.
- Spec rows render generically: Catégorie + product attributes in template
  sort order (icons mapped by label keyword). Breadcrumb = Accueil ›
  category.sector.industry › category.sector › name.
- Header = directory-header variant 'detail' (Favoris + Panier badge "2",
  `$dirCartCount` option). The design canvas CUTS OFF before any footer —
  directory-footer (product-directory defaults) is used.
- Design-static template content: "Fait main" chip, rating fallbacks
  4.8 (23 avis) product / 4.8 (56 avis) artisan (real review data wins when it
  exists), feature chips, ENQUIRY/message buttons, WhatsApp/Email/Appel/
  Partager/favoris icon row (wa.me + mailto + tel links, navigator.share),
  "Vous ne trouvez pas..." note, 7 tabs (client-side; only Description content
  is specified by the design — other tabs carry sensible real data), artisan
  card stats 156/98%/2 ans, delivery info card, Besoin d'aide card
  (→ route('contact')), confidence card with `pdetail-stamp.png`.
- "Vous pourriez aussi aimer": real related products; `productShow` now fills
  up to 6 with recent public products when category/business yield too few.
- IMPORTANT: product image URLs in replica views must use
  `asset('storage/' . file_path)` — the `ProductImage->url` accessor builds
  from APP_URL (artisanatcameroun.test), which breaks on the preview ports.

### Vendor-detail notes

- Template page (serves every business), replaced the legacy layouts/app view.
  Header = directory-header variant 'vdetail' (Favoris + Messages badge "3" +
  Panier without badge; icon flags are now derived per variant in the partial).
  Secondary nav bar active = Entreprises. Footer = vendors options + Événements
  in EXPLORER + Politique de confidentialité in RESSOURCES.
- **Seeded (`DesignVendorDetailSeeder`)**: Céramiques du Noun profile details —
  tagline "Poterie & Céramique Traditionnelle", year_established 2018,
  employee_count 8, phone/whatsapp/email/website, response_time_hours 2,
  languages ['Français','English'], created_at forced to 2021 ("Membre depuis
  2021") — plus 3 new products for the Produits phares strip:
  pot-traditionnel-bamoun, jarre-decorative (Céramiques du Noun) and
  sac-en-cuir-artisanal (Afrik Cuir Excellence), covers from design crops.
- Design-static template values with real-data fallbacks: rating 4.8 (156 avis),
  ID ENT-CN-2021-0456 and registration RC/DLA/2018/B/1234 (slug-mapped for the
  design business, generated formulas otherwise), hero stats 8/312/1,842/98%/
  2 ans/100%, tab counts Produits (312) / Collections (12) / Avis (156),
  "Voir tous les produits (312)".
- Banner `vdetail-banner.png`: design photo with title/paragraph/stats patched
  out (mirror-tile + flat strip fill; residual banding is hidden by the CSS
  left gradient overlay). Title/paragraph/stats bar are live HTML on top.
- Produits phares = business products topped to 6 by `businessShow` (new
  `$featuredProducts`), horizontal scroll carousel with arrows.
- Assets: vdetail-banner/about/client/why-pattern/cert-1..4/prod-pot/
  prod-jarre/prod-sac.

### Events-page notes

- Ticket-style listing at `events.index`; the design's 6 events render verbatim
  as static view data AND were **seeded as real events** (`DesignEventsSeeder`,
  design's 2025 dates kept verbatim — they're past dates, `events.show` still
  serves them; the legacy 3 events remain untouched). "Voir détails" → real
  `events.show` pages.
- Ticket anatomy: colored left stub (date/time + CSS `repeating-linear-gradient`
  barcode + notch circles), white body (badge pill, verified title, gold pin
  city + venue rows, description, 4 tag chips), colored right stub (star, price,
  gold "Voir détails", bookmark). Stub colors green `#06301A` / red `#C1272D` /
  gold `#EFA912`; badges red/gold/green per design.
- Design-static numbers: "128 événements trouvés", sidebar category counts
  (32/28/24/16/18/10/8/12), region counts (38/25/18/14/10/8/7/5/3/3), prices
  (ENTRÉE LIBRE / 2 000 / 5 000 / 3 000 FCFA).
- Functional: type pills + sidebar category/region links filter the static
  list via ?type=/?region=; sort works; filter panel round-trips; "Soumettre
  un événement" → admin.events for admins, contact page otherwise;
  "Charger plus" → events.index.
- Header: 'vdetail' variant with RED Messages badge "2" (`$dirMsgBadgeColor`).
  Footer gained options: `$dfBrandParagraph` ("Plateforme officielle … à
  travers le digital."), `$dfShowPayments` (event-payments.png strip: MTN
  Mobile Money, Orange Money, VISA, Mastercard, PayPal), `$dfBgColor`
  (`#021A0D` here — darker than the directory pages).
- **Fixed**: `EventWebController::lang()` read the cookie only; it now honours
  `?lang=` (same bug family as the terms/privacy note below).
- Assets: `event-icon-1..6.png` (ticket illustrations), `event-map.png`
  (sidebar map with pins), `event-payments.png`.

### Event-detail notes

- Template page at `events.show`; the 6 seeded design events carry per-slug
  display maps in the view (`$eventMeta`: city line, venue, badge + color,
  price, tag chips) with generic fallbacks parsed from `location_fr` for other
  events. Date/time/title/description come from the event row.
- NEW header option `$dirTopBar`: the tricolor becomes a 26px utility bar —
  tagline on green, star on red, links on gold (Devenir partenaire →
  partners.index, Espace Artisan / Espace Entreprise → /login, Aide → contact).
- Hero = ticket: dark green stub (big date, times, pin, CSS barcode, notch),
  CREAM panel (`#FAF5EC`) with `edetail-art.png` (mask/vase artwork + pattern +
  star circle, baked) on the right, badge/serif title/description/chips HTML.
- Design-static template content: stats 500+/50+/20+/10+/1, 7 tabs (À propos
  has the design paragraph for the design event + 5 Objectifs checks + video
  card `edetail-video.png` with baked play overlay, href="#"), 5 Points forts
  cards (`edetail-pf-1..5.png`), 6 Régions participantes silhouettes
  (`edetail-region-1..6.png`), 5 Partenaires officiels (`edetail-partner-1..5`:
  MINPMEESA/ONUDI/BANGE/CAMPOST/AFC).
- Right rail: Réservez votre place (price, S'inscrire → /inscription,
  "Ajouter à mon agenda" → real Google Calendar template URL, share circles →
  real wa.me/facebook/twitter/linkedin/mailto share links), Informations
  pratiques ("Voir sur la carte" → Google Maps search), Organisateur MINPMEESA
  card (site officiel → minpmeesa.gov.cm), Télécharger (3 PDF rows, href="#"
  as the PDFs don't exist), Restez informé mini-newsletter.

### Event-ticket notes

- Standalone printable page (no header/footer, per the design): TÉLÉCHARGER
  (PDF) + IMPRIMER buttons both call window.print() (@media print hides them
  and the feature row). Reached from the event detail page — "S'inscrire
  maintenant" routes logged-in users to the ticket, guests to /inscription.
- Ticket anatomy: left date column (kente `ticket-pattern.png` tile +
  `ticket-swoosh.png` brush/star crop + white SVG curve divider), center
  (logo/brand, badge, uppercase title, description, 5 illustrated chips
  `ticket-chip-1..5.png`, stats box 500+/50+/20+/10+), artwork
  `ticket-art.png` (re-cropped from x852 to exclude the baked stats-box
  fragment), dark contact bar (www.galerieartisanat.cm /
  contact@galerieartisanat.cm / +237 670 416 238 / social), CSS perforation,
  stub with LIVE QR code (vendored qrcodejs, encodes the event URL + ticket
  id), ENTRÉE GRATUITE (red) or the price, dashed divider, DATE/HEURE/LIEU/
  TYPE rows, TICKET ID (GVC-2025-00012345 for the design event, generated
  GVC-{year}-{8 digits} otherwise), CSS barcode, "MERCI DE SOUTENIR
  L'ARTISANAT CAMEROUNAIS" strip.
- Bottom feature row (hidden on print): Imprimable / Mobile / Sécurisé /
  Éco-responsable with cropped icons (`ticket-feat-1..4.png`).
- Same per-slug meta map as the detail page (city/venue/badge/price).

### Auth-page notes

- The design canvas holds two page mockups (login left 784px wide, signup right
  713px wide) plus a shared full-width "Pourquoi rejoindre" band and footer; the
  band + footer + mobile bottom nav live in `auth/partials/replica-bottom.blade.php`.
- All form logic preserved: POST `/login` (email+password+hidden `next`
  passthrough), POST `/inscription` (name/email/phone/password/role). The signup
  3-step wizard is purely client-side inside one `<form>`; a server validation
  error reopens step 2. Google/Facebook buttons are visual-only ("Bientôt
  disponible" note on click). The login card is absolutely positioned over the
  baked-in card in `auth-hero.png`.
- Web login accepts email only (`type="email"` is correct — the "phone" login test
  targets the separate API endpoint `/api/v1/auth/login`).
- Assets: `auth-*.png` (hero, baskets, footer-band, footer-map, footer-motif,
  band-motif-left/right).

## What is pending — build in this order

All public pages AND the seller dashboard (desktop + mobile, both inside
`pages/dashboard/entrepreneur.blade.php`: desktop chrome is `hidden lg:*`, the
mobile page is a `lg:hidden` block replicating `seller mobile dashboard.png` —
hero flag card `sm-hero-flag.png` with the HTML "Voir ma boutique" button
covering the baked one, 6 KPI tiles `sm-kpi-1..6`, Pipeline des devis
`sm-pipe-1..5` with dashed connector, Activité récente `sm-act-1..5`, 6 Actions
rapides `sm-qa-1..6`, product cards `sm-prod-1..4` with baked rank chips,
wallet bar `sm-wallet-icon`, bottom tab bar with green FAB → products.web-create
and a Menu tab that opens the sidebar slide-over) are done. The user approved
dashboard replicas on 2026-07-03 ("proceed in order"). Remaining:

The buyer dashboard is also done: `buyer dashboard mobile.png` → REPLACED
`pages/dashboard/buyer.blade.php` (route `dashboard.buyer` now honours `?lang=`
and passes `$buyerSince`). NOTE: despite its filename the design's CONTENT is a
seller/boutique dashboard ("Bonjour, Artisan Ndop", Chiffre d'affaires, Gérer
produits, "Développez votre boutique") — replicated verbatim per the mandate,
with name/member-since as template fields. Mobile-first page rendered centered
at `max-w-[431px]` on all screen sizes. The design has no sidebar, so buyer
flows (saved businesses, messages, notifications, profile, security, support,
LOGOUT) live in an extrapolated dark-green slide-over menu (hamburger + Menu
tab). Assets: `bm-avatar/hero-flag/kpi-1..4/order-1..4/qa-1..8/promo-art.png`
(hero "Voir ma boutique" HTML button covers the baked one; it links to
business.create since buyers have no shop). Design statics: KPIs 28/+18%,
356 000 FCFA/+24%, 1 245/+12%, 96% Excellent; same 4 GVN orders; badges 3/5/12
(Messages badge uses the real conversation count).

**ALL 17 design PNGs are now replicated. Nothing is pending.** The last two:

- **Certificate verification** (`certificate verification page.png`, 1024×1536) →
  NEW public route `/verification-certificat` (`certificate.verify`), standalone
  `pages/certificate-verify.blade.php` with its own header (nav Accueil/À propos/
  Artisans/Produits/Événements/Actualités/Contact + Se connecter) and footer
  (LIENS RAPIDES / RESSOURCES / SUPPORT / CONTACT + "Plateforme officielle du
  gouvernement du Cameroun" mini-flag). Hero uses `cert-hero-art.png` (shield +
  flag curves). Two working tabs (numéro / QR); the form GETs `?numero=` back to
  the page; the result card is the design's demo content verbatim (GVN-2025-0002587,
  Artisan Ndop, all 8 info rows, "Vérifié le 15 Mai 2025 à 14:35") with
  `cert-image.png` (the certificate artwork cropped from the design). Both QR
  codes are LIVE (vendored qrcodejs, encode the verification URL). Assets:
  `cert-hero-art/cert-image/cert-card-icon-1..3.png`.
- **Membership certificate** (`memersbip certificate.png` [sic], 1536×1024) →
  NEW auth route `/certificat-adhesion` (`membership.certificate`), standalone
  printable `pages/membership-certificate.blade.php`. Max-fidelity approach: the
  ENTIRE design is the base image (`cert-full.png`), with a LIVE QR overlaid on
  the baked one and — when the logged-in user owns a business — parchment-colored
  patches overlaying the demo name/number/code/dates with real values (number
  `GVN-{year}-{7 digits}` and code both derived deterministically from
  md5('gvn-cert-'+business id); dates = business created_at → +1 year). Without
  a business the design renders untouched. Print/download buttons call
  window.print() (@page landscape, chrome hidden). Reachable from the seller
  dashboard header profile dropdown ("Mon certificat d'adhésion").

Remember SetResolution(96,96) before GDI+ crops if new designs arrive.

## The replication process (repeat for each new page)

1. Read the PNG with the Read tool; note pixel dimensions (most are 1536×1024 —
   treat design px as CSS px at a 1536 viewport; content column `max-w-[1472px]`
   for gallery pages, `max-w-[1280px]` for marketing pages).
2. Zoom sections (System.Drawing crop + nearest-neighbor upscale into the
   scratchpad) to transcribe EVERY text string and measure spacing at native scale.
3. Sample flat-fill colors with `Bitmap.GetPixel` (9-point grids per element —
   single points often hit text/anti-aliasing).
4. Crop ALL custom artwork from the PNG into `public/images/landing/`
   (naming: `about-*.png`, `partner-*.png`, `biz-*.png`, `event-*.png`,
   `auth-*.png`, `cat-*.png`). Per the fidelity mandate, crop illustrated icons
   too — don't substitute lucide for custom drawings.
   - If baked-in text must be hidden, cover with a gradient fill and clone-patch
     from adjacent clean rows (see hero-bg.png history in `90c3735`).
   - When tiling a crop as a pattern, trim ~10px off the crop edges or the tile
     seam shows as a dark band (fixed in `3b446ba`).
5. Build the page as a **standalone blade view** (own `<head>` with the Tailwind
   Play config block, include the shared partials — do NOT extend
   `layouts/app.blade.php`; dashboard/gallery legacy pages still use that layout
   and must not be disturbed).
6. Verify with the preview server (`.claude/launch.json`: `laravel` on port 8321,
   `laravel-alt` on 8322, `laravel-alt2` on 8323 for when other sessions hold
   the lower ports; PHP at
   `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`). Known quirks:
   the screenshot tool lags one action behind or times out — restart the preview
   if stuck; preview screenshots render small (structure checks only) — use
   preview_eval/snapshot/inspect for text and CSS verification; preview_click on
   a submit button may not fire the native submit — use `form.requestSubmit()`
   via preview_eval.
7. Check FR (`?lang=fr`) and EN (`?lang=en`), mobile (375px, no horizontal
   overflow, bottom nav present).
8. Run `php artisan test` (34 tests must stay green), then commit
   (PowerShell 5.1: multiline commit messages need `git commit -F <file>`).

## Shared design system

- **Fonts** (all local, no CDN): Playfair Display (serif display), Poppins (UI
  sans), Inter (legacy layouts). Loaded via `public/vendor/fonts.css` +
  `public/vendor/fonts/*.woff2`.
- **Offline rule**: never reference a CDN. Tailwind Play runtime is
  `public/vendor/tailwindcss.js`, lucide `public/vendor/lucide.min.js`, qrcodejs
  `public/vendor/qrcode.min.js`. CSP in
  `app/Http/Middleware/SecurityHeaders.php` still allows the old CDNs; local is 'self'.
- **Palette** (sampled from the designs):
  - Cream page bg `#F8F3ED` (landing) / `#F7F2EC` (about) / `#F6F4F2` (auth) /
    `#FEFDFC` (categories); card bg `#FBF9F3`/`#FDFBF7`/`#F9F6F1`; borders `#E7E1D4`
  - Deep greens: buttons `#164C28`, auth submit `#0A331C`, header button `#0A3020`,
    auth brand panel `#091C10`, sidebar header `#0A2C1D`, region-card button `#0E3022`,
    about/stats bands `#0E1D13`, landing mission strip `#0E261C`,
    landing footer `#0D0F0D`, auth footer `#0B2014`, categories footer `#0B2C1E`
  - Golds: bright text `#E5A82E`/`#D9A439`, mid `#C9942E`, sidebar active bar `#D9991F`,
    icon `#D79326`/`#D49B2D`, subscribe button `#E9A830`, CTA fill `#E0A52F` with
    dark text `#4A3A0B`/`#3A2E08`
  - Categories tricolor: green `#034226`, red `#B70415`, gold grad `#F2B01C→#E6C89A`,
    stars `#F5C33B` (landing/about tricolor: `#125527`/`#C10913`/`#EBAB1A`)
  - Accents: stat red `#A51717`, value red `#B42025`, icon brown `#4A2E1E`,
    muted `#8A857A`, sage `#A8B8AC`, trust strip bg `#F6F6EF`,
    icon circle bg `#F7F1E9` with gold arc `#E9C989`
- **Icons**: lucide via `data-lucide` for generic UI icons only; custom illustrated
  icons are cropped from the PNGs. Brand/social icons are inline SVG paths
  (`$socialIcons`: Facebook, Instagram, LinkedIn, YouTube, X — duplicated per page/partial).
- **Bilingual**: every string has FR/EN variants keyed off `$isFr`; `?lang=` +
  30-day cookie (terms/privacy routes still read the cookie only — same fix as
  `3b446ba` applies if those pages get rebuilt).

## Data seeded for the designs (admin-editable, not fixtures)

- **Industries** (see categories notes): 8 new categories seeded 2026-07-02 to make
  the design's category links functional. Pre-existing 5: `artisanat`, `aquaculture`,
  `agriculture`, `textile-mode`, `agroalimentaire`.
- **Events**: `festival-national-du-textile` (15–18 Jul 2026, Bafoussam),
  `expo-artisanat-jeunesse` (5–8 Aug 2026, Douala), `semaine-nationale-du-bois`
  (22–27 Sep 2026, Ebolowa). Design showed 2025 dates; rolled forward so they display.
- **Partners** (sort_order 1–9): MINCOMMERCE, MINAC, UNESCO, ITC, CEPII Cameroun,
  OAPI, Banque Africaine de Développement, AFD, Union Européenne. Logo tiles are
  static crops mapped by `name_fr` in the home view (`$partnerTiles`); the DB `logo`
  column is empty. Pre-existing MINEPIA/OIDAC moved to sort_order 10/11.
- `FrontendController::home()` picks the spotlight event by slug prefix `siac%`,
  lists other upcoming events, and passes 9 partners + a regions count.

## Guardrails honored so far (keep honoring them)

- No existing route, dashboard, flow, or button was removed or renamed.
- `layouts/app.blade.php` / `layouts/dashboard.blade.php` changed only in their
  `<head>` (CDN → local vendor files).
- Every replica link maps onto a real route (`businesses.index` with `?industry=`,
  `industries.index`, `events.index`, `partners.index`, `gallery.search`,
  `support.index`, `terms`, `privacy`) — no dead handlers, no 404s.
- Mobile bottom nav is replicated inside each standalone page so small screens keep it.
- When design content conflicts with live data, the design wins on DISPLAY
  (fidelity mandate above) while the underlying routes/flows stay real.
