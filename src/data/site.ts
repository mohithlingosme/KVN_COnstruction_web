import type {
  BlogPost,
  FAQ,
  PackagePlan,
  Project,
  Service,
  Testimonial,
} from "@/types/domain";

export const services: Service[] = [
  {
    slug: "residential-construction",
    title: "Residential Construction",
    summary: "Luxury villas, duplex homes, gated community plots, and premium urban residences.",
    description:
      "End-to-end planning, engineering, procurement, and site execution for custom homes in Bengaluru with timeline discipline and transparent milestone billing.",
    icon: "House",
    features: [
      "Architect + structural coordination",
      "BBMP and approval guidance",
      "Site supervision and QA checkpoints",
      "Weekly client progress reviews",
    ],
    idealFor: ["Villa owners", "Independent homeowners", "NRI clients"],
    deliverables: [
      "Scope freeze and BOQ",
      "Construction schedule",
      "Material procurement tracking",
      "Client dashboard access",
    ],
  },
  {
    slug: "commercial-construction",
    title: "Commercial Construction",
    summary: "Office buildings, retail spaces, healthcare facilities, and mixed-use developments.",
    description:
      "Program-managed commercial delivery with dedicated project controls, contractor coordination, and documentation support across the full construction lifecycle.",
    icon: "Building2",
    features: [
      "Project controls and vendor management",
      "Commercial code compliance",
      "Site safety audits",
      "Cash flow and package scheduling",
    ],
    idealFor: ["Developers", "Business owners", "Investors"],
    deliverables: [
      "Pre-construction planning",
      "Execution dashboard",
      "Snagging and handover matrix",
      "Document repository",
    ],
  },
  {
    slug: "interior-fitouts",
    title: "Interior Fit-Outs",
    summary: "Turnkey interior environments with custom joinery, smart home integration, and lighting design.",
    description:
      "A premium interior delivery workflow covering mood boards, manufacturing coordination, finishes, site installation, and final styling.",
    icon: "LampFloor",
    features: [
      "Luxury wardrobes and modular systems",
      "False ceiling and lighting plans",
      "Loose furniture curation",
      "Smart home integration",
    ],
    idealFor: ["Homeowners", "Builders", "Hospitality clients"],
    deliverables: [
      "Interior concept deck",
      "Factory production tracking",
      "Room-wise milestone plan",
      "Aftercare checklist",
    ],
  },
  {
    slug: "documentation-services",
    title: "Documentation Services",
    summary: "Approvals, permits, compliance filings, and construction documentation assistance.",
    description:
      "Support for plan approvals, plan revisions, NOCs, occupancy workflows, and structured documentation management for smoother project execution.",
    icon: "ScrollText",
    features: [
      "Approval file coordination",
      "Permit checklist tracking",
      "Digitized documentation vault",
      "Third-party liaison support",
    ],
    idealFor: ["First-time owners", "Developers", "Plot buyers"],
    deliverables: [
      "Documentation roadmap",
      "Approval checklist",
      "Status reminders",
      "Secure file storage",
    ],
  },
];

export const projects: Project[] = [
  {
    slug: "whitefield-skyline-villa",
    title: "Whitefield Skyline Villa",
    category: "Residential",
    location: "Whitefield, Bengaluru",
    budget: "₹2.8 Cr",
    area: "4,800 sqft",
    completion: "14 months",
    heroImage:
      "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80",
    overview:
      "A courtyard-led villa with triple-height living, natural stone palette, landscaped deck, and integrated smart controls.",
    highlights: [
      "98% schedule adherence",
      "Rainwater harvesting and solar-ready roof",
      "Imported marble and teak finishes",
    ],
    milestones: [
      { name: "Design Freeze", status: "Completed" },
      { name: "Civil Structure", status: "Completed" },
      { name: "Finishing Works", status: "Active" },
      { name: "Handover", status: "Upcoming" },
    ],
  },
  {
    slug: "koramangala-workplace-hub",
    title: "Koramangala Workplace Hub",
    category: "Commercial",
    location: "Koramangala, Bengaluru",
    budget: "₹6.4 Cr",
    area: "16,000 sqft",
    completion: "11 months",
    heroImage:
      "https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=80",
    overview:
      "Contemporary office development with collaborative work floors, acoustic focus pods, and a hospitality-grade arrival lobby.",
    highlights: [
      "Multi-vendor fit-out coordination",
      "Fast-track occupancy sequence",
      "Integrated access and surveillance planning",
    ],
    milestones: [
      { name: "Site Mobilization", status: "Completed" },
      { name: "Core Works", status: "Completed" },
      { name: "MEP Installations", status: "Active" },
      { name: "Client Handover", status: "Upcoming" },
    ],
  },
  {
    slug: "jayanagar-bespoke-interiors",
    title: "Jayanagar Bespoke Interiors",
    category: "Interior",
    location: "Jayanagar, Bengaluru",
    budget: "₹68 L",
    area: "3,100 sqft",
    completion: "18 weeks",
    heroImage:
      "https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1600&q=80",
    overview:
      "A warm minimal interior package with walnut veneers, fluted glass elements, sculpted lighting, and concealed services.",
    highlights: [
      "Factory-finished joinery",
      "Room-wise punch list tracking",
      "Client sign-off at every milestone",
    ],
    milestones: [
      { name: "Design Approval", status: "Completed" },
      { name: "Factory Production", status: "Active" },
      { name: "Installation", status: "Upcoming" },
      { name: "Styling", status: "Upcoming" },
    ],
  },
];

