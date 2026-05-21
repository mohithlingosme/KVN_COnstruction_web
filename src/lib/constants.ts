export const siteConfig = {
  name: "KVN Construction",
  legalName: "KVN Construction & Infra Private Limited",
  description:
    "Premium Bengaluru construction platform for luxury homes, commercial builds, interiors, documentation support, finance assistance, and client delivery management.",
  url: process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000",
  ogImage:
    "https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1600&q=80",
  phone: "+91 98765 43210",
  whatsappNumber: "919876543210",
  email: "hello@kvnconstruction.in",
  address: "Indiranagar, Bengaluru, Karnataka 560038",
  locale: "en_IN",
  social: {
    instagram: "https://www.instagram.com/",
    linkedin: "https://www.linkedin.com/",
    youtube: "https://www.youtube.com/",
  },
};

export const appNav = {
  public: [
    { title: "About", href: "/about" },
    { title: "Services", href: "/services" },
    { title: "Projects", href: "/projects" },
    { title: "Packages", href: "/packages" },
    { title: "Pricing", href: "/pricing" },
    { title: "Blogs", href: "/blogs" },
    { title: "Contact", href: "/contact" },
  ],
  admin: [
    { title: "Dashboard", href: "/admin" },
    { title: "Leads", href: "/admin/leads" },
    { title: "Appointments", href: "/admin/appointments" },
    { title: "Clients", href: "/admin/clients" },
    { title: "Projects", href: "/admin/projects" },
    { title: "Materials", href: "/admin/materials" },
    { title: "Documents", href: "/admin/documents" },
    { title: "Blogs", href: "/admin/blogs" },
    { title: "Testimonials", href: "/admin/testimonials" },
    { title: "FAQs", href: "/admin/faqs" },
    { title: "Services", href: "/admin/services" },
    { title: "Payments", href: "/admin/payments" },
    { title: "Notifications", href: "/admin/notifications" },
    { title: "WhatsApp", href: "/admin/whatsapp" },
    { title: "Settings", href: "/admin/settings" },
    { title: "Team", href: "/admin/team" },
  ],
  client: [
    { title: "Overview", href: "/client" },
    { title: "Timeline", href: "/client/timeline" },
    { title: "Updates", href: "/client/updates" },
    { title: "Documents", href: "/client/documents" },
    { title: "Payments", href: "/client/payments" },
    { title: "Appointments", href: "/client/appointments" },
    { title: "Messages", href: "/client/messages" },
  ],
};
