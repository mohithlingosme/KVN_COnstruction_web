"use client";

import { addDays, format } from "date-fns";
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
import { Textarea } from "@/components/ui/textarea";
import { appointmentSchema } from "@/lib/validators";

type AppointmentValues = z.infer<typeof appointmentSchema>;

export function AppointmentForm() {
  const form = useForm<AppointmentValues>({
    resolver: zodResolver(appointmentSchema),
    defaultValues: {
      name: "",
      email: "",
      phone: "",
      service: "Residential Construction",
      date: format(addDays(new Date(), 3), "yyyy-MM-dd"),
      time: "11:00",
      notes: "",
    },
  });

  const mutation = useMutation({
    mutationFn: async (values: AppointmentValues) => {
      const response = await fetch("/api/appointments", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(values),
      });

      if (!response.ok) {
        throw new Error("Appointment submission failed");
      }

      return response.json();
    },
    onSuccess: () => {
      toast.success("Appointment booked. Calendar and WhatsApp workflows were triggered.");
      form.reset();
    },
    onError: () => {
      toast.error("Unable to book appointment right now.");
    },
  });

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-3xl">Book an appointment</CardTitle>
      </CardHeader>
      <CardContent>
        <form
          className="grid gap-4"
          onSubmit={form.handleSubmit((values) => mutation.mutate(values))}
        >
          <div className="grid gap-4 md:grid-cols-2">
            <div>
              <Label htmlFor="appointment-name">Name</Label>
              <Input id="appointment-name" {...form.register("name")} />
            </div>
            <div>
              <Label htmlFor="appointment-phone">Phone</Label>
              <Input id="appointment-phone" {...form.register("phone")} />
            </div>
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            <div>
              <Label htmlFor="appointment-email">Email</Label>
              <Input id="appointment-email" type="email" {...form.register("email")} />
            </div>
            <div>
              <Label htmlFor="appointment-service">Service</Label>
              <Select id="appointment-service" {...form.register("service")}>
                <option>Residential Construction</option>
                <option>Commercial Construction</option>
                <option>Interior Fit-Outs</option>
                <option>Documentation Services</option>
              </Select>
            </div>
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            <div>
              <Label htmlFor="appointment-date">Date</Label>
              <Input id="appointment-date" type="date" {...form.register("date")} />
            </div>
            <div>
              <Label htmlFor="appointment-time">Time</Label>
              <Input id="appointment-time" type="time" {...form.register("time")} />
            </div>
          </div>
          <div>
            <Label htmlFor="appointment-notes">Notes</Label>
            <Textarea id="appointment-notes" {...form.register("notes")} />
          </div>
          <Button type="submit" disabled={mutation.isPending}>
            {mutation.isPending ? "Booking..." : "Confirm Appointment"}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}
