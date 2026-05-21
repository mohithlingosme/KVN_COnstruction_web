import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { member: "Aisha Rao", role: "Admin", access: "Full", status: "Active" },
  { member: "Rahul Menon", role: "Manager", access: "Projects + Leads", status: "Active" },
  { member: "Nikhil S", role: "Site Engineer", access: "Projects + Materials", status: "Active" },
];

export default function AdminTeamPage() {
  return (
    <DataTable
      title="Team role management"
      columns={[
        { key: "member", title: "Member" },
        { key: "role", title: "Role" },
        { key: "access", title: "Access" },
        { key: "status", title: "Status" },
      ]}
      rows={rows}
    />
  );
}
