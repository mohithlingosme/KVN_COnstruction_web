import { useState } from "react";
import { ChevronDown } from "lucide-react";

const faqs = [
  {
    q: "What is the construction cost per sqft in Bengaluru?",
    a: "Construction cost in Bengaluru typically ranges from ₹1,800–3,500 per sqft depending on material quality, location, and design. Our Basic package starts at ₹1,800/sqft, Premium at ₹2,200/sqft, and Luxury at ₹3,000+/sqft. Use our free Cost Estimator for a personalized budget.",
  },
  {
    q: "How long does home construction take in Bengaluru?",
    a: "A ground floor (G+0) takes 6–8 months, G+1 (2 floors) takes 10–14 months, and G+2 (3 floors) takes 14–18 months. Timeline depends on approvals, design complexity, and weather. We provide a detailed milestone schedule before work begins.",
  },
  {
    q: "Do you handle BBMP approvals and plan sanctions?",
    a: "Yes, absolutely. We handle the complete BBMP approval process including building plan submission, dimensional checks, setback compliance, and occupancy certificates. This is included in all our packages. Our experienced team ensures zero violations.",
  },
  {
    q: "Can I track my construction progress online?",
    a: "Yes! After signing the contract, you get access to our Client Portal where you can view real-time progress photos, milestone completion percentages, upcoming tasks, invoices, and project documents — from anywhere, including abroad (NRI clients welcome).",
  },
  {
    q: "What payment modes do you accept?",
    a: "We accept bank transfers, UPI, cheque, and online payments via Razorpay. Payments are milestone-based (not upfront). Typically: 10% at signing, 30% at foundation, 30% at slab, 20% at finishing, and 10% at handover.",
  },
  {
    q: "Do you provide home loan assistance?",
    a: "Yes. We have tie-ups with leading banks (SBI, HDFC, ICICI) and can assist with home loan applications, documentation, and EMI calculations. Our team can connect you with our finance partner within 24 hours.",
  },
  {
    q: "Is Vastu compliance included?",
    a: "All our house designs are built with Vastu principles as the baseline. If you have specific Vastu requirements, our architects work with your family Vastu consultant to incorporate them into the floor plan at no extra cost.",
  },
  {
    q: "What warranty do you provide?",
    a: "Basic packages come with 1-year workmanship warranty, Premium with 2-year structural warranty, and Luxury with 5-year comprehensive warranty covering structure, waterproofing, and electrical. Structural defects are covered for 10 years as per law.",
  },
];

export function FAQSection() {
  const [openIdx, setOpenIdx] = useState<number | null>(0);

  return (
    <section id="faq" className="py-20 bg-white">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-12">
          <span className="text-amber-600 text-sm tracking-widest uppercase">FAQ</span>
          <h2 className="text-slate-900 mt-2" style={{ fontSize: "2rem", fontWeight: 700 }}>
            Frequently Asked Questions
          </h2>
          <p className="text-slate-600 mt-3">
            Everything you need to know about building your home in Bengaluru.
          </p>
        </div>

        <div className="space-y-3">
          {faqs.map((faq, idx) => (
            <div
              key={idx}
              className={`border rounded-xl overflow-hidden transition-all ${
                openIdx === idx ? "border-amber-300 shadow-sm" : "border-gray-200"
              }`}
            >
              <button
                onClick={() => setOpenIdx(openIdx === idx ? null : idx)}
                className="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-amber-50/50 transition-colors"
              >
                <span
                  className={`text-sm ${openIdx === idx ? "text-amber-700" : "text-slate-800"}`}
                  style={{ fontWeight: 600 }}
                >
                  {faq.q}
                </span>
                <ChevronDown
                  className={`w-5 h-5 flex-shrink-0 ml-3 transition-transform ${
                    openIdx === idx ? "rotate-180 text-amber-500" : "text-slate-400"
                  }`}
                />
              </button>
              {openIdx === idx && (
                <div className="px-5 pb-4 text-slate-600 text-sm leading-relaxed border-t border-amber-100 pt-3">
                  {faq.a}
                </div>
              )}
            </div>
          ))}
        </div>

        <p className="text-center text-slate-500 text-sm mt-8">
          Still have questions?{" "}
          <a
            href="https://wa.me/919876543210"
            target="_blank"
            rel="noopener noreferrer"
            className="text-amber-600 hover:underline"
          >
            Chat with us on WhatsApp
          </a>{" "}
          or call{" "}
          <a href="tel:+919876543210" className="text-amber-600 hover:underline">
            +91 98765 43210
          </a>
        </p>
      </div>
    </section>
  );
}
