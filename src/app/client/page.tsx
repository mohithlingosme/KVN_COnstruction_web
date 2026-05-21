import { clientMetrics, clientPayments, clientUpdates, timelineMilestones } from "@/data/dashboard";
import { MetricCard } from "@/components/common/metric-card";
import { ProgressDonut } from "@/components/dashboard/chart-cards";
import { DataTable } from "@/components/dashboard/data-table";
import { TimelinePanel } from "@/components/dashboard/timeline-panel";

export default function ClientOverviewPage() {
  return (
    <div className="space-y-6">
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        {clientMetrics.map((metric) => (
          <MetricCard key={metric.title} metric={metric} />
        ))}
      </div>
      <div className="grid gap-6 xl:grid-cols-[0.9fr,1.1fr]">
        <ProgressDonut value={68} />
        <TimelinePanel items={timelineMilestones} />
      </div>
      <DataTable
        title="Recent weekly updates"
        columns={[
          { key: "title", title: "Update" },
          { key: "date", title: "Date" },
          { key: "status", title: "Status" },
          { key: "note", title: "Note" },
        ]}
        rows={clientUpdates}
      />
      <DataTable
        title="Payment history"
        columns={[
          { key: "invoice", title: "Invoice" },
          { key: "amount", title: "Amount" },
          { key: "dueDate", title: "Due date" },
          { key: "status", title: "Status" },
        ]}
        rows={clientPayments.map((item) => ({
          ...item,
          amount: `₹${item.amount.toLocaleString("en-IN")}`,
        }))}
      />
    </div>
  );
}
