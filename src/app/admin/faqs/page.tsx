import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { question: "How is pricing calculated?", category: "Construction", status: "Published", owner: "Content Team" },
  { question: "How often are updates shared?", category: "Timeline", status: "Published", owner: "Support" },
  { question: "Can approvals be managed by KVN?", category: "Documentation", status: "Published", owner: "Operations" },
];

export default function AdminFaqsPage() {
  return (
    <DataTable
      title="FAQ CMS"
      columns={[
        { key: "question", title: "Question" },
        { key: "category", title: "Category" },
        { key: "status", title: "Status" },
        { key: "owner", title: "Owner" },
      ]}
      rows={rows}
    />
  );
}
