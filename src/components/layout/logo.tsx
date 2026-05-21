import Link from "next/link";
import { Building2 } from "lucide-react";

import { cn } from "@/lib/utils";

export function Logo({ className }: { className?: string }) {
  return (
    <Link href="/" className={cn("inline-flex items-center gap-3", className)}>
      <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-clay text-white shadow-soft">
        <Building2 className="h-5 w-5" />
      </span>
      <span>
        <span className="block font-display text-2xl leading-none tracking-wide">
          KVN
        </span>
        <span className="block text-[11px] uppercase tracking-[0.28em] text-muted-foreground">
          Construction
        </span>
      </span>
    </Link>
  );
}
