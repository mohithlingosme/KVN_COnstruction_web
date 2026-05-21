"use client";

import { useEffect } from "react";

import { Button } from "@/components/ui/button";

export default function GlobalError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <html>
      <body className="flex min-h-screen items-center justify-center bg-background px-6">
        <div className="max-w-lg rounded-[2rem] border border-border bg-card p-8 text-center shadow-soft">
          <p className="text-sm uppercase tracking-[0.28em] text-brand-clay">System Recovery</p>
          <h1 className="mt-4 font-display text-4xl">Something interrupted the build flow.</h1>
          <p className="mt-4 text-muted-foreground">
            The page threw an unexpected error. Retry the render or inspect the server logs for the originating module.
          </p>
          <div className="mt-6 flex justify-center">
            <Button onClick={reset}>Retry</Button>
          </div>
        </div>
      </body>
    </html>
  );
}
