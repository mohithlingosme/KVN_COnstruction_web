import { notFound } from "next/navigation";

import { PageHero } from "@/components/common/page-hero";
import { buildMetadata } from "@/lib/metadata";
import { blogPosts } from "@/data/site";
import { Card, CardContent } from "@/components/ui/card";

type Props = {
  params: Promise<{ slug: string }>;
};

export async function generateMetadata({ params }: Props) {
  const { slug } = await params;
  const post = blogPosts.find((item) => item.slug === slug);

  if (!post) return {};

  return buildMetadata({
    title: post.title,
    path: `/blogs/${post.slug}`,
    description: post.excerpt,
  });
}

export default async function BlogDetailPage({ params }: Props) {
  const { slug } = await params;
  const post = blogPosts.find((item) => item.slug === slug);

  if (!post) notFound();

  return (
    <>
      <PageHero
        eyebrow={post.category}
        title={post.title}
        description={`${post.readTime} • Published ${post.publishedAt}`}
      />
      <section className="container py-16">
        <Card>
          <CardContent className="space-y-6 p-8 text-lg leading-8 text-muted-foreground">
            {post.content.map((paragraph) => (
              <p key={paragraph}>{paragraph}</p>
            ))}
          </CardContent>
        </Card>
      </section>
    </>
  );
}
