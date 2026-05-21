import { Check, Star } from "lucide-react";

const packages = [
  {
    name: "Basic",
    tagline: "Solid foundation. Smart value.",
    rate: "₹1,800–2,000",
    unit: "per sqft",
    highlight: false,
    features: [
      "AAC/Red brick structure",
      "Standard cement & tiles",
      "Basic electrical & plumbing",
      "Simple false ceiling",
      "Standard doors & windows",
      "2-coat exterior paint",
      "BBMP-approved plan",
      "1-year warranty",
    ],
    cta: "Get Basic Quote",
  },
  {
    name: "Premium",
    tagline: "Most popular choice in Bengaluru.",
    rate: "₹2,200–2,500",
    unit: "per sqft",
    highlight: true,
    features: [
      "All Basic features",
      "Superior cement & vitrified tiles",
      "Premium electrical fittings",
      "Designer false ceiling",
      "Hardwood doors & uPVC windows",
      "3-coat premium paint",
      "Modular kitchen",
      "Client portal access",
      "2-year structural warranty",
    ],
    cta: "Get Premium Quote",
  },
  {
    name: "Luxury",
    tagline: "Uncompromising quality. Forever.",
    rate: "₹3,000+",
    unit: "per sqft",
    highlight: false,
    features: [
      "All Premium features",
      "Imported marble & premium flooring",
      "Smart home automation",
      "Bespoke interior design",
      "Landscaping included",
      "Solar panel provision",
      "Dedicated project manager",
      "Priority WhatsApp support",
      "5-year comprehensive warranty",
    ],
    cta: "Get Luxury Quote",
  },
];

export function PackagesSection() {
  const scrollTo = (href: string) => {
    const el = document.querySelector(href);
    if (el) el.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <section id="packages" className="py-20 bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-12">
          <span className="text-amber-600 text-sm tracking-widest uppercase">Pricing</span>
          <h2 className="text-slate-900 mt-2" style={{ fontSize: "2rem", fontWeight: 700 }}>
            Transparent Packages. No Hidden Costs.
          </h2>
          <p className="text-slate-600 mt-3 max-w-2xl mx-auto">
            Choose the package that fits your vision and budget. All include BBMP approvals
            and dedicated project management.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
          {packages.map((pkg) => (
            <div
              key={pkg.name}
              className={`rounded-2xl overflow-hidden border transition-all ${
                pkg.highlight
                  ? "bg-slate-900 border-amber-500 shadow-2xl scale-105"
                  : "bg-white border-gray-200 shadow-sm"
              }`}
            >
              {pkg.highlight && (
                <div className="bg-amber-500 text-white text-center py-1.5 text-xs tracking-widest uppercase">
                  <Star className="inline w-3 h-3 mr-1" />
                  Most Popular
                </div>
              )}

              <div className="p-6">
                <h3
                  className={pkg.highlight ? "text-white" : "text-slate-900"}
                  style={{ fontSize: "1.3rem", fontWeight: 700 }}
                >
                  {pkg.name}
                </h3>
                <p className={`text-sm mt-1 mb-4 ${pkg.highlight ? "text-slate-400" : "text-slate-500"}`}>
                  {pkg.tagline}
                </p>

                <div className="mb-6">
                  <span
                    className={`${pkg.highlight ? "text-amber-400" : "text-slate-900"}`}
                    style={{ fontSize: "1.8rem", fontWeight: 800 }}
                  >
                    {pkg.rate}
                  </span>
                  <span className={`text-sm ml-1 ${pkg.highlight ? "text-slate-400" : "text-slate-500"}`}>
                    {pkg.unit}
                  </span>
                </div>

                <ul className="space-y-2.5 mb-6">
                  {pkg.features.map((f) => (
                    <li key={f} className="flex items-start gap-2.5">
                      <Check
                        className={`w-4 h-4 mt-0.5 flex-shrink-0 ${
                          pkg.highlight ? "text-amber-400" : "text-amber-500"
                        }`}
                      />
                      <span className={`text-sm ${pkg.highlight ? "text-slate-300" : "text-slate-600"}`}>
                        {f}
                      </span>
                    </li>
                  ))}
                </ul>

                <button
                  onClick={() => scrollTo("#booking")}
                  className={`w-full py-3 rounded-xl transition-colors ${
                    pkg.highlight
                      ? "bg-amber-500 hover:bg-amber-600 text-white"
                      : "bg-slate-900 hover:bg-slate-800 text-white"
                  }`}
                  style={{ fontWeight: 600 }}
                >
                  {pkg.cta}
                </button>
              </div>
            </div>
          ))}
        </div>

        <p className="text-center text-slate-500 text-sm mt-8">
          * Rates are indicative. Final pricing depends on plot size, location, and chosen specifications.
          <button
            onClick={() => scrollTo("#estimator")}
            className="text-amber-600 hover:underline ml-1"
          >
            Use our free estimator
          </button>{" "}
          for a personalized figure.
        </p>
      </div>
    </section>
  );
}
