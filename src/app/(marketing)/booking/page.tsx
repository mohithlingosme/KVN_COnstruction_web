import { AppointmentForm } from "@/components/forms/appointment-form";
import { PageHero } from "@/components/common/page-hero";
import { buildMetadata } from "@/lib/metadata";

export const metadata = buildMetadata({
  title: "Appointment Booking",
  path: "/booking",
  description: "Book consultation calls, site visits, or design meetings with KVN Construction.",
});

export default function BookingPage() {
  return (
    <>
      <PageHero
        eyebrow="Appointment Booking"
        title="Calendar-ready booking flow for consultation and site planning."
        description="Appointments can trigger CRM, WhatsApp, and Google Calendar workflows from the same route."
      />
      <section className="container py-16">
        <AppointmentForm />
      </section>
    </>
  );
}
