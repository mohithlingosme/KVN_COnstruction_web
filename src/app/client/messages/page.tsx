import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export default function ClientMessagesPage() {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Communication center</CardTitle>
      </CardHeader>
      <CardContent className="space-y-4 text-muted-foreground">
        <p>Use this panel to consolidate project conversations, action approvals, and shared update summaries.</p>
        <p>Production integration can connect this view to Supabase realtime channels, WhatsApp event mirrors, or email thread snapshots.</p>
      </CardContent>
    </Card>
  );
}
