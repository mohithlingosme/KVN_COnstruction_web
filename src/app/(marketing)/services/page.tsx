import { ServiceCard } from "@/components/common/cards";
import { PageHero } from "@/components/common/page-hero";
import { services } from "@/data/site";
import { buildMetadata } from "@/lib/metadata";

export const metadata = buildMetadata({
  title: "Services",
  path: "/services",
  description: "Residential, commercial, interiors, documentation, and managed delivery services for Bengaluru construction clients.",
});

export default function ServicesPage() {
  return (
    <>
      <PageHero
        eyebrow="Services"
        title="Service architecture for construction, interiors, documentation, and support."
        description="Modular service lines that can sell independently and operate together inside the CRM and client portal."
      />
      <section className="container py-16">
        <div className="grid gap-6 lg:grid-cols-2">
          {services.map((service) => (
            <ServiceCard key={service.slug} service={service} />
          ))}
        </div>
      </section>
    </>
  );
}
