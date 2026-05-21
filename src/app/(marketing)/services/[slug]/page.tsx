import { notFound } from "next/navigation";

import { PageHero } from "@/components/common/page-hero";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { buildMetadata } from "@/lib/metadata";
import { services } from "@/data/site";

type Props = {
  params: Promise<{ slug: string }>;
};

export async function generateMetadata({ params }: Props) {
  const { slug } = await params;
  const service = services.find((item) => item.slug === slug);

  if (!service) {
    return {};
  }

  return buildMetadata({
    title: service.title,
    path: `/services/${service.slug}`,
    description: service.description,
  });
}

export default async function ServiceDetailPage({ params }: Props) {
  const { slug } = await params;
  const service = services.find((item) => item.slug === slug);

  if (!service) notFound();

  return (
    <>
      <PageHero
        eyebrow="Service Detail"
        title={service.title}
        description={service.description}
        primaryCta={{ label: "Book Service Consultation", href: "/booking" }}
        secondaryCta={{ label: "Request Callback", href: "/callback" }}
      />
      <section className="container py-16">
        <div className="grid gap-6 lg:grid-cols-3">
          <Card>
            <CardHeader>
              <CardTitle>Core Features</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-muted-foreground">
              {service.features.map((feature) => (
                <p key={feature}>• {feature}</p>
              ))}
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Ideal For</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-muted-foreground">
              {service.idealFor.map((item) => (
                <p key={item}>• {item}</p>
              ))}
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Deliverables</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3 text-muted-foreground">
              {service.deliverables.map((item) => (
                <p key={item}>• {item}</p>
              ))}
            </CardContent>
          </Card>
        </div>
      </section>
    </>
  );
}
