import { DataTable } from "@/components/dashboard/data-table";
import { clientPayments } from "@/data/dashboard";

export default function ClientPaymentsPage() {
  return (
    <DataTable
      title="Payments and invoices"
      columns={[
        { key: "invoice", title: "Invoice" },
        { key: "amount", title: "Amount" },
        { key: "dueDate", title: "Due date" },
        { key: "status", title: "Status" },
      ]}
      rows={clientPayments.map((item) => ({
        ...item,
        amount: `₹${item.amount.toLocaleString("en-IN")}`,
      }))}
    />
  );
}
