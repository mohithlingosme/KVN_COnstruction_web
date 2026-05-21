import { PageHero } from "@/components/common/page-hero";
import { buildMetadata } from "@/lib/metadata";
import { Card, CardContent } from "@/components/ui/card";

export const metadata = buildMetadata({
  title: "Terms and Conditions",
  path: "/terms-and-conditions",
  description: "Terms and conditions governing the KVN Construction website, dashboards, bookings, and digital services.",
});

export default function TermsPage() {
  return (
    <>
      <PageHero
        eyebrow="Policy"
        title="Terms & Conditions"
        description="Use of the public website, CRM forms, dashboards, estimator outputs, and appointment tools is subject to these terms."
      />
      <section className="container py-16">
        <Card>
          <CardContent className="space-y-5 p-8 text-muted-foreground">
            <p>Estimator outputs are indicative and not binding quotations. Final pricing depends on drawings, engineering, approvals, and scope definition.</p>
            <p>Appointments, callbacks, and digital communications submitted through the platform may trigger automated workflows such as calendar booking, email, and WhatsApp notifications.</p>
            <p>Production launch should replace this scaffold text with counsel-approved terms specific to the operating entity and payment workflows.</p>
          </CardContent>
        </Card>
      </section>
    </>
  );
}
