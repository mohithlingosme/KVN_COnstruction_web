import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { material: "TMT Steel", project: "Whitefield Villa", usage: "74%", vendor: "Prime Metals", status: "On track" },
  { material: "Cement", project: "Koramangala Hub", usage: "58%", vendor: "UltraBuild", status: "Reorder due" },
  { material: "Veneer Panels", project: "Jayanagar Interiors", usage: "91%", vendor: "Oakline", status: "Delivered" },
];

export default function AdminMaterialsPage() {
  return (
    <DataTable
      title="Material tracking"
      columns={[
        { key: "material", title: "Material" },
        { key: "project", title: "Project" },
        { key: "usage", title: "Usage" },
        { key: "vendor", title: "Vendor" },
        { key: "status", title: "Status" },
      ]}
      rows={rows}
    />
  );
}
