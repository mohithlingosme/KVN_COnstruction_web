import { adminLeadFunnel, adminMetrics, adminRevenueTrend, recentLeads } from "@/data/dashboard";
import { FunnelChart, RevenueChart } from "@/components/dashboard/chart-cards";
import { DataTable } from "@/components/dashboard/data-table";
import { MetricCard } from "@/components/common/metric-card";

export default function AdminDashboardPage() {
  return (
    <div className="space-y-6">
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        {adminMetrics.map((metric) => (
          <MetricCard key={metric.title} metric={metric} />
        ))}
      </div>
      <div className="grid gap-6 xl:grid-cols-2">
        <RevenueChart data={adminRevenueTrend} />
        <FunnelChart data={adminLeadFunnel} />
      </div>
      <DataTable
        title="Recent leads"
        columns={[
          { key: "name", title: "Lead" },
          { key: "source", title: "Source" },
          { key: "stage", title: "Stage" },
          { key: "requirement", title: "Requirement" },
          { key: "budget", title: "Budget" },
        ]}
        rows={recentLeads}
      />
    </div>
  );
}
