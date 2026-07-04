# Preston Daub Node Backend

This folder contains the new Node.js backend scaffold for migrating the existing PHP backend to Supabase.

## What is included

- Express API under `/api`
- Supabase client and service-role client
- Form submission routes for:
  - `/api/forms/contact`
  - `/api/forms/prospera`
  - `/api/forms/mosaic`
  - `/api/forms/sports`
  - `/api/forms/financing`
- Public content routes:
  - `/api/public/team-members`
  - `/api/public/news`
- Admin auth routes:
  - `/api/auth/login`
  - `/api/auth/logout`
  - `/api/auth/me`
- Admin form routes:
  - `/api/admin/forms/poll`
  - `/api/admin/forms/:id`
  - `/api/admin/forms/:id/detail`
  - `/api/admin/forms/actions`
  - `/api/admin/forms/:id/actions`
- SMTP email service via Nodemailer
- Supabase SQL schema in `supabase/schema.sql`

## Setup

1. Copy `.env.example` to `.env`
2. Add your Supabase and SMTP values
3. Install dependencies:

```bash
npm install
```

4. Start the server:

```bash
npm run dev
```

## Frontend route mapping

Replace the current PHP endpoints with these Node endpoints:

- `assets/mail.php` -> `/api/forms/contact`
- `assets/prospera-submit.php` -> `/api/forms/prospera`
- `assets/mosaic-submit.php` -> `/api/forms/mosaic`
- `assets/sports-submit.php` -> `/api/forms/sports`
- `admin/submit-form.php` -> `/api/forms/financing` or `/api/forms/contact` depending on form
- `admin/api-team-members.php` -> `/api/public/team-members`
- `admin/get-published-news.php` -> `/api/public/news`

## Notes

- The current scaffold focuses first on public form handling.
- Public news and team APIs are included.
- Admin auth APIs are included and issue an `admin_token` cookie.
- Admin form APIs are included, but they require an admin JWT in the `admin_token` cookie or `Authorization: Bearer <token>` header.
- Add admin records to the `admin_users` table with `bcrypt` password hashes.
- Financing submissions are stored in `contact_forms.form_data` as JSON so the long application payload is preserved.
