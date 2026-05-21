import { BellDot } from "lucide-react";

import { Logo } from "@/components/layout/logo";
import { ThemeToggle } from "@/components/layout/theme-toggle";
import { Button } from "@/components/ui/button";

export function DashboardShell({
  sidebar,
  title,
  description,
  children,
}: {
  sidebar: React.ReactNode;
  title: string;
  description: string;
  children: React.ReactNode;
}) {
  return (
    <div className="min-h-screen bg-muted/40">
      <div className="container py-6">
        <div className="mb-6 flex items-center justify-between gap-4 rounded-[2rem] border border-border/70 bg-card/80 p-4 shadow-glass">
          <Logo />
          <div className="flex items-center gap-3">
            <ThemeToggle />
            <Button variant="secondary" size="icon">
              <BellDot className="h-5 w-5" />
            </Button>
          </div>
        </div>
        <div className="grid gap-6 xl:grid-cols-[280px,1fr]">
          {sidebar}
          <div className="space-y-6">
            <div className="rounded-[2rem] border border-border/70 bg-card/80 p-6 shadow-glass">
              <p className="text-sm uppercase tracking-[0.28em] text-muted-foreground">
                Workspace
              </p>
              <h1 className="mt-3 font-display text-4xl">{title}</h1>
              <p className="mt-2 max-w-2xl text-muted-foreground">{description}</p>
            </div>
            {children}
          </div>
        </div>
      </div>
    </div>
  );
}
