import { ArrowRight } from "lucide-react";
import Link from "next/link";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Reveal } from "@/components/motion/reveal";

type PageHeroProps = {
  eyebrow: string;
  title: string;
  description: string;
  primaryCta?: { label: string; href: string };
  secondaryCta?: { label: string; href: string };
};

export function PageHero({
  eyebrow,
  title,
  description,
  primaryCta,
  secondaryCta,
}: PageHeroProps) {
  return (
    <section className="relative overflow-hidden border-b border-border/50">
      <div className="absolute inset-0 bg-hero-radial opacity-90" />
      <div className="absolute inset-0 grid-fade opacity-30" />
      <div className="container relative py-16 md:py-24">
        <Reveal>
          <div className="max-w-4xl space-y-6">
          <Badge>{eyebrow}</Badge>
          <h1 className="max-w-3xl font-display text-5xl leading-none tracking-tight md:text-7xl">
            {title}
          </h1>
          <p className="max-w-2xl text-lg text-muted-foreground md:text-xl">
            {description}
          </p>
          <div className="flex flex-col gap-3 sm:flex-row">
            {primaryCta ? (
              <Button asChild size="lg">
                <Link href={primaryCta.href}>
                  {primaryCta.label}
                  <ArrowRight className="h-4 w-4" />
                </Link>
              </Button>
            ) : null}
            {secondaryCta ? (
              <Button asChild size="lg" variant="outline">
                <Link href={secondaryCta.href}>{secondaryCta.label}</Link>
              </Button>
            ) : null}
          </div>
          </div>
        </Reveal>
      </div>
    </section>
  );
}
