export type Role = "admin" | "manager" | "site_engineer" | "client";

export type Service = {
  slug: string;
  title: string;
  summary: string;
  description: string;
  icon: string;
  features: string[];
  idealFor: string[];
  deliverables: string[];
};

export type Project = {
  slug: string;
  title: string;
  category: "Residential" | "Commercial" | "Interior" | "Turnkey";
  location: string;
  budget: string;
  area: string;
  completion: string;
  heroImage: string;
  overview: string;
  highlights: string[];
  milestones: {
    name: string;
    status: "Completed" | "Active" | "Upcoming";
  }[];
};

export type PackagePlan = {
  slug: string;
  title: string;
  pricePerSqft: number;
  blurb: string;
  features: string[];
  targetAudience: string;
};

export type Testimonial = {
  id: string;
  name: string;
  role: string;
  quote: string;
  rating: number;
  project: string;
};

export type BlogPost = {
  slug: string;
  title: string;
  excerpt: string;
  coverImage: string;
  category: string;
  readTime: string;
  publishedAt: string;
  content: string[];
};

export type FAQ = {
  question: string;
  answer: string;
  category: string;
};

export type DashboardMetric = {
  title: string;
  value: string;
  change: string;
  trend: "up" | "down" | "flat";
};

export type ProjectUpdate = {
  title: string;
  date: string;
  status: string;
  note: string;
};

export type PaymentRecord = {
  invoice: string;
  amount: number;
  dueDate: string;
  status: "Paid" | "Due" | "Scheduled";
};
