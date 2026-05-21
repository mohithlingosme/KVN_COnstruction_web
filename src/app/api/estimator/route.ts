import { NextResponse } from "next/server";

import { calculateEstimate } from "@/lib/estimators";
import { estimatorSchema } from "@/lib/validators";

export async function POST(request: Request) {
  const json = await request.json();
  const parsed = estimatorSchema.safeParse(json);

  if (!parsed.success) {
    return NextResponse.json({ error: "Invalid estimator payload" }, { status: 400 });
  }

  return NextResponse.json(calculateEstimate(parsed.data));
}
