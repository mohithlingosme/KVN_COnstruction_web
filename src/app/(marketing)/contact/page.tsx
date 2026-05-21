import { LeadForm } from "@/components/forms/lead-form";
import { PageHero } from "@/components/common/page-hero";
import { buildMetadata } from "@/lib/metadata";
import { Card, CardContent } from "@/components/ui/card";
import { siteConfig } from "@/lib/constants";

export const metadata = buildMetadata({
  title: "Contact",
  path: "/contact",
  description: "Contact KVN Construction for consultations, project discussions, documentation support, and financing guidance.",
});

export default function ContactPage() {
  return (
    <>
      <PageHero
        eyebrow="Contact"
        title="Start the project conversation with the right context."
        description="Use the lead form for project discussions, site visits, budgeting, documentation, or finance assistance."
      />
      <section className="container py-16">
        <div className="grid gap-6 lg:grid-cols-[0.9fr,1.1fr]">
          <Card>
            <CardContent className="space-y-4 p-8">
              <h2 className="font-display text-4xl">Bengaluru Office</h2>
              <p className="text-muted-foreground">{siteConfig.address}</p>
              <p className="text-muted-foreground">{siteConfig.phone}</p>
              <p className="text-muted-foreground">{siteConfig.email}</p>
              <div className="h-[320px] rounded-[1.5rem] bg-muted p-6 text-sm text-muted-foreground">
                Google Maps embed placeholder. Replace this block with your production embed URL.
              </div>
            </CardContent>
          </Card>
          <LeadForm />
        </div>
      </section>
    </>
  );
}
