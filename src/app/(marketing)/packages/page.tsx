import { PackageCard } from "@/components/common/cards";
import { PageHero } from "@/components/common/page-hero";
import { packages } from "@/data/site";
import { buildMetadata } from "@/lib/metadata";

export const metadata = buildMetadata({
  title: "Packages",
  path: "/packages",
  description: "Construction and turnkey package tiers for Bengaluru residential and commercial projects.",
});

export default function PackagesPage() {
  return (
    <>
      <PageHero
        eyebrow="Packages"
        title="Package-led pricing that helps clients buy with more confidence."
        description="Each package aligns scope, quality level, service intensity, and reporting depth."
      />
      <section className="container py-16">
        <div className="grid gap-6 lg:grid-cols-3">
          {packages.map((item) => (
            <PackageCard key={item.slug} item={item} />
          ))}
        </div>
      </section>
    </>
  );
}
