"use client";

import { Button } from "@/components/ui/button";

export default function AdminError({
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <div className="rounded-[2rem] border border-border bg-card p-8 shadow-soft">
      <h2 className="font-display text-4xl">Admin module failed to render.</h2>
      <p className="mt-3 text-muted-foreground">
        Retry the route. If the error persists, inspect the dashboard module or API dependency.
      </p>
      <Button className="mt-6" onClick={reset}>
        Retry
      </Button>
    </div>
  );
}
