import Link from "next/link";

import { Button } from "@/components/ui/button";

export default function NotFound() {
  return (
    <div className="flex min-h-screen items-center justify-center px-6">
      <div className="max-w-xl rounded-[2rem] border border-border bg-card p-10 text-center shadow-soft">
        <p className="text-sm uppercase tracking-[0.28em] text-brand-clay">404</p>
        <h1 className="mt-4 font-display text-5xl">Page not found</h1>
        <p className="mt-4 text-muted-foreground">
          The requested route does not exist in this workspace.
        </p>
        <div className="mt-6">
          <Button asChild>
            <Link href="/">Return home</Link>
          </Button>
        </div>
      </div>
    </div>
  );
}
