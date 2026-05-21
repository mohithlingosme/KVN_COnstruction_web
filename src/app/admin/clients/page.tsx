import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { client: "Harsha R.", project: "Whitefield Skyline Villa", status: "Execution", portal: "Active", manager: "Sujith" },
  { client: "Aamir K.", project: "Jayanagar Bespoke Interiors", status: "Production", portal: "Active", manager: "Aisha" },
  { client: "Vertex Dental", project: "Koramangala Workplace Hub", status: "Planning", portal: "Pending", manager: "Rahul" },
];

export default function AdminClientsPage() {
  return (
    <DataTable
      title="Client management"
      columns={[
        { key: "client", title: "Client" },
        { key: "project", title: "Project" },
        { key: "status", title: "Status" },
        { key: "portal", title: "Portal" },
        { key: "manager", title: "Manager" },
      ]}
      rows={rows}
    />
  );
}
