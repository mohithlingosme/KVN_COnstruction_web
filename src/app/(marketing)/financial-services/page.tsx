import { PageHero } from "@/components/common/page-hero";
import { buildMetadata } from "@/lib/metadata";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export const metadata = buildMetadata({
  title: "Financial Services",
  path: "/financial-services",
  description: "Home loan assistance, EMI planning, financial consultation, and bank support for construction clients.",
});

export default function FinancialServicesPage() {
  return (
    <>
      <PageHero
        eyebrow="Financial Services"
        title="Funding support integrated into the construction journey."
        description="Loan assistance, EMI planning, bank coordination, and financial consultation services tailored for residential and commercial clients."
      />
      <section className="container py-16">
        <div className="grid gap-6 md:grid-cols-2">
          {[
            "Home loan assistance and lender coordination",
            "EMI planning and stage-wise cash flow mapping",
            "Bank partnership and document readiness support",
            "Loan eligibility guidance for first-time builders",
          ].map((item) => (
            <Card key={item}>
              <CardHeader>
                <CardTitle>{item}</CardTitle>
              </CardHeader>
              <CardContent className="text-muted-foreground">
                Built to reduce financing friction during pre-construction and execution.
              </CardContent>
            </Card>
          ))}
        </div>
      </section>
    </>
  );
}
