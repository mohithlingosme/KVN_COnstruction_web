import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { project: "Whitefield Skyline Villa", phase: "Finishes", progress: "68%", engineer: "Nikhil", next: "Flooring" },
  { project: "Koramangala Workplace Hub", phase: "MEP", progress: "54%", engineer: "Shravan", next: "Ceiling grid" },
  { project: "Jayanagar Bespoke Interiors", phase: "Factory", progress: "72%", engineer: "Asha", next: "Install slot" },
];

export default function AdminProjectsPage() {
  return (
    <DataTable
      title="Project management"
      columns={[
        { key: "project", title: "Project" },
        { key: "phase", title: "Current phase" },
        { key: "progress", title: "Progress" },
        { key: "engineer", title: "Lead" },
        { key: "next", title: "Next milestone" },
      ]}
      rows={rows}
    />
  );
}
