import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { event: "Milestone payment due", channel: "Email + WhatsApp", audience: "Harsha R.", status: "Queued" },
  { event: "Weekly update published", channel: "Portal", audience: "All active clients", status: "Sent" },
  { event: "Document upload reminder", channel: "Email", audience: "Vertex Dental", status: "Draft" },
];

export default function AdminNotificationsPage() {
  return (
    <DataTable
      title="Notifications"
      columns={[
        { key: "event", title: "Event" },
        { key: "channel", title: "Channel" },
        { key: "audience", title: "Audience" },
        { key: "status", title: "Status" },
      ]}
      rows={rows}
    />
  );
}
