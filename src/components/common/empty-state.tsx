import { Construction } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";

export function EmptyState({
  title,
  description,
  ctaLabel,
}: {
  title: string;
  description: string;
  ctaLabel?: string;
}) {
  return (
    <Card>
      <CardContent className="flex flex-col items-center justify-center gap-4 py-16 text-center">
        <div className="rounded-full bg-brand-sand/10 p-4 text-brand-clay">
          <Construction className="h-8 w-8" />
        </div>
        <div className="space-y-2">
          <h3 className="font-display text-3xl">{title}</h3>
          <p className="max-w-md text-muted-foreground">{description}</p>
        </div>
        {ctaLabel ? <Button variant="outline">{ctaLabel}</Button> : null}
      </CardContent>
    </Card>
  );
}
