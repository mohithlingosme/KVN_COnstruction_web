import { NextResponse } from "next/server";
import { z } from "zod";

import { sendWhatsAppTemplate } from "@/lib/integrations/whatsapp";

const whatsappSchema = z.object({
  to: z.string().min(10),
  body: z.string().min(2),
});

export async function POST(request: Request) {
  const json = await request.json();
  const parsed = whatsappSchema.safeParse(json);

  if (!parsed.success) {
    return NextResponse.json({ error: "Invalid WhatsApp payload" }, { status: 400 });
  }

  const result = await sendWhatsAppTemplate(parsed.data);
  return NextResponse.json(result);
}
