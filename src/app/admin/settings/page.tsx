import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export default function AdminSettingsPage() {
  return (
    <div className="grid gap-6 md:grid-cols-2">
      {[
        "Supabase connection and RLS defaults",
        "Razorpay keys and webhook endpoint",
        "Google Calendar service account settings",
        "WhatsApp Business API configuration",
      ].map((item) => (
        <Card key={item}>
          <CardHeader>
            <CardTitle>{item}</CardTitle>
          </CardHeader>
          <CardContent className="text-muted-foreground">
            Environment-driven configuration placeholder with production setup routed through `.env` and deployment secrets.
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
