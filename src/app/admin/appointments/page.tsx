import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { client: "Harsha R.", service: "Residential Consultation", date: "2026-05-24", slot: "11:00", channel: "Google Calendar" },
  { client: "Aparna S", service: "Interior Fit-Out", date: "2026-05-25", slot: "15:00", channel: "WhatsApp" },
  { client: "Vertex Dental", service: "Commercial Build", date: "2026-05-26", slot: "10:30", channel: "Email" },
];

export default function AdminAppointmentsPage() {
  return (
    <DataTable
      title="Appointment management"
      columns={[
        { key: "client", title: "Client" },
        { key: "service", title: "Service" },
        { key: "date", title: "Date" },
        { key: "slot", title: "Slot" },
        { key: "channel", title: "Automation" },
      ]}
      rows={rows}
    />
  );
}
