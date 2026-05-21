import { redirect } from "next/navigation";

import { appNav } from "@/lib/constants";
import { getCurrentUserRole } from "@/lib/auth";
import { AccessBanner } from "@/components/dashboard/access-banner";
import { DashboardShell } from "@/components/dashboard/dashboard-shell";
import { DashboardSidebar } from "@/components/dashboard/dashboard-sidebar";

export default async function ClientLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const demoMode = process.env.NEXT_PUBLIC_ENABLE_DEMO_MODE !== "false";
  const role = await getCurrentUserRole();

  if (!demoMode && role !== "client" && role !== "admin" && role !== "manager") {
    redirect("/auth");
  }

  return (
    <DashboardShell
      sidebar={<DashboardSidebar nav={appNav.client} title="Client Portal" />}
      title="Client portal"
      description="Project overview, updates, milestones, documents, communication, appointments, and payments in one secure workspace."
    >
      {demoMode ? <AccessBanner portal="client" /> : null}
      {children}
    </DashboardShell>
  );
}
