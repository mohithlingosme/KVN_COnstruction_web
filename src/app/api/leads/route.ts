import { NextResponse } from "next/server";

import { leadSchema } from "@/lib/validators";
import { sendAutomationEmail } from "@/lib/integrations/email";
import { sendWhatsAppTemplate } from "@/lib/integrations/whatsapp";

export async function POST(request: Request) {
  const json = await request.json();
  const parsed = leadSchema.safeParse(json);

  if (!parsed.success) {
    return NextResponse.json(
      { error: "Invalid lead payload", issues: parsed.error.flatten() },
      { status: 400 },
    );
  }

  await Promise.allSettled([
    sendAutomationEmail({
      to: process.env.NOTIFICATION_EMAIL || "sales@kvnconstruction.in",
      subject: `New lead from ${parsed.data.name}`,
      html: `<p>${parsed.data.requirement}</p>`,
    }),
    sendWhatsAppTemplate({
      to: process.env.WHATSAPP_ALERT_NUMBER || "919876543210",
      body: `New lead: ${parsed.data.name} • ${parsed.data.phone} • ${parsed.data.budgetRange}`,
    }),
  ]);

  return NextResponse.json({
    ok: true,
    message: "Lead captured successfully.",
    lead: parsed.data,
  });
}
