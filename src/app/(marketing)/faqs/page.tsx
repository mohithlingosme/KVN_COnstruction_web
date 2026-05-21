import { PageHero } from "@/components/common/page-hero";
import { faqs } from "@/data/site";
import { buildMetadata } from "@/lib/metadata";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export const metadata = buildMetadata({
  title: "FAQs",
  path: "/faqs",
  description: "Frequently asked questions about pricing, construction timelines, approvals, documentation, and payments.",
});

export default function FaqsPage() {
  return (
    <>
      <PageHero
        eyebrow="FAQs"
        title="The questions serious buyers ask before committing."
        description="Pricing, scope, communication, documentation, billing, and delivery process FAQs."
      />
      <section className="container py-16">
        <div className="grid gap-6">
          {faqs.map((faq) => (
            <Card key={faq.question}>
              <CardHeader>
                <p className="text-xs uppercase tracking-[0.2em] text-brand-clay">{faq.category}</p>
                <CardTitle>{faq.question}</CardTitle>
              </CardHeader>
              <CardContent className="text-muted-foreground">{faq.answer}</CardContent>
            </Card>
          ))}
        </div>
      </section>
    </>
  );
}
