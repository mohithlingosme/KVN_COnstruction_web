import { PageHero } from "@/components/common/page-hero";
import { buildMetadata } from "@/lib/metadata";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export const metadata = buildMetadata({
  title: "Documentation Services",
  path: "/documentation-services",
  description: "Plan approvals, permit management, compliance coordination, and digital documentation services.",
});

export default function DocumentationServicesPage() {
  return (
    <>
      <PageHero
        eyebrow="Documentation Services"
        title="Approval and documentation support that reduces hidden delays."
        description="The platform supports plan approvals, permits, compliance workflows, and a centralized document trail."
      />
      <section className="container py-16">
        <div className="grid gap-6 md:grid-cols-2">
          {[
            "Plan approvals and revision tracking",
            "Building permits and NOC support",
            "Secure document storage and retrieval",
            "Status reminders and milestone follow-ups",
          ].map((item) => (
            <Card key={item}>
              <CardHeader>
                <CardTitle>{item}</CardTitle>
              </CardHeader>
              <CardContent className="text-muted-foreground">
                Structured as a service line and backed by the same admin CRM as delivery projects.
              </CardContent>
            </Card>
          ))}
        </div>
      </section>
    </>
  );
}
