"use client";

import { Button } from "@/components/ui/button";

export default function ClientError({
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <div className="rounded-[2rem] border border-border bg-card p-8 shadow-soft">
      <h2 className="font-display text-4xl">Client portal failed to render.</h2>
      <p className="mt-3 text-muted-foreground">
        Retry the view or inspect the affected data source.
      </p>
      <Button className="mt-6" onClick={reset}>
        Retry
      </Button>
    </div>
  );
}
