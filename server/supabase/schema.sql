create extension if not exists pgcrypto;

create table if not exists public.contact_forms (
  id uuid primary key default gen_random_uuid(),
  form_type text not null,
  name text,
  email text not null,
  phone text,
  company text,
  organization text,
  organization_type text,
  service text,
  job_title text,
  interests jsonb default '[]'::jsonb,
  goals_challenges text,
  message text,
  priority text,
  notes text,
  status text not null default 'new',
  ip_address text,
  user_agent text,
  form_data jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default timezone('utc', now()),
  updated_at timestamptz not null default timezone('utc', now())
);

create table if not exists public.team_members (
  id uuid primary key default gen_random_uuid(),
  full_name text not null,
  role text,
  bio text,
  image_url text,
  linkedin_url text,
  sort_order integer not null default 0,
  is_active boolean not null default true,
  created_at timestamptz not null default timezone('utc', now())
);

create table if not exists public.settings (
  id uuid primary key default gen_random_uuid(),
  setting_key text not null unique,
  setting_value text not null default '',
  created_at timestamptz not null default timezone('utc', now()),
  updated_at timestamptz not null default timezone('utc', now())
);

create table if not exists public.admin_users (
  id uuid primary key default gen_random_uuid(),
  email text not null unique,
  password_hash text not null,
  name text not null,
  role text not null default 'admin',
  is_active boolean not null default true,
  created_at timestamptz not null default timezone('utc', now()),
  updated_at timestamptz not null default timezone('utc', now())
);

create table if not exists public.news_categories (
  id uuid primary key default gen_random_uuid(),
  name text not null unique,
  slug text not null unique,
  created_at timestamptz not null default timezone('utc', now())
);

create table if not exists public.news (
  id uuid primary key default gen_random_uuid(),
  category_id uuid references public.news_categories(id) on delete set null,
  title text not null,
  slug text not null unique,
  excerpt text,
  content text,
  featured_image_url text,
  status text not null default 'draft',
  view_count integer not null default 0,
  published_at timestamptz,
  created_at timestamptz not null default timezone('utc', now()),
  updated_at timestamptz not null default timezone('utc', now())
);

create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
  new.updated_at = timezone('utc', now());
  return new;
end;
$$;

drop trigger if exists contact_forms_set_updated_at on public.contact_forms;
create trigger contact_forms_set_updated_at
before update on public.contact_forms
for each row
execute procedure public.set_updated_at();

drop trigger if exists news_set_updated_at on public.news;
create trigger news_set_updated_at
before update on public.news
for each row
execute procedure public.set_updated_at();

alter table public.contact_forms enable row level security;
alter table public.team_members enable row level security;
alter table public.news_categories enable row level security;
alter table public.news enable row level security;
alter table public.settings enable row level security;
alter table public.admin_users enable row level security;

drop policy if exists "Public can insert contact forms" on public.contact_forms;
create policy "Public can insert contact forms"
on public.contact_forms
for insert
to anon, authenticated
with check (true);

drop policy if exists "Admins can read contact forms" on public.contact_forms;
create policy "Admins can read contact forms"
on public.contact_forms
for select
to authenticated
using (true);

drop policy if exists "Public can view active team members" on public.team_members;
create policy "Public can view active team members"
on public.team_members
for select
to anon, authenticated
using (is_active = true);

drop policy if exists "Public can view published news" on public.news;
create policy "Public can view published news"
on public.news
for select
to anon, authenticated
using (status = 'published');

drop policy if exists "Public can view categories" on public.news_categories;
create policy "Public can view categories"
on public.news_categories
for select
to anon, authenticated
using (true);

drop policy if exists "Public can view settings" on public.settings;
create policy "Public can view settings"
on public.settings
for select
to anon, authenticated
using (true);

drop policy if exists "Admins can view admin users" on public.admin_users;
create policy "Admins can view admin users"
on public.admin_users
for select
to authenticated
using (true);
