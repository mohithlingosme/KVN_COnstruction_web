import { notFound } from "next/navigation";

import { PageHero } from "@/components/common/page-hero";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { buildMetadata } from "@/lib/metadata";
import { projects } from "@/data/site";

type Props = {
  params: Promise<{ slug: string }>;
};

export async function generateMetadata({ params }: Props) {
  const { slug } = await params;
  const project = projects.find((item) => item.slug === slug);

  if (!project) {
    return {};
  }

  return buildMetadata({
    title: project.title,
    path: `/projects/${project.slug}`,
    description: project.overview,
  });
}

export default async function ProjectDetailPage({ params }: Props) {
  const { slug } = await params;
  const project = projects.find((item) => item.slug === slug);

  if (!project) notFound();

  return (
    <>
      <PageHero
        eyebrow={project.category}
        title={project.title}
        description={`${project.location} • ${project.area} • ${project.budget} • ${project.completion}`}
      />
      <section className="container py-16">
        <div className="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
          <Card>
            <CardHeader>
              <CardTitle>Project Overview</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4 text-muted-foreground">
              <p>{project.overview}</p>
              {project.highlights.map((highlight) => (
                <p key={highlight}>• {highlight}</p>
              ))}
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Milestones</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4 text-muted-foreground">
              {project.milestones.map((milestone) => (
                <div key={milestone.name} className="flex items-center justify-between rounded-2xl bg-muted/60 px-4 py-3">
                  <span>{milestone.name}</span>
                  <span>{milestone.status}</span>
                </div>
              ))}
            </CardContent>
          </Card>
        </div>
      </section>
    </>
  );
}
