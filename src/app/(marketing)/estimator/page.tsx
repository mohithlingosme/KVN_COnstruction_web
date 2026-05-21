import { CostEstimatorForm } from "@/components/forms/cost-estimator-form";
import { PageHero } from "@/components/common/page-hero";
import { buildMetadata } from "@/lib/metadata";

export const metadata = buildMetadata({
  title: "Cost Estimator",
  path: "/estimator",
  description: "Smart construction cost estimator for plot size, floors, materials, location, interiors, and timeline guidance.",
});

export default function EstimatorPage() {
  return (
    <>
      <PageHero
        eyebrow="Estimator"
        title="A buyer-friendly estimation tool that also qualifies leads."
        description="Capture plot, scope, finish, and location data, then return budget, timeline, and package guidance."
      />
      <section className="container py-16">
        <CostEstimatorForm />
      </section>
    </>
  );
}
