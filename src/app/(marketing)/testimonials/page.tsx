import { TestimonialCard } from "@/components/common/cards";
import { PageHero } from "@/components/common/page-hero";
import { testimonials } from "@/data/site";
import { buildMetadata } from "@/lib/metadata";

export const metadata = buildMetadata({
  title: "Testimonials",
  path: "/testimonials",
  description: "Client testimonials highlighting trust, delivery quality, communication, and transparency.",
});

export default function TestimonialsPage() {
  return (
    <>
      <PageHero
        eyebrow="Client Voice"
        title="What clients actually valued was visibility."
        description="Testimonials positioned around trust, communication, scheduling, and delivery confidence."
      />
      <section className="container py-16">
        <div className="grid gap-6 lg:grid-cols-3">
          {testimonials.map((item) => (
            <TestimonialCard key={item.id} testimonial={item} />
          ))}
        </div>
      </section>
    </>
  );
}
