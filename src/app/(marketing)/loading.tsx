import { Skeleton } from "@/components/ui/skeleton";

export default function MarketingLoading() {
  return (
    <div className="container py-10">
      <Skeleton className="h-[360px] w-full" />
    </div>
  );
}
