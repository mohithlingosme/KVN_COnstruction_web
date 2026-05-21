import type { MetadataRoute } from "next";

import { blogPosts, projects, services } from "@/data/site";
import { siteConfig } from "@/lib/constants";

export default function sitemap(): MetadataRoute.Sitemap {
  const baseRoutes = [
    "",
    "/about",
    "/services",
    "/projects",
    "/packages",
    "/pricing",
    "/testimonials",
    "/blogs",
    "/faqs",
    "/contact",
    "/estimator",
    "/booking",
    "/callback",
    "/financial-services",
    "/documentation-services",
    "/privacy-policy",
    "/terms-and-conditions",
  ];

  const generated = [
    ...services.map((service) => `/services/${service.slug}`),
    ...projects.map((project) => `/projects/${project.slug}`),
    ...blogPosts.map((post) => `/blogs/${post.slug}`),
  ];

  return [...baseRoutes, ...generated].map((path) => ({
    url: `${siteConfig.url}${path}`,
    lastModified: new Date(),
    changeFrequency: "weekly",
    priority: path === "" ? 1 : 0.7,
  }));
}
