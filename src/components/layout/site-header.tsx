"use client";

import Link from "next/link";
import { Menu, Phone, X } from "lucide-react";
import { usePathname } from "next/navigation";

import { appNav, siteConfig } from "@/lib/constants";
import { cn } from "@/lib/utils";
import { useUIStore } from "@/store/ui-store";
import { Button } from "@/components/ui/button";
import { Logo } from "@/components/layout/logo";
import { ThemeToggle } from "@/components/layout/theme-toggle";

export function SiteHeader() {
  const pathname = usePathname();
  const { mobileNavOpen, setMobileNavOpen } = useUIStore();

  return (
    <header className="sticky top-0 z-50 border-b border-border/60 bg-background/85 backdrop-blur-xl">
      <div className="container flex h-20 items-center justify-between gap-4">
        <Logo />
        <nav className="hidden items-center gap-6 lg:flex">
          {appNav.public.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                "text-sm font-medium transition hover:text-brand-clay",
                pathname === item.href ? "text-brand-clay" : "text-muted-foreground",
              )}
            >
              {item.title}
            </Link>
          ))}
        </nav>
        <div className="hidden items-center gap-3 lg:flex">
          <a
            href={`tel:${siteConfig.phone.replace(/\s+/g, "")}`}
            className="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground transition hover:text-brand-clay"
          >
            <Phone className="h-4 w-4" />
            {siteConfig.phone}
          </a>
          <ThemeToggle />
          <Button asChild>
            <Link href="/booking">Book Consultation</Link>
          </Button>
        </div>
        <div className="flex items-center gap-2 lg:hidden">
          <ThemeToggle />
          <Button
            variant="secondary"
            size="icon"
            onClick={() => setMobileNavOpen(!mobileNavOpen)}
            aria-label="Toggle navigation"
          >
            {mobileNavOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
          </Button>
        </div>
      </div>
      {mobileNavOpen ? (
        <div className="border-t border-border/60 bg-background/95 lg:hidden">
          <div className="container flex flex-col gap-4 py-5">
            {appNav.public.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className="text-sm font-medium text-muted-foreground"
                onClick={() => setMobileNavOpen(false)}
              >
                {item.title}
              </Link>
            ))}
            <Button asChild className="w-full">
              <Link href="/booking">Book Consultation</Link>
            </Button>
          </div>
        </div>
      ) : null}
    </header>
  );
}
