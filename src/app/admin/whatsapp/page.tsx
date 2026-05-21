import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { template: "consultation_confirmation", trigger: "Appointment booked", status: "Active", language: "en" },
  { template: "weekly_update_alert", trigger: "Project update", status: "Active", language: "en" },
  { template: "payment_reminder", trigger: "Invoice due", status: "Review", language: "en" },
];

export default function AdminWhatsappPage() {
  return (
    <DataTable
      title="WhatsApp automation"
      columns={[
        { key: "template", title: "Template" },
        { key: "trigger", title: "Trigger" },
        { key: "status", title: "Status" },
        { key: "language", title: "Language" },
      ]}
      rows={rows}
    />
  );
}
