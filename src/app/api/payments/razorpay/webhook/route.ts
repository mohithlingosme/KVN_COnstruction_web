import { NextResponse } from "next/server";

export async function POST(request: Request) {
  const body = await request.text();
  const signature = request.headers.get("x-razorpay-signature");

  return NextResponse.json({
    ok: true,
    received: Boolean(body),
    signaturePresent: Boolean(signature),
    message:
      "Webhook received. Add signature verification and invoice reconciliation logic in production.",
  });
}
