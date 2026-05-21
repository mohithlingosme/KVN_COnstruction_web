"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation } from "@tanstack/react-query";
import { useForm } from "react-hook-form";
import { toast } from "sonner";
import { z } from "zod";

import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { callbackSchema } from "@/lib/validators";

type CallbackValues = z.infer<typeof callbackSchema>;

export function CallbackForm() {
  const form = useForm<CallbackValues>({
    resolver: zodResolver(callbackSchema),
    defaultValues: {
      name: "",
      phone: "",
      preferredSlot: "10:00 AM - 12:00 PM",
    },
  });

  const mutation = useMutation({
    mutationFn: async (values: CallbackValues) => {
      const response = await fetch("/api/callbacks", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(values),
      });

      if (!response.ok) {
        throw new Error("Callback submission failed");
      }

      return response.json();
    },
    onSuccess: () => {
      toast.success("Callback request queued for the CRM desk.");
      form.reset();
    },
    onError: () => {
      toast.error("Unable to schedule callback. Please retry.");
    },
  });

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-3xl">Request a callback</CardTitle>
      </CardHeader>
      <CardContent>
        <form
          className="grid gap-4"
          onSubmit={form.handleSubmit((values) => mutation.mutate(values))}
        >
          <div>
            <Label htmlFor="callback-name">Name</Label>
            <Input id="callback-name" {...form.register("name")} />
          </div>
          <div>
            <Label htmlFor="callback-phone">Phone</Label>
            <Input id="callback-phone" {...form.register("phone")} />
          </div>
          <div>
            <Label htmlFor="callback-slot">Preferred Slot</Label>
            <Select id="callback-slot" {...form.register("preferredSlot")}>
              <option>10:00 AM - 12:00 PM</option>
              <option>12:00 PM - 2:00 PM</option>
              <option>3:00 PM - 5:00 PM</option>
              <option>6:00 PM - 8:00 PM</option>
            </Select>
          </div>
          <Button type="submit" disabled={mutation.isPending}>
            {mutation.isPending ? "Scheduling..." : "Schedule Callback"}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}
