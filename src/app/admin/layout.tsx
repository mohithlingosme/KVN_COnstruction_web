import { redirect } from "next/navigation";

import { appNav } from "@/lib/constants";
import { getCurrentUserRole } from "@/lib/auth";
import { AccessBanner } from "@/components/dashboard/access-banner";
import { DashboardShell } from "@/components/dashboard/dashboard-shell";
import { DashboardSidebar } from "@/components/dashboard/dashboard-sidebar";

export default async function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const demoMode = process.env.NEXT_PUBLIC_ENABLE_DEMO_MODE !== "false";
  const role = await getCurrentUserRole();

  if (!demoMode && role !== "admin" && role !== "manager") {
    redirect("/auth");
  }

  return (
    <DashboardShell
      sidebar={<DashboardSidebar nav={appNav.admin} title="Admin CRM" />}
      title="Admin dashboard"
      description="Lead management, appointments, projects, materials, CMS, payments, automation, and team operations from a single workspace."
    >
      {demoMode ? <AccessBanner portal="admin" /> : null}
      {children}
    </DashboardShell>
  );
}
