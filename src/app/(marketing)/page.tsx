import Link from "next/link";
import { Building2, Calculator, FileCheck2, HandCoins, ShieldCheck } from "lucide-react";

import { BlogCard, PackageCard, ProjectCard, ServiceCard, TestimonialCard } from "@/components/common/cards";
import { PageHero } from "@/components/common/page-hero";
import { SchemaScript } from "@/components/common/schema-script";
import { SectionHeading } from "@/components/common/section-heading";
import { CostEstimatorForm } from "@/components/forms/cost-estimator-form";
import { LeadForm } from "@/components/forms/lead-form";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { siteConfig } from "@/lib/constants";
import { buildMetadata } from "@/lib/metadata";
import { blogPosts, packages, projects, servicePageHighlights, services, testimonials } from "@/data/site";

export const metadata = buildMetadata({
  title: "Bengaluru Construction Platform",
  description:
    "Luxury residential and commercial construction website with CRM, client portal, finance support, documentation, estimator, and booking flows.",
  keywords: ["construction company Bengaluru", "turnkey home construction", "luxury villa builder"],
});

const trustStats = [
  { label: "Projects delivered", value: "500+" },
  { label: "Ongoing sites", value: "28" },
  { label: "Avg collection visibility", value: "91%" },
  { label: "Client update cadence", value: "Weekly" },
];

