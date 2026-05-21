import { addMinutes, formatISO } from "date-fns";
import { NextResponse } from "next/server";

import { appointmentSchema } from "@/lib/validators";
import { createCalendarEvent } from "@/lib/integrations/calendar";
import { sendWhatsAppTemplate } from "@/lib/integrations/whatsapp";

export async function POST(request: Request) {
  const json = await request.json();
  const parsed = appointmentSchema.safeParse(json);

  if (!parsed.success) {
    return NextResponse.json({ error: "Invalid appointment payload" }, { status: 400 });
  }

  const start = new Date(`${parsed.data.date}T${parsed.data.time}:00+05:30`);
  const end = addMinutes(start, 60);

  const [calendarResult] = await Promise.allSettled([
    createCalendarEvent({
      title: `${parsed.data.service} Consultation`,
      description: parsed.data.notes || "Construction consultation",
      start: formatISO(start),
      end: formatISO(end),
      attendees: [parsed.data.email],
    }),
    sendWhatsAppTemplate({
      to: process.env.WHATSAPP_ALERT_NUMBER || "919876543210",
      body: `Appointment booked: ${parsed.data.name} for ${parsed.data.service} on ${parsed.data.date} ${parsed.data.time}.`,
    }),
  ]);

  return NextResponse.json({
    ok: true,
    appointment: parsed.data,
    calendar:
      calendarResult.status === "fulfilled" ? calendarResult.value : { ok: false },
  });
}
