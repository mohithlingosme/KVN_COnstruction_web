"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation } from "@tanstack/react-query";
import { Calculator, Clock3, PackageCheck } from "lucide-react";
import { useForm } from "react-hook-form";
import { z } from "zod";

import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Select } from "@/components/ui/select";
import { estimatorSchema } from "@/lib/validators";
import { formatCurrency } from "@/lib/utils";

type EstimatorValues = z.infer<typeof estimatorSchema>;

export function CostEstimatorForm() {
  const form = useForm<EstimatorValues>({
    resolver: zodResolver(estimatorSchema),
    defaultValues: {
      plotSize: 2400,
      floors: 2,
      materialQuality: "premium",
      location: "east-bengaluru",
      interiorRequirements: "essential",
    },
  });

  const mutation = useMutation({
    mutationFn: async (values: EstimatorValues) => {
      const response = await fetch("/api/estimator", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(values),
      });
      return response.json();
    },
  });

  return (
    <div className="grid gap-6 lg:grid-cols-[1.05fr,0.95fr]">
      <Card>
        <CardHeader>
          <CardTitle className="text-3xl">Smart cost estimator</CardTitle>
        </CardHeader>
        <CardContent>
          <form
            className="grid gap-4"
            onSubmit={form.handleSubmit((values) => mutation.mutate(values))}
          >
            <div>
              <Label>Plot Size (sqft)</Label>
              <input
                type="range"
                min={400}
                max={8000}
                step={100}
                className="mt-3 w-full accent-[#a86f3e]"
                {...form.register("plotSize", { valueAsNumber: true })}
              />
              <p className="mt-2 text-sm text-muted-foreground">
                {form.watch("plotSize")} sqft planned footprint
              </p>
            </div>
            <div>
              <Label>Floors</Label>
              <Select {...form.register("floors", { valueAsNumber: true })}>
                {[1, 2, 3, 4, 5].map((level) => (
                  <option key={level} value={level}>
                    {level}
                  </option>
                ))}
              </Select>
            </div>
            <div className="grid gap-4 md:grid-cols-2">
              <div>
                <Label>Material Quality</Label>
                <Select {...form.register("materialQuality")}>
                  <option value="standard">Standard</option>
                  <option value="premium">Premium</option>
                  <option value="luxury">Luxury</option>
                </Select>
              </div>
              <div>
                <Label>Location</Label>
                <Select {...form.register("location")}>
                  <option value="north-bengaluru">North Bengaluru</option>
                  <option value="east-bengaluru">East Bengaluru</option>
                  <option value="south-bengaluru">South Bengaluru</option>
                  <option value="central-bengaluru">Central Bengaluru</option>
                </Select>
              </div>
            </div>
            <div>
              <Label>Interior Requirements</Label>
              <Select {...form.register("interiorRequirements")}>
                <option value="shell">Shell Only</option>
                <option value="essential">Essential Interiors</option>
                <option value="full-luxury">Full Luxury Interiors</option>
              </Select>
            </div>
            <Button type="submit" size="lg" disabled={mutation.isPending}>
              {mutation.isPending ? "Calculating..." : "Generate Estimate"}
            </Button>
          </form>
        </CardContent>
      </Card>
      <Card className="bg-brand-night text-white dark:border-white/10 dark:bg-brand-night">
        <CardHeader>
          <CardTitle className="text-3xl">Planning output</CardTitle>
        </CardHeader>
        <CardContent className="space-y-5">
          <div className="rounded-[1.5rem] bg-white/5 p-5">
            <div className="flex items-center gap-3 text-brand-sand">
              <Calculator className="h-5 w-5" />
              <span className="text-sm uppercase tracking-[0.2em]">Approximate Budget</span>
            </div>
            <p className="mt-3 font-display text-4xl">
              {mutation.data?.budget ? formatCurrency(mutation.data.budget) : "₹0"}
            </p>
          </div>
          <div className="grid gap-4 md:grid-cols-2">
            <div className="rounded-[1.5rem] bg-white/5 p-5">
              <div className="flex items-center gap-3 text-brand-sand">
                <Clock3 className="h-5 w-5" />
                <span className="text-sm uppercase tracking-[0.2em]">Timeline</span>
              </div>
              <p className="mt-3 text-2xl font-semibold">
                {mutation.data?.timelineMonths || 0} months
              </p>
            </div>
            <div className="rounded-[1.5rem] bg-white/5 p-5">
              <div className="flex items-center gap-3 text-brand-sand">
                <PackageCheck className="h-5 w-5" />
                <span className="text-sm uppercase tracking-[0.2em]">Recommended</span>
              </div>
              <p className="mt-3 text-2xl font-semibold">
                {mutation.data?.recommendation || "Awaiting input"}
              </p>
            </div>
          </div>
          <p className="rounded-[1.5rem] border border-white/10 p-5 text-sm text-white/80">
            {mutation.data?.materialEstimate ||
              "Submit inputs to receive a material strategy, planning range, and package direction."}
          </p>
        </CardContent>
      </Card>
    </div>
  );
}
