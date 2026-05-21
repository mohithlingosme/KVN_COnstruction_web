import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { name: "Raghav Menon", source: "Google Ads", stage: "Proposal", owner: "Aisha", updated: "2h ago" },
  { name: "Saanvi Buildco", source: "Referral", stage: "Qualified", owner: "Kiran", updated: "5h ago" },
  { name: "Aparna S", source: "WhatsApp", stage: "Visit booked", owner: "Nikhil", updated: "1d ago" },
];

export default function AdminLeadsPage() {
  return (
    <DataTable
      title="Lead management"
      columns={[
        { key: "name", title: "Name" },
        { key: "source", title: "Source" },
        { key: "stage", title: "Stage" },
        { key: "owner", title: "Owner" },
        { key: "updated", title: "Updated" },
      ]}
      rows={rows}
    />
  );
}
