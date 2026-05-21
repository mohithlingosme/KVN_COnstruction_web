import { Badge } from "@/components/ui/badge";

export function SectionHeading({
  badge,
  title,
  description,
}: {
  badge: string;
  title: string;
  description: string;
}) {
  return (
    <div className="max-w-2xl space-y-4">
      <Badge>{badge}</Badge>
      <h2 className="font-display text-4xl tracking-tight md:text-5xl">{title}</h2>
      <p className="text-lg text-muted-foreground">{description}</p>
    </div>
  );
}
