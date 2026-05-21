import type { DashboardMetric, PaymentRecord, ProjectUpdate } from "@/types/domain";

export const adminMetrics: DashboardMetric[] = [
  { title: "Active leads", value: "128", change: "+18% this month", trend: "up" },
  { title: "Site revenue", value: "₹4.8 Cr", change: "+9.4% vs last month", trend: "up" },
  { title: "Appointments", value: "36", change: "+12 new this week", trend: "up" },
  { title: "Collection efficiency", value: "91%", change: "-2% pending follow-up", trend: "down" },
];

export const adminLeadFunnel = [
  { name: "Inquiry", value: 124 },
  { name: "Qualified", value: 81 },
  { name: "Site Visit", value: 47 },
  { name: "Proposal", value: 23 },
  { name: "Won", value: 11 },
];

export const adminRevenueTrend = [
  { month: "Jan", revenue: 72, collections: 58 },
  { month: "Feb", revenue: 84, collections: 66 },
  { month: "Mar", revenue: 96, collections: 71 },
  { month: "Apr", revenue: 103, collections: 88 },
  { month: "May", revenue: 118, collections: 92 },
  { month: "Jun", revenue: 131, collections: 108 },
];

export const recentLeads = [
  {
    name: "Raghav Menon",
    source: "Google Ads",
    stage: "Proposal sent",
    requirement: "Villa construction",
    budget: "₹2-3 Cr",
  },
  {
    name: "Aparna S",
    source: "WhatsApp",
    stage: "Site visit booked",
    requirement: "Interior fit-out",
    budget: "₹40-60 L",
  },
  {
    name: "Vertex Dental",
    source: "Referral",
    stage: "Qualified",
    requirement: "Clinic build-out",
    budget: "₹1 Cr+",
  },
];

export const clientMetrics: DashboardMetric[] = [
  { title: "Project progress", value: "68%", change: "Ahead by 4 days", trend: "up" },
  { title: "Upcoming milestone", value: "Flooring", change: "Starts in 6 days", trend: "flat" },
  { title: "Open actions", value: "4", change: "2 pending approvals", trend: "flat" },
  { title: "Payments cleared", value: "₹78 L", change: "On schedule", trend: "up" },
];

export const clientUpdates: ProjectUpdate[] = [
  {
    title: "First-floor blockwork completed",
    date: "2026-05-18",
    status: "Completed",
    note: "Internal partitions finished and slab prep started.",
  },
  {
    title: "Electrical conduit walkthrough",
    date: "2026-05-20",
    status: "Scheduled",
    note: "Client approval needed on switchboard positions in master suite.",
  },
  {
    title: "Window vendor mockup review",
    date: "2026-05-24",
    status: "Upcoming",
    note: "Final bronze frame finish sample to be approved.",
  },
];

export const clientPayments: PaymentRecord[] = [
  { invoice: "INV-1024", amount: 1850000, dueDate: "2026-04-12", status: "Paid" },
  { invoice: "INV-1033", amount: 2150000, dueDate: "2026-05-09", status: "Paid" },
  { invoice: "INV-1044", amount: 2480000, dueDate: "2026-06-02", status: "Scheduled" },
  { invoice: "INV-1048", amount: 960000, dueDate: "2026-06-18", status: "Due" },
];

export const timelineMilestones = [
  { stage: "Design freeze", status: "done", date: "2026-01-10" },
  { stage: "Foundation complete", status: "done", date: "2026-02-14" },
  { stage: "Structure to roof", status: "done", date: "2026-04-05" },
  { stage: "MEP rough-ins", status: "active", date: "2026-05-28" },
  { stage: "Finishes and fixtures", status: "upcoming", date: "2026-07-14" },
];
