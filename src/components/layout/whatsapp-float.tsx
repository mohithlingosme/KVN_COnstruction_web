import Link from "next/link";
import { MessageCircleMore } from "lucide-react";

import { siteConfig } from "@/lib/constants";

export function WhatsAppFloat() {
  return (
    <Link
      href={`https://wa.me/${siteConfig.whatsappNumber}?text=Hi%2C%20I%27m%20interested%20in%20your%20construction%20services.`}
      className="fixed bottom-6 right-6 z-50 inline-flex h-14 w-14 items-center justify-center rounded-full bg-green-500 text-white shadow-soft transition hover:scale-105"
      target="_blank"
      rel="noreferrer"
      aria-label="Open WhatsApp"
    >
      <MessageCircleMore className="h-6 w-6" />
    </Link>
  );
}
