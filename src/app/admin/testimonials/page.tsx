import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { name: "Harsha R.", project: "Whitefield Villa", rating: "5/5", status: "Live", source: "Google Review" },
  { name: "Neha B.", project: "Workplace Hub", rating: "5/5", status: "Live", source: "Video" },
  { name: "Aamir K.", project: "Bespoke Interiors", rating: "5/5", status: "Pending", source: "Portal Prompt" },
];

export default function AdminTestimonialsPage() {
  return (
    <DataTable
      title="Testimonial CMS"
      columns={[
        { key: "name", title: "Client" },
        { key: "project", title: "Project" },
        { key: "rating", title: "Rating" },
        { key: "status", title: "Status" },
        { key: "source", title: "Source" },
      ]}
      rows={rows}
    />
  );
}