export default function HomePage() {
  return (
    <>
      <SchemaScript
        id="home-schema"
        data={{
          "@context": "https://schema.org",
          "@type": "LocalBusiness",
          name: siteConfig.name,
          image: siteConfig.ogImage,
          telephone: siteConfig.phone,
          address: {
            "@type": "PostalAddress",
            streetAddress: siteConfig.address,
            addressLocality: "Bengaluru",
            addressRegion: "Karnataka",
            addressCountry: "IN",
          },
          url: siteConfig.url,
        }}
      />
      <PageHero
        eyebrow="Premium Construction Intelligence"
        title="Built for Bengaluru projects that demand trust, control, and premium delivery."
        description="A modern full-stack platform for residential and commercial construction, combining lead capture, client collaboration, approvals, payment tracking, and on-site execution visibility."
        primaryCta={{ label: "Get Free Estimate", href: "/estimator" }}
        secondaryCta={{ label: "Book Consultation", href: "/booking" }}
      />

      <section className="container py-10">
        <div className="grid gap-4 md:grid-cols-4">
          {trustStats.map((item) => (
            <Card key={item.label}>
              <CardContent className="space-y-2 p-6">
                <p className="font-display text-4xl">{item.value}</p>
                <p className="text-sm uppercase tracking-[0.24em] text-muted-foreground">
                  {item.label}
                </p>
              </CardContent>
            </Card>
          ))}
        </div>
      </section>

      <section className="container py-16">
        <div className="grid gap-8 lg:grid-cols-[0.95fr,1.05fr]">
          <SectionHeading
            badge="Why KVN"
            title="An architectural brand experience backed by operating discipline."
            description="The website is only one layer. Under it sits a practical operating model for sales, scheduling, updates, documentation, collections, and client communication."
          />
          <div className="grid gap-4 md:grid-cols-2">
            {[
              { icon: Building2, title: "Residential and commercial delivery", copy: "One platform for homes, offices, clinics, retail, and premium interiors." },
              { icon: Calculator, title: "Smart budgeting tools", copy: "Estimator, package guidance, and milestone-based payment logic built into the flow." },
              { icon: FileCheck2, title: "Documentation workflow", copy: "Approval and permit support embedded alongside project delivery." },
              { icon: HandCoins, title: "Finance support", copy: "Loan assistance, EMI planning, and consultation-ready decision pathways." },
            ].map((item) => (
              <Card key={item.title}>
                <CardContent className="space-y-4 p-6">
                  <item.icon className="h-8 w-8 text-brand-clay" />
                  <div>
                    <h3 className="text-xl font-semibold">{item.title}</h3>
                    <p className="mt-2 text-sm text-muted-foreground">{item.copy}</p>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      <section className="container py-16">
        <div className="mb-10 flex items-end justify-between gap-6">
          <SectionHeading
            badge="Services"
            title="Full-spectrum delivery from approvals to handover."
            description="Structured service lines built for modern Bengaluru construction clients."
          />
          <Button asChild variant="outline">
            <Link href="/services">View All Services</Link>
          </Button>
        </div>
        <div className="grid gap-6 lg:grid-cols-4">
          {services.map((service) => (
            <ServiceCard key={service.slug} service={service} />
          ))}
        </div>
      </section>

      <section className="container py-16">
        <div className="mb-10 flex items-end justify-between gap-6">
          <SectionHeading
            badge="Projects"
            title="Premium homes and commercial spaces with documented outcomes."
            description="Each project page is designed to function like a sell-through case study."
          />
          <Button asChild variant="outline">
            <Link href="/projects">See Portfolio</Link>
          </Button>
        </div>
        <div className="grid gap-6 lg:grid-cols-3">
          {projects.map((project) => (
            <ProjectCard key={project.slug} project={project} />
          ))}
        </div>
      </section>

      <section className="container py-16">
        <div className="mb-10">
          <SectionHeading
            badge="Estimator"
            title="Lead magnet plus planning engine."
            description="The estimator captures intent while giving prospects a credible first planning range."
          />
        </div>
        <CostEstimatorForm />
      </section>

      <section className="container py-16">
        <div className="mb-10 flex items-end justify-between gap-6">
          <SectionHeading
            badge="Packages"
            title="Pricing tiers calibrated for execution quality."
            description="Transparent direction for homeowners and developers comparing finish standards and service levels."
          />
          <Button asChild variant="outline">
            <Link href="/pricing">Compare Pricing</Link>
          </Button>
        </div>
        <div className="grid gap-6 lg:grid-cols-3">
          {packages.map((item) => (
            <PackageCard key={item.slug} item={item} />
          ))}
        </div>
      </section>

      <section className="container py-16">
        <div className="grid gap-6 lg:grid-cols-[1fr,1.05fr]">
          <Card className="bg-brand-night text-white dark:border-white/10 dark:bg-brand-night">
            <CardContent className="space-y-6 p-8">
              <Badge className="w-fit bg-white/10 text-brand-sand">Operating Edge</Badge>
              <h2 className="font-display text-5xl">A website designed to sell, a platform designed to run the business.</h2>
              <div className="grid gap-3">
                {servicePageHighlights.map((highlight) => (
                  <div key={highlight} className="inline-flex items-center gap-3 text-white/80">
                    <ShieldCheck className="h-5 w-5 text-brand-sand" />
                    {highlight}
                  </div>
                ))}
              </div>
              <Button asChild variant="secondary">
                <Link href="/admin">Preview Admin CRM</Link>
              </Button>
            </CardContent>
          </Card>
          <LeadForm />
        </div>
      </section>

      <section className="container py-16">
        <div className="mb-10">
          <SectionHeading
            badge="Testimonials"
            title="Client confidence built through visibility."
            description="Reviews positioned around transparency, scheduling, and project communication."
          />
        </div>
        <div className="grid gap-6 lg:grid-cols-3">
          {testimonials.map((item) => (
            <TestimonialCard key={item.id} testimonial={item} />
          ))}
        </div>
      </section>

      <section className="container py-16">
        <div className="mb-10">
          <SectionHeading
            badge="Insights"
            title="SEO-ready construction content architecture."
            description="The blog system supports authority building across planning, materials, budgeting, interiors, legal workflows, and smart homes."
          />
        </div>
        <div className="grid gap-6 lg:grid-cols-3">
          {blogPosts.map((post) => (
            <BlogCard key={post.slug} post={post} />
          ))}
        </div>
      </section>
    </>
  );
}
