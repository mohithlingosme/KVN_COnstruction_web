import { PageHero } from "@/components/common/page-hero";
import { SectionHeading } from "@/components/common/section-heading";
import { Card, CardContent } from "@/components/ui/card";
import { buildMetadata } from "@/lib/metadata";

export const metadata = buildMetadata({
  title: "About",
  path: "/about",
  description: "About KVN Construction, our delivery model, operating values, and Bengaluru project expertise.",
});

export default function AboutPage() {
  return (
    <>
      <PageHero
        eyebrow="About KVN"
        title="Construction delivery shaped by clarity, not chaos."
        description="KVN Construction combines engineering discipline, premium design sensitivity, and client communication systems that reduce friction from inquiry to handover."
      />
      <section className="container py-16">
        <SectionHeading
          badge="Operating Model"
          title="Designed for modern owners, developers, and NRI clients."
          description="Our approach combines site execution with CRM, documentation, finance coordination, and digital reporting so every stakeholder sees the same truth."
        />
        <div className="mt-10 grid gap-6 md:grid-cols-3">
          {[
            "Transparent scope, BOQ, and milestone logic",
            "Project dashboards with weekly updates and documents",
            "Approval, finance, and post-handover support layers",
          ].map((item) => (
            <Card key={item}>
              <CardContent className="p-6 text-lg text-muted-foreground">{item}</CardContent>
            </Card>
          ))}
        </div>
      </section>
    </>
  );
}
