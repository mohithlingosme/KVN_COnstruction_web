"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

import { cn } from "@/lib/utils";

export function DashboardSidebar({
  nav,
  title,
}: {
  nav: { title: string; href: string }[];
  title: string;
}) {
  const pathname = usePathname();

  return (
    <aside className="rounded-[2rem] border border-border/70 bg-card/80 p-5 shadow-glass">
      <p className="px-3 text-xs uppercase tracking-[0.28em] text-muted-foreground">
        {title}
      </p>
      <nav className="mt-5 grid gap-1">
        {nav.map((item) => (
          <Link
            key={item.href}
            href={item.href}
            className={cn(
              "rounded-2xl px-3 py-3 text-sm font-medium transition",
              pathname === item.href
                ? "bg-brand-clay text-white"
                : "text-muted-foreground hover:bg-muted hover:text-foreground",
            )}
          >
            {item.title}
          </Link>
        ))}
      </nav>
    </aside>
  );
}
