import { CallbackForm } from "@/components/forms/callback-form";
import { PageHero } from "@/components/common/page-hero";
import { buildMetadata } from "@/lib/metadata";

export const metadata = buildMetadata({
  title: "Callback Request",
  path: "/callback",
  description: "Request a callback from the KVN Construction team.",
});

export default function CallbackPage() {
  return (
    <>
      <PageHero
        eyebrow="Callback Request"
        title="Fast callback capture for high-intent inquiries."
        description="A lightweight conversion path for mobile visitors and WhatsApp traffic."
      />
      <section className="container py-16">
        <CallbackForm />
      </section>
    </>
  );
}
