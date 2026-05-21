import { ArrowDownRight, ArrowUpRight, Minus } from "lucide-react";

import type { DashboardMetric } from "@/types/domain";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

const trendIcon = {
  up: ArrowUpRight,
  down: ArrowDownRight,
  flat: Minus,
};

export function MetricCard({ metric }: { metric: DashboardMetric }) {
  const TrendIcon = trendIcon[metric.trend];

  return (
    <Card>
      <CardHeader>
        <p className="text-sm uppercase tracking-[0.2em] text-muted-foreground">
          {metric.title}
        </p>
        <CardTitle className="text-4xl">{metric.value}</CardTitle>
      </CardHeader>
      <CardContent className="flex items-center gap-2 text-sm text-muted-foreground">
        <TrendIcon className="h-4 w-4 text-brand-clay" />
        <span>{metric.change}</span>
      </CardContent>
    </Card>
  );
}
