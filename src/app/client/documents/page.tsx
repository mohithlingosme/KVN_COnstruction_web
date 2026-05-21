import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { file: "Structural_Drawings.pdf", category: "Project", access: "Download", updated: "2026-05-14" },
  { file: "Invoice_1044.pdf", category: "Finance", access: "Download", updated: "2026-05-12" },
  { file: "Approval_Status_Sheet.pdf", category: "Documentation", access: "View", updated: "2026-05-08" },
];

export default function ClientDocumentsPage() {
  return (
    <DataTable
      title="Document downloads"
      columns={[
        { key: "file", title: "File" },
        { key: "category", title: "Category" },
        { key: "access", title: "Action" },
        { key: "updated", title: "Updated" },
      ]}
      rows={rows}
    />
  );
}
