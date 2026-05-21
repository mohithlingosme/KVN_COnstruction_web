import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

type Column<T> = {
  key: keyof T;
  title: string;
};

export function DataTable<T extends Record<string, string>>({
  title,
  columns,
  rows,
}: {
  title: string;
  columns: Column<T>[];
  rows: T[];
}) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>{title}</CardTitle>
      </CardHeader>
      <CardContent className="overflow-x-auto">
        <table className="min-w-full text-left text-sm">
          <thead className="text-muted-foreground">
            <tr>
              {columns.map((column) => (
                <th key={String(column.key)} className="pb-4 pr-4 font-medium">
                  {column.title}
                </th>
              ))}
            </tr>
          </thead>
          <tbody className="divide-y divide-border">
            {rows.map((row, index) => (
              <tr key={index}>
                {columns.map((column) => (
                  <td key={String(column.key)} className="py-4 pr-4 text-foreground">
                    {row[column.key]}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </CardContent>
    </Card>
  );
}
