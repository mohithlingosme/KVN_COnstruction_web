import { PageHero } from "@/components/common/page-hero";
import { buildMetadata } from "@/lib/metadata";
import { Card, CardContent } from "@/components/ui/card";

export const metadata = buildMetadata({
  title: "Privacy Policy",
  path: "/privacy-policy",
  description: "Privacy policy for KVN Construction website, CRM forms, dashboard data, and client communication records.",
});

export default function PrivacyPolicyPage() {
  return (
    <>
      <PageHero
        eyebrow="Policy"
        title="Privacy Policy"
        description="Covers how inquiry data, project records, documents, and communication logs are handled across the platform."
      />
      <section className="container py-16">
        <Card>
          <CardContent className="space-y-5 p-8 text-muted-foreground">
            <p>We collect form data, project data, and communication data necessary to provide construction consultation, delivery, documentation, and support services.</p>
            <p>Client records, project updates, invoices, and uploaded documents are intended to be stored through secure Supabase services and access-controlled dashboards.</p>
            <p>Production deployment should add a final legal review tailored to the company’s actual storage, consent, and retention policies.</p>
          </CardContent>
        </Card>
      </section>
    </>
  );
}
