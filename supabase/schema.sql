create extension if not exists "pgcrypto";

create type public.user_role as enum ('admin', 'manager', 'site_engineer', 'client');
create type public.lead_stage as enum ('new', 'qualified', 'site_visit', 'proposal', 'won', 'lost');
create type public.project_status as enum ('planning', 'execution', 'finishing', 'handover', 'completed');
create type public.payment_status as enum ('paid', 'due', 'scheduled', 'overdue');

create table if not exists public.profiles (
  id uuid primary key references auth.users(id) on delete cascade,
  full_name text not null,
  email text unique not null,
  phone text,
  role public.user_role not null default 'client',
  avatar_url text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.services (
  id uuid primary key default gen_random_uuid(),
  slug text unique not null,
  title text not null,
  summary text not null,
  description text not null,
  created_at timestamptz not null default now()
);

create table if not exists public.leads (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  email text not null,
  phone text not null,
  requirement text not null,
  budget_range text,
  stage public.lead_stage not null default 'new',
  source text default 'website',
  owner_id uuid references public.profiles(id),
  created_at timestamptz not null default now()
);

create table if not exists public.appointments (
  id uuid primary key default gen_random_uuid(),
  lead_id uuid references public.leads(id) on delete set null,
  client_id uuid references public.profiles(id) on delete set null,
  service_id uuid references public.services(id) on delete set null,
  title text not null,
  notes text,
  start_at timestamptz not null,
  end_at timestamptz not null,
  created_at timestamptz not null default now()
);

create table if not exists public.callback_requests (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  phone text not null,
  preferred_slot text not null,
  status text not null default 'new',
  created_at timestamptz not null default now()
);

create table if not exists public.projects (
  id uuid primary key default gen_random_uuid(),
  slug text unique not null,
  client_id uuid references public.profiles(id) on delete set null,
  title text not null,
  category text not null,
  location text not null,
  budget numeric(14,2),
  area_sqft numeric(12,2),
  start_date date,
  estimated_end_date date,
  progress_percent integer not null default 0,
  status public.project_status not null default 'planning',
  hero_image text,
  overview text,
  created_at timestamptz not null default now()
);

create table if not exists public.project_milestones (
  id uuid primary key default gen_random_uuid(),
  project_id uuid not null references public.projects(id) on delete cascade,
  title text not null,
  description text,
  due_date date,
  status text not null default 'upcoming',
  created_at timestamptz not null default now()
);

create table if not exists public.project_updates (
  id uuid primary key default gen_random_uuid(),
  project_id uuid not null references public.projects(id) on delete cascade,
  title text not null,
  note text not null,
  progress_percent integer,
  created_by uuid references public.profiles(id),
  created_at timestamptz not null default now()
);

create table if not exists public.material_tracking (
  id uuid primary key default gen_random_uuid(),
  project_id uuid not null references public.projects(id) on delete cascade,
  material_name text not null,
  vendor_name text,
  ordered_qty numeric(12,2),
  used_qty numeric(12,2),
  status text not null default 'planned',
  created_at timestamptz not null default now()
);

create table if not exists public.documents (
  id uuid primary key default gen_random_uuid(),
  project_id uuid references public.projects(id) on delete cascade,
  uploaded_by uuid references public.profiles(id),
  name text not null,
  bucket text not null,
  path text not null,
  category text not null,
  is_client_visible boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.blog_categories (
  id uuid primary key default gen_random_uuid(),
  name text unique not null,
  slug text unique not null
);

create table if not exists public.blog_posts (
  id uuid primary key default gen_random_uuid(),
  category_id uuid references public.blog_categories(id) on delete set null,
  slug text unique not null,
  title text not null,
  excerpt text not null,
  cover_image text,
  content jsonb not null default '[]'::jsonb,
  seo_title text,
  seo_description text,
  published_at timestamptz,
  is_published boolean not null default false,
  created_at timestamptz not null default now()
);

create table if not exists public.testimonials (
  id uuid primary key default gen_random_uuid(),
  project_id uuid references public.projects(id) on delete set null,
  client_name text not null,
  client_role text,
  quote text not null,
  rating integer not null check (rating between 1 and 5),
  is_published boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.faqs (
  id uuid primary key default gen_random_uuid(),
  category text not null,
  question text not null,
  answer text not null,
  sort_order integer not null default 0,
  is_published boolean not null default true
);

create table if not exists public.packages (
  id uuid primary key default gen_random_uuid(),
  slug text unique not null,
  title text not null,
  price_per_sqft numeric(10,2) not null,
  blurb text not null,
  features jsonb not null default '[]'::jsonb,
  target_audience text
);

create table if not exists public.payments (
  id uuid primary key default gen_random_uuid(),
  project_id uuid references public.projects(id) on delete set null,
  client_id uuid references public.profiles(id) on delete set null,
  invoice_number text unique not null,
  amount numeric(14,2) not null,
  due_date date,
  paid_at timestamptz,
  status public.payment_status not null default 'scheduled',
  razorpay_order_id text,
  razorpay_payment_id text,
  created_at timestamptz not null default now()
);

create table if not exists public.notifications (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references public.profiles(id) on delete cascade,
  title text not null,
  body text not null,
  channel text not null,
  is_read boolean not null default false,
  created_at timestamptz not null default now()
);

create table if not exists public.whatsapp_logs (
  id uuid primary key default gen_random_uuid(),
  recipient text not null,
  template_name text,
  payload jsonb not null default '{}'::jsonb,
  status text not null default 'queued',
  created_at timestamptz not null default now()
);

alter table public.profiles enable row level security;
alter table public.projects enable row level security;
alter table public.project_updates enable row level security;
alter table public.project_milestones enable row level security;
alter table public.documents enable row level security;
alter table public.payments enable row level security;
alter table public.notifications enable row level security;

create policy "profiles_self_view" on public.profiles
  for select using (auth.uid() = id);

create policy "projects_client_or_staff_view" on public.projects
  for select using (
    auth.uid() = client_id
    or exists (
      select 1 from public.profiles
      where profiles.id = auth.uid()
      and profiles.role in ('admin', 'manager', 'site_engineer')
    )
  );

create policy "project_updates_client_or_staff_view" on public.project_updates
  for select using (
    exists (
      select 1 from public.projects
      where projects.id = project_updates.project_id
      and (
        projects.client_id = auth.uid()
        or exists (
          select 1 from public.profiles
          where profiles.id = auth.uid()
          and profiles.role in ('admin', 'manager', 'site_engineer')
        )
      )
    )
  );

create policy "project_milestones_client_or_staff_view" on public.project_milestones
  for select using (
    exists (
      select 1 from public.projects
      where projects.id = project_milestones.project_id
      and (
        projects.client_id = auth.uid()
        or exists (
          select 1 from public.profiles
          where profiles.id = auth.uid()
          and profiles.role in ('admin', 'manager', 'site_engineer')
        )
      )
    )
  );

create policy "documents_client_or_staff_view" on public.documents
  for select using (
    is_client_visible = true
    and exists (
      select 1 from public.projects
      where projects.id = documents.project_id
      and (
        projects.client_id = auth.uid()
        or exists (
          select 1 from public.profiles
          where profiles.id = auth.uid()
          and profiles.role in ('admin', 'manager', 'site_engineer')
        )
      )
    )
  );

create policy "payments_client_or_staff_view" on public.payments
  for select using (
    client_id = auth.uid()
    or exists (
      select 1 from public.profiles
      where profiles.id = auth.uid()
      and profiles.role in ('admin', 'manager')
    )
  );

create policy "notifications_owner_view" on public.notifications
  for select using (user_id = auth.uid());
