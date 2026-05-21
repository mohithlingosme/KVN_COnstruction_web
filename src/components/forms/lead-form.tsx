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
import { Textarea } from "@/components/ui/textarea";
import { leadSchema } from "@/lib/validators";

type LeadFormValues = z.infer<typeof leadSchema>;

export function LeadForm() {
  const form = useForm<LeadFormValues>({
    resolver: zodResolver(leadSchema),
    defaultValues: {
      name: "",
      email: "",
      phone: "",
      requirement: "",
      budgetRange: "₹50L-1Cr",
    },
  });

  const mutation = useMutation({
    mutationFn: async (values: LeadFormValues) => {
      const response = await fetch("/api/leads", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(values),
      });

      if (!response.ok) {
        throw new Error("Lead submission failed");
      }

      return response.json();
    },
    onSuccess: () => {
      toast.success("Lead captured. The CRM pipeline has been notified.");
      form.reset();
    },
    onError: () => {
      toast.error("Unable to submit right now. Please retry.");
    },
  });

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-3xl">Request a project consultation</CardTitle>
      </CardHeader>
      <CardContent>
        <form
          className="grid gap-4"
          onSubmit={form.handleSubmit((values) => mutation.mutate(values))}
        >
          <div className="grid gap-4 md:grid-cols-2">
            <div>
              <Label htmlFor="name">Name</Label>
              <Input id="name" {...form.register("name")} />
            </div>
            <div>
              <Label htmlFor="phone">Phone</Label>
              <Input id="phone" {...form.register("phone")} />
            </div>
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            <div>
              <Label htmlFor="email">Email</Label>
              <Input id="email" type="email" {...form.register("email")} />
            </div>
            <div>
              <Label htmlFor="budgetRange">Budget Range</Label>
              <Select id="budgetRange" {...form.register("budgetRange")}>
                <option>₹50L-1Cr</option>
                <option>₹1Cr-2Cr</option>
                <option>₹2Cr-4Cr</option>
                <option>₹4Cr+</option>
              </Select>
            </div>
          </div>
          <div>
            <Label htmlFor="requirement">Requirement</Label>
            <Textarea
              id="requirement"
              placeholder="Tell us about plot location, scale, interiors, or approvals."
              {...form.register("requirement")}
            />
          </div>
          <Button type="submit" disabled={mutation.isPending}>
            {mutation.isPending ? "Submitting..." : "Submit Lead"}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}
