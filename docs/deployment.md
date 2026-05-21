# Deployment Guide

## 1. Install dependencies

```bash
npm install
```

## 2. Configure environment

1. Copy `.env.example` to `.env.local`
2. Add:
   - Supabase URL and anon key
   - Supabase service role key for privileged server actions
   - Razorpay keys and webhook secret
   - WhatsApp Business API credentials
   - Google service account credentials and calendar id
   - Google Analytics measurement id

## 3. Provision Supabase

1. Create a Supabase project
2. Run `supabase/schema.sql`
3. Run `supabase/seed.sql`
4. Enable:
   - Auth email OTP
   - Storage buckets for `project-documents` and `project-media`
   - Realtime on project update tables if live portal updates are required

## 4. Run locally

```bash
npm run dev
```

## 5. Production deploy

Recommended stack:

1. Vercel for Next.js hosting
2. Supabase for Postgres, Auth, Storage, Realtime
3. Razorpay for payment checkout and webhook settlement
4. Meta WhatsApp Cloud API
5. Google Calendar API via service account

Vercel configuration:

1. Import repository
2. Add all environment variables
3. Set build command to `npm run build`
4. Set output to Next.js default

## 6. Post deploy hardening

1. Turn `NEXT_PUBLIC_ENABLE_DEMO_MODE` to `false`
2. Enforce RLS policies and role mappings
3. Add webhook signature validation
4. Replace placeholder legal copy with approved content
5. Connect a production email adapter
6. Add real signed upload URLs for Supabase Storage
