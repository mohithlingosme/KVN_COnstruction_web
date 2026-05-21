import { NextResponse } from "next/server";
import { z } from "zod";

import { getRazorpayClient } from "@/lib/integrations/razorpay";

const orderSchema = z.object({
  amount: z.number().positive(),
  receipt: z.string().min(3),
});

export async function POST(request: Request) {
  const json = await request.json();
  const parsed = orderSchema.safeParse(json);

  if (!parsed.success) {
    return NextResponse.json({ error: "Invalid payment request" }, { status: 400 });
  }

  const razorpay = getRazorpayClient();

  if (!razorpay) {
    return NextResponse.json({
      ok: false,
      skipped: true,
      message: "Razorpay credentials are not configured.",
    });
  }

  const order = await razorpay.orders.create({
    amount: parsed.data.amount * 100,
    currency: "INR",
    receipt: parsed.data.receipt,
  });

  return NextResponse.json({
    ok: true,
    order,
  });
}
