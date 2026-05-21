import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { file: "Approval_Set_v3.pdf", client: "Harsha R.", type: "Approvals", access: "Shared", updated: "2026-05-20" },
  { file: "Invoice_1044.pdf", client: "Aamir K.", type: "Finance", access: "Portal", updated: "2026-05-18" },
  { file: "Vendor_Contract_MEP.pdf", client: "Vertex Dental", type: "Project", access: "Internal", updated: "2026-05-17" },
];

export default function AdminDocumentsPage() {
  return (
    <DataTable
      title="Document management"
      columns={[
        { key: "file", title: "File" },
        { key: "client", title: "Client" },
        { key: "type", title: "Type" },
        { key: "access", title: "Access" },
        { key: "updated", title: "Updated" },
      ]}
      rows={rows}
    />
  );
}
