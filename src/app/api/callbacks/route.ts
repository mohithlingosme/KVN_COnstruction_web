import { NextResponse } from "next/server";

import { callbackSchema } from "@/lib/validators";
import { sendWhatsAppTemplate } from "@/lib/integrations/whatsapp";

export async function POST(request: Request) {
  const json = await request.json();
  const parsed = callbackSchema.safeParse(json);

  if (!parsed.success) {
    return NextResponse.json({ error: "Invalid callback payload" }, { status: 400 });
  }

  await sendWhatsAppTemplate({
    to: process.env.WHATSAPP_ALERT_NUMBER || "919876543210",
    body: `Callback requested by ${parsed.data.name} at ${parsed.data.preferredSlot}.`,
  });

  return NextResponse.json({
    ok: true,
    callback: parsed.data,
  });
}
