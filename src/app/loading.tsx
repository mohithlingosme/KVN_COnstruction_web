import { Skeleton } from "@/components/ui/skeleton";

export default function RootLoading() {
  return (
    <div className="container py-10">
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-[420px] w-full" />
        <div className="grid gap-6 md:grid-cols-3">
          {Array.from({ length: 3 }).map((_, index) => (
            <Skeleton key={index} className="h-56 w-full" />
          ))}
        </div>
      </div>
    </div>
  );
}
