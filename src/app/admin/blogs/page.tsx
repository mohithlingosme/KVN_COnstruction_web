import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { title: "Home Budget Planning", category: "Budget estimation", status: "Published", seo: "82/100", updated: "2026-05-14" },
  { title: "Smart Material Choices", category: "Material explanation", status: "Published", seo: "79/100", updated: "2026-05-10" },
  { title: "BBMP Approval Checklist", category: "Legal information", status: "Draft", seo: "Pending", updated: "2026-05-06" },
];

export default function AdminBlogsPage() {
  return (
    <DataTable
      title="Blog CMS"
      columns={[
        { key: "title", title: "Article" },
        { key: "category", title: "Category" },
        { key: "status", title: "Status" },
        { key: "seo", title: "SEO" },
        { key: "updated", title: "Updated" },
      ]}
      rows={rows}
    />
  );
}
