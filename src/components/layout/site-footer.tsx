import Link from "next/link";

import { appNav, siteConfig } from "@/lib/constants";
import { Logo } from "@/components/layout/logo";

export function SiteFooter() {
  return (
    <footer className="border-t border-border/70 bg-card/70">
      <div className="container grid gap-10 py-12 lg:grid-cols-[1.4fr,1fr,1fr,1fr]">
        <div className="space-y-4">
          <Logo />
          <p className="max-w-sm text-sm text-muted-foreground">
            Premium residential and commercial construction management for Bengaluru clients who want clarity, execution discipline, and a better delivery experience.
          </p>
        </div>
        <div className="space-y-3">
          <p className="text-sm font-semibold uppercase tracking-[0.24em] text-muted-foreground">
            Navigation
          </p>
          {appNav.public.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className="block text-sm text-muted-foreground transition hover:text-brand-clay"
            >
              {item.title}
            </Link>
          ))}
        </div>
        <div className="space-y-3">
          <p className="text-sm font-semibold uppercase tracking-[0.24em] text-muted-foreground">
            Services
          </p>
          <Link href="/financial-services" className="block text-sm text-muted-foreground hover:text-brand-clay">
            Financial Services
          </Link>
          <Link href="/documentation-services" className="block text-sm text-muted-foreground hover:text-brand-clay">
            Documentation Services
          </Link>
          <Link href="/estimator" className="block text-sm text-muted-foreground hover:text-brand-clay">
            Cost Estimator
          </Link>
        </div>
        <div className="space-y-3">
          <p className="text-sm font-semibold uppercase tracking-[0.24em] text-muted-foreground">
            Contact
          </p>
          <p className="text-sm text-muted-foreground">{siteConfig.address}</p>
          <a href={`tel:${siteConfig.phone}`} className="block text-sm text-muted-foreground hover:text-brand-clay">
            {siteConfig.phone}
          </a>
          <a href={`mailto:${siteConfig.email}`} className="block text-sm text-muted-foreground hover:text-brand-clay">
            {siteConfig.email}
          </a>
        </div>
      </div>
      <div className="border-t border-border/70">
        <div className="container flex flex-col gap-3 py-5 text-sm text-muted-foreground md:flex-row md:items-center md:justify-between">
          <p>© 2026 {siteConfig.legalName}. All rights reserved.</p>
          <div className="flex gap-4">
            <Link href="/privacy-policy">Privacy Policy</Link>
            <Link href="/terms-and-conditions">Terms & Conditions</Link>
          </div>
        </div>
      </div>
    </footer>
  );
}
