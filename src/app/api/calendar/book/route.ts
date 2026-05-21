import { NextResponse } from "next/server";
import { z } from "zod";

import { createCalendarEvent } from "@/lib/integrations/calendar";

const calendarSchema = z.object({
  title: z.string().min(2),
  description: z.string().min(2),
  start: z.string().min(2),
  end: z.string().min(2),
  attendees: z.array(z.string().email()).default([]),
});

export async function POST(request: Request) {
  const json = await request.json();
  const parsed = calendarSchema.safeParse(json);

  if (!parsed.success) {
    return NextResponse.json({ error: "Invalid calendar payload" }, { status: 400 });
  }

  const result = await createCalendarEvent(parsed.data);
  return NextResponse.json(result);
}
