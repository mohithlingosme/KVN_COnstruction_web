import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { purpose: "Electrical walkthrough", date: "2026-05-20", time: "11:00", owner: "Nikhil S." },
  { purpose: "Window mockup review", date: "2026-05-24", time: "15:00", owner: "Aisha Rao" },
  { purpose: "Milestone payment discussion", date: "2026-05-29", time: "17:30", owner: "Finance Desk" },
];

export default function ClientAppointmentsPage() {
  return (
    <DataTable
      title="Appointment scheduling"
      columns={[
        { key: "purpose", title: "Purpose" },
        { key: "date", title: "Date" },
        { key: "time", title: "Time" },
        { key: "owner", title: "Coordinator" },
      ]}
      rows={rows}
    />
  );
}
