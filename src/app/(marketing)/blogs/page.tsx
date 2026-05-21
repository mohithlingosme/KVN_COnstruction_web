import { BlogCard } from "@/components/common/cards";
import { PageHero } from "@/components/common/page-hero";
import { blogCategories, blogPosts } from "@/data/site";
import { buildMetadata } from "@/lib/metadata";
import { Badge } from "@/components/ui/badge";

export const metadata = buildMetadata({
  title: "Blogs",
  path: "/blogs",
  description: "Construction, interiors, planning, legal, budgeting, and smart home content for Bengaluru audiences.",
});

export default function BlogsPage() {
  return (
    <>
      <PageHero
        eyebrow="Knowledge Hub"
        title="Content designed for SEO authority and buyer education."
        description="Category architecture covers construction tips, planning, budgeting, legal workflows, interiors, materials, vastu, and smart homes."
      />
      <section className="container py-10">
        <div className="flex flex-wrap gap-3">
          {blogCategories.map((category) => (
            <Badge key={category} variant="secondary">
              {category}
            </Badge>
          ))}
        </div>
      </section>
      <section className="container pb-16">
        <div className="grid gap-6 lg:grid-cols-3">
          {blogPosts.map((post) => (
            <BlogCard key={post.slug} post={post} />
          ))}
        </div>
      </section>
    </>
  );
}
