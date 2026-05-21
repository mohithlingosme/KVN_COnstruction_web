"use client";

import {
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Line,
  LineChart,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from "recharts";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

export function RevenueChart({
  data,
}: {
  data: { month: string; revenue: number; collections: number }[];
}) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Revenue vs collections</CardTitle>
      </CardHeader>
      <CardContent className="h-[320px]">
        <ResponsiveContainer width="100%" height="100%">
          <LineChart data={data}>
            <CartesianGrid strokeDasharray="3 3" stroke="rgba(148,163,184,0.18)" />
            <XAxis dataKey="month" stroke="currentColor" className="text-xs" />
            <YAxis stroke="currentColor" className="text-xs" />
            <Tooltip />
            <Line type="monotone" dataKey="revenue" stroke="#a86f3e" strokeWidth={3} />
            <Line type="monotone" dataKey="collections" stroke="#6f7463" strokeWidth={3} />
          </LineChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  );
}

export function FunnelChart({
  data,
}: {
  data: { name: string; value: number }[];
}) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Lead funnel</CardTitle>
      </CardHeader>
      <CardContent className="h-[320px]">
        <ResponsiveContainer width="100%" height="100%">
          <BarChart data={data}>
            <CartesianGrid strokeDasharray="3 3" stroke="rgba(148,163,184,0.18)" />
            <XAxis dataKey="name" stroke="currentColor" className="text-xs" />
            <YAxis stroke="currentColor" className="text-xs" />
            <Tooltip />
            <Bar dataKey="value" radius={[16, 16, 0, 0]}>
              {data.map((entry, index) => (
                <Cell
                  key={entry.name}
                  fill={index % 2 === 0 ? "#a86f3e" : "#6f7463"}
                />
              ))}
            </Bar>
          </BarChart>
        </ResponsiveContainer>
      </CardContent>
    </Card>
  );
}

export function ProgressDonut({ value }: { value: number }) {
  const data = [
    { name: "Done", value },
    { name: "Pending", value: 100 - value },
  ];

  return (
    <Card>
      <CardHeader>
        <CardTitle>Project completion</CardTitle>
      </CardHeader>
      <CardContent className="h-[320px]">
        <ResponsiveContainer width="100%" height="100%">
          <PieChart>
            <Pie
              data={data}
              innerRadius={78}
              outerRadius={110}
              paddingAngle={4}
              dataKey="value"
            >
              <Cell fill="#a86f3e" />
              <Cell fill="rgba(148,163,184,0.16)" />
            </Pie>
            <Tooltip />
          </PieChart>
        </ResponsiveContainer>
        <p className="-mt-36 text-center font-display text-5xl">{value}%</p>
      </CardContent>
    </Card>
  );
}
