import { CheckCircle2, Circle, Clock3 } from "lucide-react";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function TimelinePanel({
  items,
}: {
  items: { stage: string; status: string; date: string }[];
}) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Milestone timeline</CardTitle>
      </CardHeader>
      <CardContent className="space-y-5">
        {items.map((item) => (
          <div key={item.stage} className="flex gap-4">
            <div className="mt-1 text-brand-clay">
              {item.status === "done" ? (
                <CheckCircle2 className="h-5 w-5" />
              ) : item.status === "active" ? (
                <Clock3 className="h-5 w-5" />
              ) : (
                <Circle className="h-5 w-5" />
              )}
            </div>
            <div>
              <p className="font-medium">{item.stage}</p>
              <p className="text-sm text-muted-foreground">{item.date}</p>
            </div>
          </div>
        ))}
      </CardContent>
    </Card>
  );
}
