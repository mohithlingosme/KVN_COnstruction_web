import { Check } from "lucide-react";

import { PageHero } from "@/components/common/page-hero";
import { packages } from "@/data/site";
import { formatCurrency } from "@/lib/utils";
import { buildMetadata } from "@/lib/metadata";
import { Card, CardContent } from "@/components/ui/card";

export const metadata = buildMetadata({
  title: "Pricing",
  path: "/pricing",
  description: "Transparent pricing overview for KVN Construction packages and planning ranges.",
});

export default function PricingPage() {
  return (
    <>
      <PageHero
        eyebrow="Pricing"
        title="Indicative pricing with premium transparency."
        description="Real construction pricing depends on design, approvals, and site constraints. This page is structured to set expectations correctly."
      />
      <section className="container py-16">
        <div className="grid gap-6">
          {packages.map((item) => (
            <Card key={item.slug}>
              <CardContent className="grid gap-6 p-8 lg:grid-cols-[0.8fr,1.2fr] lg:items-center">
                <div>
                  <p className="font-display text-4xl">{item.title}</p>
                  <p className="mt-2 text-muted-foreground">{item.targetAudience}</p>
                  <p className="mt-4 font-display text-5xl">{formatCurrency(item.pricePerSqft)}</p>
                  <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                    per sqft
                  </p>
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                  {item.features.map((feature) => (
                    <div key={feature} className="inline-flex items-center gap-3 rounded-2xl bg-muted/60 px-4 py-3">
                      <Check className="h-4 w-4 text-brand-clay" />
                      <span className="text-sm text-muted-foreground">{feature}</span>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>
    </>
  );
}
