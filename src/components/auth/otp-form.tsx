"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useForm } from "react-hook-form";
import { toast } from "sonner";
import { z } from "zod";

import { createSupabaseBrowserClient } from "@/lib/supabase/client";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

const otpSchema = z.object({
  email: z.string().email(),
});

type OTPValues = z.infer<typeof otpSchema>;

export function OTPForm() {
  const form = useForm<OTPValues>({
    resolver: zodResolver(otpSchema),
    defaultValues: {
      email: "",
    },
  });

  async function onSubmit(values: OTPValues) {
    const supabase = createSupabaseBrowserClient();
    const { error } = await supabase.auth.signInWithOtp({
      email: values.email,
      options: {
        emailRedirectTo: `${window.location.origin}/client`,
      },
    });

    if (error) {
      toast.error(error.message);
      return;
    }

    toast.success("OTP login link sent.");
  }

  return (
    <Card className="w-full max-w-lg">
      <CardHeader>
        <CardTitle className="text-4xl">OTP Login</CardTitle>
      </CardHeader>
      <CardContent>
        <form className="grid gap-4" onSubmit={form.handleSubmit(onSubmit)}>
          <div>
            <Label htmlFor="email">Email</Label>
            <Input id="email" type="email" {...form.register("email")} />
          </div>
          <Button type="submit">Send OTP Link</Button>
        </form>
      </CardContent>
    </Card>
  );
}
