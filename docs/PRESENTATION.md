# Presentation quick sheet (2026-07-06)

## Access from phones/tablets (same Wi-Fi)
- URL: **http://192.168.1.33:8080**  (server: `php artisan serve --host=0.0.0.0 --port=8080`, started hidden)
- If the PC's IP changes: `ipconfig` → use the IPv4 address; restart serve if needed:
  PowerShell: `Start-Process C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -ArgumentList "artisan","serve","--host=0.0.0.0","--port=8080" -WorkingDirectory C:\laragon\www\artisanatcameroun -WindowStyle Hidden`
- On the PC itself you can keep using http://artisanatcameroun.test

## One-click demo accounts (on /login)
- **Admin** — Administrateur SIARC (full admin + SIARC console)
- **Vendeur** — Paul Nguema, Atelier Nguéma Sculptures (listed in the gallery; receives messages/quotes)
- **Acheteur** — Test Buyer (sends messages/quotes, buyer dashboard)

## Demo scripts
1. **SIARC visitor journey**: /siarc/inscription → register → banner shows badge → "Voir & imprimer mon badge" (QR is scannable) → "Accéder à mon espace" (badge + QR in Mon Espace). Returning: /tableau-de-bord/siarc → email + badge code.
2. **Scan & check-in (admin)**: login Admin → Accréditation → QR Scanner → Saisie manuelle → type the badge code → VALIDATION RÉUSSIE → "Confirmer le check-in". Public verify page then shows "Oui — enregistré".
3. **Gallery message**: login Acheteur → open a business (Atelier Nguéma Sculptures) → Contacter → send. Then login Vendeur → Messages: it's there.
4. **Quote (RFQ)**: Acheteur → /tableau-de-bord/demandes/creer → pick Atelier Nguéma → submit. Vendeur → /tableau-de-bord/devis.
5. **Publish product**: Vendeur → /tableau-de-bord/produits/nouveau → fill → publish → visible at /galerie/produits immediately.
6. **Quick signup**: gallery header → "Inscription rapide" → email+password → straight into dashboard.

## Notes
- QR codes encode the URL of the host that rendered them: for phone scanning, open the badge via http://192.168.1.33:8080/...
- Kiosk screens: /tableau-de-bord/admin/siarc/securite/kiosque-checkin and kiosque-scanner (great on tablets).