export const packages: PackagePlan[] = [
  {
    slug: "smart-build",
    title: "Smart Build",
    pricePerSqft: 1900,
    blurb: "Efficient construction package for plot owners who want dependable quality and tight cost control.",
    features: [
      "RCC structure and branded materials",
      "Dedicated site engineer",
      "Bi-weekly client reports",
      "Essential approval support",
    ],
    targetAudience: "Budget-conscious first home builds",
  },
  {
    slug: "premium-turnkey",
    title: "Premium Turnkey",
    pricePerSqft: 2350,
    blurb: "Balanced premium package with upgraded finishes, stronger controls, and richer dashboard visibility.",
    features: [
      "Premium tiles and sanitaryware",
      "Weekly progress updates",
      "Client portal and milestone finance tracking",
      "Interior coordination support",
    ],
    targetAudience: "High-quality family homes and offices",
  },
  {
    slug: "signature-luxe",
    title: "Signature Luxe",
    pricePerSqft: 2950,
    blurb: "Luxury delivery for bespoke residences and flagship commercial spaces with high-detail execution.",
    features: [
      "Concierge project manager",
      "Luxury finish schedules",
      "Smart home and lighting integration",
      "Executive reporting and white-glove handover",
    ],
    targetAudience: "Luxury villas and prestige developments",
  },
];

export const testimonials: Testimonial[] = [
  {
    id: "1",
    name: "Harsha R.",
    role: "Villa Owner, Whitefield",
    quote:
      "The difference was control. Every week we knew the exact site status, cash flow, and decisions pending. That transparency is rare.",
    rating: 5,
    project: "Whitefield Skyline Villa",
  },
  {
    id: "2",
    name: "Neha B.",
    role: "Founder, Studio Collective",
    quote:
      "KVN handled design coordination, approvals, and execution without the chaos usually associated with commercial projects.",
    rating: 5,
    project: "Koramangala Workplace Hub",
  },
  {
    id: "3",
    name: "Aamir K.",
    role: "NRI Client",
    quote:
      "The client portal made remote decision-making simple. Photos, updates, invoices, and documents were all where they should be.",
    rating: 5,
    project: "Jayanagar Bespoke Interiors",
  },
];

export const faqs: FAQ[] = [
  {
    category: "Construction",
    question: "How does KVN price residential construction in Bengaluru?",
    answer:
      "We price by scope, engineering complexity, finish grade, and site constraints. The estimator gives a planning range, while the final BOQ comes after scope freeze.",
  },
  {
    category: "Timeline",
    question: "How often do clients receive updates?",
    answer:
      "Weekly dashboard updates are standard. Premium packages also receive milestone reports, site imagery, and coordination calls.",
  },
  {
    category: "Payments",
    question: "Do you support milestone-based payments?",
    answer:
      "Yes. Payments are structured by agreed milestones, tracked in the client portal, and aligned to measurable construction progress.",
  },
  {
    category: "Documentation",
    question: "Can you help with approvals and building permits?",
    answer:
      "Yes. Our documentation desk coordinates approval checklists, status tracking, and liaison support for plan approvals and compliance paperwork.",
  },
];

export const blogPosts: BlogPost[] = [
  {
    slug: "how-to-plan-a-bengaluru-home-budget",
    title: "How To Plan a Bengaluru Home Construction Budget Without Surprises",
    excerpt:
      "A practical framework for setting realistic budgets across civil, MEP, finishes, approvals, and contingency.",
    coverImage:
      "https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1600&q=80",
    category: "Budget estimation",
    readTime: "6 min read",
    publishedAt: "2026-05-14",
    content: [
      "Start with plot potential, floor count, and finish grade. Construction budgets fail when scope is vague, not when rates are high.",
      "Break the budget into structure, MEP, finishes, interiors, approvals, and contingency. Each category must have a measurable owner and decision stage.",
      "Milestone billing works when the milestone definition is objective. Tie payment releases to deliverables, not optimism.",
    ],
  },
  {
    slug: "smart-material-choices-for-premium-homes",
    title: "Smart Material Choices for Premium Homes in Bengaluru",
    excerpt:
      "Where it makes sense to upgrade, where standard materials perform well, and how to avoid vanity-spec overspend.",
    coverImage:
      "https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=1600&q=80",
    category: "Material explanation",
    readTime: "5 min read",
    publishedAt: "2026-05-10",
    content: [
      "Prioritize structure, waterproofing, windows, and plumbing before spending heavily on visible finish upgrades.",
      "A premium home should feel deliberate, not overloaded. Use better systems where failure is expensive and maintainable finishes where style evolves faster.",
      "Material approvals should happen through samples, mockups, and room-wise finish matrices.",
    ],
  },
  {
    slug: "bbmp-approval-checklist-for-first-time-builders",
    title: "BBMP Approval Checklist for First-Time Builders",
    excerpt:
      "A high-level guide to the documents, consultants, and approval sequence plot owners should expect.",
    coverImage:
      "https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1600&q=80",
    category: "Legal information",
    readTime: "7 min read",
    publishedAt: "2026-05-06",
    content: [
      "Approval readiness begins before submission. Gather ownership records, survey details, drawings, structural inputs, and consultant sign-offs early.",
      "The cost of rework is usually greater than the cost of preparation. Approval gaps often show up later as schedule risk on site.",
      "Use a live documentation tracker so the owner, architect, and liaison team are aligned on the file status.",
    ],
  },
];

export const blogCategories = [
  "Construction tips",
  "Home planning",
  "Interior design",
  "Budget estimation",
  "Legal information",
  "Material explanation",
  "Vastu concepts",
  "Smart home",
  "Cost saving tips",
];

export const servicePageHighlights = [
  "Bengaluru-local execution intelligence",
  "Transparent BOQs and milestone billing",
  "Dedicated CRM dashboards for admin and clients",
  "Premium site communication and documentation",
];
