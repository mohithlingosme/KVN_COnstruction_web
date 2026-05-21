import Link from "next/link";
import { ArrowRight, MapPin, Star } from "lucide-react";

import type { BlogPost, PackagePlan, Project, Service, Testimonial } from "@/types/domain";
import { formatCurrency } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";

export function ServiceCard({ service }: { service: Service }) {
  return (
    <Card className="h-full">
      <CardHeader>
        <Badge variant="secondary">{service.title}</Badge>
        <CardTitle>{service.title}</CardTitle>
        <CardDescription>{service.summary}</CardDescription>
      </CardHeader>
      <CardContent>
        <ul className="space-y-2 text-sm text-muted-foreground">
          {service.features.slice(0, 3).map((feature) => (
            <li key={feature}>• {feature}</li>
          ))}
        </ul>
      </CardContent>
      <CardFooter>
        <Button asChild variant="outline">
          <Link href={`/services/${service.slug}`}>
            Explore Service
            <ArrowRight className="h-4 w-4" />
          </Link>
        </Button>
      </CardFooter>
    </Card>
  );
}

export function ProjectCard({ project }: { project: Project }) {
  return (
    <Card className="h-full overflow-hidden">
      <div
        className="h-56 bg-cover bg-center"
        style={{ backgroundImage: `url(${project.heroImage})` }}
      />
      <CardHeader>
        <Badge>{project.category}</Badge>
        <CardTitle>{project.title}</CardTitle>
        <CardDescription>{project.overview}</CardDescription>
      </CardHeader>
      <CardContent className="space-y-2 text-sm text-muted-foreground">
        <p className="inline-flex items-center gap-2">
          <MapPin className="h-4 w-4" />
          {project.location}
        </p>
        <p>{project.area}</p>
        <p>{project.budget}</p>
      </CardContent>
      <CardFooter>
        <Button asChild variant="outline">
          <Link href={`/projects/${project.slug}`}>View Case Study</Link>
        </Button>
      </CardFooter>
    </Card>
  );
}

export function PackageCard({ item }: { item: PackagePlan }) {
  return (
    <Card className="h-full">
      <CardHeader>
        <Badge>{item.title}</Badge>
        <CardTitle>{item.title}</CardTitle>
        <p className="font-display text-4xl">{formatCurrency(item.pricePerSqft)}</p>
        <CardDescription>Per sqft indicative pricing</CardDescription>
      </CardHeader>
      <CardContent className="space-y-3">
        <p className="text-sm text-muted-foreground">{item.blurb}</p>
        <ul className="space-y-2 text-sm text-muted-foreground">
          {item.features.map((feature) => (
            <li key={feature}>• {feature}</li>
          ))}
        </ul>
      </CardContent>
      <CardFooter>
        <Button asChild className="w-full">
          <Link href="/booking">Choose Package</Link>
        </Button>
      </CardFooter>
    </Card>
  );
}

export function TestimonialCard({ testimonial }: { testimonial: Testimonial }) {
  return (
    <Card className="h-full">
      <CardHeader>
        <div className="flex gap-1 text-brand-clay">
          {Array.from({ length: testimonial.rating }).map((_, index) => (
            <Star key={index} className="h-4 w-4 fill-current" />
          ))}
        </div>
        <CardTitle>{testimonial.name}</CardTitle>
        <CardDescription>{testimonial.role}</CardDescription>
      </CardHeader>
      <CardContent className="text-muted-foreground">
        “{testimonial.quote}”
      </CardContent>
      <CardFooter className="text-sm text-muted-foreground">
        {testimonial.project}
      </CardFooter>
    </Card>
  );
}

export function BlogCard({ post }: { post: BlogPost }) {
  return (
    <Card className="h-full overflow-hidden">
      <div
        className="h-52 bg-cover bg-center"
        style={{ backgroundImage: `url(${post.coverImage})` }}
      />
      <CardHeader>
        <Badge variant="secondary">{post.category}</Badge>
        <CardTitle>{post.title}</CardTitle>
        <CardDescription>{post.excerpt}</CardDescription>
      </CardHeader>
      <CardFooter className="justify-between">
        <span className="text-sm text-muted-foreground">{post.readTime}</span>
        <Button asChild variant="ghost">
          <Link href={`/blogs/${post.slug}`}>Read article</Link>
        </Button>
      </CardFooter>
    </Card>
  );
}
