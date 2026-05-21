import { NextResponse } from "next/server";
import { z } from "zod";

const uploadSchema = z.object({
  fileName: z.string().min(2),
  bucket: z.string().min(2),
});

export async function POST(request: Request) {
  const json = await request.json();
  const parsed = uploadSchema.safeParse(json);

  if (!parsed.success) {
    return NextResponse.json({ error: "Invalid upload request" }, { status: 400 });
  }

  return NextResponse.json({
    ok: true,
    message:
      "Connect Supabase Storage signed upload generation here. This endpoint is scaffolded for production use.",
    request: parsed.data,
  });
}
