import { DataTable } from "@/components/dashboard/data-table";

const rows = [
  { invoice: "INV-1044", client: "Harsha R.", amount: "₹24.8L", mode: "Razorpay", status: "Scheduled" },
  { invoice: "INV-1041", client: "Aamir K.", amount: "₹9.6L", mode: "Bank Transfer", status: "Due" },
  { invoice: "INV-1033", client: "Harsha R.", amount: "₹21.5L", mode: "Razorpay", status: "Paid" },
];

export default function AdminPaymentsPage() {
  return (
    <DataTable
      title="Payment tracking"
      columns={[
        { key: "invoice", title: "Invoice" },
        { key: "client", title: "Client" },
        { key: "amount", title: "Amount" },
        { key: "mode", title: "Mode" },
        { key: "status", title: "Status" },
      ]}
      rows={rows}
    />
  );
}
