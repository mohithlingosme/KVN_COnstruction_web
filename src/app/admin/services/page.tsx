import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { service: "Residential Construction", visibility: "Homepage + SEO", pricing: "Dynamic", owner: "Growth" },
  { service: "Commercial Construction", visibility: "SEO + Sales", pricing: "Proposal", owner: "Enterprise" },
  { service: "Documentation Services", visibility: "Support Funnel", pricing: "Consultation", owner: "Ops" },
];

export default function AdminServicesPage() {
  return (
    <DataTable
      title="Service CMS"
      columns={[
        { key: "service", title: "Service" },
        { key: "visibility", title: "Visibility" },
        { key: "pricing", title: "Pricing Mode" },
        { key: "owner", title: "Owner" },
      ]}
      rows={rows}
    />
  );
}
