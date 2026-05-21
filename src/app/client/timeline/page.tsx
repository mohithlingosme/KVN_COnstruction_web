import { TimelinePanel } from "@/components/dashboard/timeline-panel";
import { timelineMilestones } from "@/data/dashboard";

export default function ClientTimelinePage() {
  return <TimelinePanel items={timelineMilestones} />;
}
