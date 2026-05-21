import { ProjectCard } from "@/components/common/cards";
import { PageHero } from "@/components/common/page-hero";
import { projects } from "@/data/site";
import { buildMetadata } from "@/lib/metadata";

export const metadata = buildMetadata({
  title: "Projects",
  path: "/projects",
  description: "Portfolio of Bengaluru residential, commercial, interior, and turnkey construction projects.",
});

export default function ProjectsPage() {
  return (
    <>
      <PageHero
        eyebrow="Portfolio"
        title="Case studies that build trust before the sales call."
        description="Structured project narratives covering location, scale, milestones, highlights, and delivery logic."
      />
      <section className="container py-16">
        <div className="grid gap-6 lg:grid-cols-3">
          {projects.map((project) => (
            <ProjectCard key={project.slug} project={project} />
          ))}
        </div>
      </section>
    </>
  );
}
