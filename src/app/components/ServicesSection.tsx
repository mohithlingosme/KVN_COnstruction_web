import { Home, Building, Hammer, Paintbrush, FileText, IndianRupee } from "lucide-react";

const services = [
  {
    icon: Home,
    title: "Residential Construction",
    desc: "Villas, row-houses, bungalows, and 2BHK/3BHK homes built to your vision with Vastu compliance and smart-home readiness.",
    highlights: ["Custom floor plans", "Vastu compliant", "Smart home ready"],
  },
  {
    icon: Building,
    title: "Commercial Construction",
    desc: "Office buildings, retail spaces, and commercial complexes built with precision engineering and modern aesthetics.",
    highlights: ["Turnkey solutions", "BBMP approved", "Energy efficient"],
  },
  {
    icon: Hammer,
    title: "Renovation & Remodeling",
    desc: "Breathe new life into your existing structure. Kitchen remodels, bathroom upgrades, structural extensions — all with minimal disruption.",
    highlights: ["Structural repair", "Extension work", "Heritage renovation"],
  },
  {
    icon: Paintbrush,
    title: "Interior Design",
    desc: "End-to-end interior design and execution — from modular kitchens and wardrobes to false ceilings and premium finishes.",
    highlights: ["Modular kitchens", "False ceilings", "Premium finishes"],
  },
  {
    icon: FileText,
    title: "Documentation & Approvals",
    desc: "Complete assistance with BBMP approvals, building plan sanctions, and all statutory permissions required in Bengaluru.",
    highlights: ["BBMP approval", "Plan sanction", "Occupancy certificate"],
  },
  {
    icon: IndianRupee,
    title: "Financial Assistance",
    desc: "Get connected to leading banks for home loans, EMI calculations, and tie-ups with finance partners for easy project financing.",
    highlights: ["Home loan help", "EMI calculator", "Finance tie-ups"],
  },
];

export function ServicesSection() {
  const scrollTo = (href: string) => {
    const el = document.querySelector(href);
    if (el) el.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <section id="services" className="py-20 bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-14">
          <span className="text-amber-600 text-sm tracking-widest uppercase">What We Offer</span>
          <h2 className="text-slate-900 mt-2" style={{ fontSize: "2rem", fontWeight: 700 }}>
            Comprehensive Construction Services
          </h2>
          <p className="text-slate-600 mt-3 max-w-2xl mx-auto">
            From foundation to finishing — we handle every aspect of your construction project in Bengaluru.
          </p>
        </div>

        {/* Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {services.map((svc) => {
            const Icon = svc.icon;
            return (
              <div
                key={svc.title}
                className="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md hover:border-amber-200 transition-all group"
              >
                <div className="w-12 h-12 bg-amber-50 group-hover:bg-amber-100 rounded-xl flex items-center justify-center mb-4 transition-colors">
                  <Icon className="w-6 h-6 text-amber-600" />
                </div>
                <h3 className="text-slate-900 mb-2" style={{ fontSize: "1.05rem", fontWeight: 600 }}>
                  {svc.title}
                </h3>
                <p className="text-slate-500 text-sm mb-4 leading-relaxed">{svc.desc}</p>
                <ul className="space-y-1">
                  {svc.highlights.map((h) => (
                    <li key={h} className="flex items-center gap-2 text-sm text-slate-600">
                      <span className="w-1.5 h-1.5 bg-amber-500 rounded-full" />
                      {h}
                    </li>
                  ))}
                </ul>
              </div>
            );
          })}
        </div>

        <div className="text-center mt-10">
          <button
            onClick={() => scrollTo("#contact")}
            className="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-lg transition-colors"
          >
            Discuss Your Project
          </button>
        </div>
      </div>
    </section>
  );
}
