import { DataTable } from "@/components/dashboard/data-table";
import { clientUpdates } from "@/data/dashboard";

export default function ClientUpdatesPage() {
  return (
    <DataTable
      title="Weekly updates"
      columns={[
        { key: "title", title: "Update" },
        { key: "date", title: "Date" },
        { key: "status", title: "Status" },
        { key: "note", title: "Notes" },
      ]}
      rows={clientUpdates}
    />
  );
}
