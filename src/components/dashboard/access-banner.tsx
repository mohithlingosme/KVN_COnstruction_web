import Link from "next/link";
import { LockKeyhole } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";

export function AccessBanner({
  portal,
}: {
  portal: "admin" | "client";
}) {
  return (
    <Card className="border-dashed">
      <CardContent className="flex flex-col gap-4 p-6 md:flex-row md:items-center md:justify-between">
        <div className="flex items-start gap-3">
          <LockKeyhole className="mt-1 h-5 w-5 text-brand-clay" />
          <div>
            <p className="font-semibold">Preview mode enabled</p>
            <p className="text-sm text-muted-foreground">
              Authentication is not enforced because demo mode is active. Set `NEXT_PUBLIC_ENABLE_DEMO_MODE=false` to require role-based access.
            </p>
          </div>
        </div>
        <Button asChild variant="outline">
          <Link href="/auth">
            {portal === "admin" ? "Admin login" : "Client login"}
          </Link>
        </Button>
      </CardContent>
    </Card>
  );
}
