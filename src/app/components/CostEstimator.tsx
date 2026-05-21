import { useState } from "react";
import { Calculator, Clock, Package, IndianRupee, MessageCircle } from "lucide-react";

type Material = "Standard" | "Premium" | "Luxury";
type Location = "Suburb" | "City" | "Central";
type Interior = "Basic" | "Premium";

function calcEstimate(
  plotSize: number,
  floors: number,
  material: Material,
  location: Location,
  interior: Interior
) {
  const builtUp = plotSize * floors;

  const baseRates: Record<Material, number> = {
    Standard: 1900,
    Premium: 2300,
    Luxury: 3200,
  };

  const locationFactor: Record<Location, number> = {
    Suburb: 0.95,
    City: 1.0,
    Central: 1.1,
  };

  const interiorFactor: Record<Interior, number> = {
    Basic: 1.0,
    Premium: 1.15,
  };

  const base = builtUp * baseRates[material] * locationFactor[location] * interiorFactor[interior];
  const withGST = base * 1.18;
  const inLakhs = Math.round(withGST / 100000);

  const timelineMonths: Record<number, string> = {
    1: "6–8 months",
    2: "10–14 months",
    3: "14–18 months",
  };
  const timeline = floors >= 3 ? "14–18 months" : timelineMonths[floors] ?? "14–18 months";

  const pkg = material === "Standard" ? "Basic Package" : material === "Premium" ? "Premium Package" : "Luxury Package";

  return { inLakhs, builtUp, timeline, pkg };
}

export function CostEstimator() {
  const [plotSize, setPlotSize] = useState(1200);
  const [floors, setFloors] = useState(2);
  const [material, setMaterial] = useState<Material>("Standard");
  const [location, setLocation] = useState<Location>("City");
  const [interior, setInterior] = useState<Interior>("Basic");
  const [submitted, setSubmitted] = useState(false);
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");

  const result = calcEstimate(plotSize, floors, material, location, interior);

  return (
    <section id="estimator" className="py-20 bg-slate-900">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-12">
          <span className="text-amber-400 text-sm tracking-widest uppercase">Free Tool</span>
          <h2 className="text-white mt-2" style={{ fontSize: "2rem", fontWeight: 700 }}>
            Instant Construction Cost Estimator
          </h2>
          <p className="text-slate-400 mt-3 max-w-xl mx-auto">
            Get an approximate budget for your home in Bengaluru. Rates based on current market
            benchmarks (₹1800–3500/sqft).
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-10 items-start">
          {/* Left: Form */}
          <div className="bg-slate-800 rounded-2xl p-6 sm:p-8">
            {/* Plot Size */}
            <div className="mb-6">
              <label className="text-slate-300 text-sm mb-2 block">
                Plot Size: <span className="text-amber-400">{plotSize.toLocaleString()} sqft</span>
              </label>
              <input
                type="range"
                min={500}
                max={5000}
                step={100}
                value={plotSize}
                onChange={(e) => setPlotSize(Number(e.target.value))}
                className="w-full accent-amber-500"
              />
              <div className="flex justify-between text-xs text-slate-500 mt-1">
                <span>500 sqft</span>
                <span>5,000 sqft</span>
              </div>
            </div>

            {/* Floors */}
            <div className="mb-6">
              <label className="text-slate-300 text-sm mb-2 block">Number of Floors</label>
              <div className="flex gap-3">
                {[1, 2, 3, 4].map((f) => (
                  <button
                    key={f}
                    onClick={() => setFloors(f)}
                    className={`flex-1 py-2.5 rounded-lg text-sm transition-all ${
                      floors === f
                        ? "bg-amber-500 text-white"
                        : "bg-slate-700 text-slate-300 hover:bg-slate-600"
                    }`}
                  >
                    {f}G+{f === 1 ? "" : f - 1}
                  </button>
                ))}
              </div>
            </div>

            {/* Material Quality */}
            <div className="mb-6">
              <label className="text-slate-300 text-sm mb-2 block">Material Quality</label>
              <div className="grid grid-cols-3 gap-3">
                {(["Standard", "Premium", "Luxury"] as Material[]).map((m) => (
                  <button
                    key={m}
                    onClick={() => setMaterial(m)}
                    className={`py-2.5 rounded-lg text-sm transition-all ${
                      material === m
                        ? "bg-amber-500 text-white"
                        : "bg-slate-700 text-slate-300 hover:bg-slate-600"
                    }`}
                  >
                    {m}
                    <div className="text-xs mt-0.5 opacity-75">
                      {m === "Standard" ? "₹1,900/sqft" : m === "Premium" ? "₹2,300/sqft" : "₹3,200/sqft"}
                    </div>
                  </button>
                ))}
              </div>
            </div>

            {/* Location */}
            <div className="mb-6">
              <label className="text-slate-300 text-sm mb-2 block">Location / Zone</label>
              <div className="grid grid-cols-3 gap-3">
                {(["Suburb", "City", "Central"] as Location[]).map((l) => (
                  <button
                    key={l}
                    onClick={() => setLocation(l)}
                    className={`py-2.5 rounded-lg text-sm transition-all ${
                      location === l
                        ? "bg-amber-500 text-white"
                        : "bg-slate-700 text-slate-300 hover:bg-slate-600"
                    }`}
                  >
                    {l}
                    <div className="text-xs mt-0.5 opacity-75">
                      {l === "Suburb" ? "-5%" : l === "City" ? "Standard" : "+10%"}
                    </div>
                  </button>
                ))}
              </div>
            </div>

            {/* Interior */}
            <div className="mb-6">
              <label className="text-slate-300 text-sm mb-2 block">Interior Requirement</label>
              <div className="grid grid-cols-2 gap-3">
                {(["Basic", "Premium"] as Interior[]).map((i) => (
                  <button
                    key={i}
                    onClick={() => setInterior(i)}
                    className={`py-2.5 rounded-lg text-sm transition-all ${
                      interior === i
                        ? "bg-amber-500 text-white"
                        : "bg-slate-700 text-slate-300 hover:bg-slate-600"
                    }`}
                  >
                    {i} Interior
                    <div className="text-xs mt-0.5 opacity-75">
                      {i === "Basic" ? "Standard finish" : "+15% uplift"}
                    </div>
                  </button>
                ))}
              </div>
            </div>
          </div>

          {/* Right: Result + Lead Capture */}
          <div className="space-y-5">
            {/* Result Card */}
            <div className="bg-amber-500 rounded-2xl p-6 sm:p-8 text-white">
              <div className="flex items-center gap-3 mb-4">
                <Calculator className="w-6 h-6" />
                <span style={{ fontWeight: 600 }}>Your Estimated Budget</span>
              </div>
              <div style={{ fontSize: "3rem", fontWeight: 800, lineHeight: 1 }}>
                ₹{result.inLakhs} Lakhs
              </div>
              <p className="text-amber-100 text-sm mt-1">Inclusive of 18% GST (approx.)</p>
              <div className="border-t border-amber-400/50 mt-5 pt-5 grid grid-cols-2 gap-4">
                {[
                  { icon: Package, label: "Built-up Area", value: `${result.builtUp.toLocaleString()} sqft` },
                  { icon: Clock, label: "Timeline", value: result.timeline },
                  { icon: IndianRupee, label: "Recommended", value: result.pkg },
                  { icon: Calculator, label: "Rate Applied", value: `${material} Grade` },
                ].map((d) => {
                  const Icon = d.icon;
                  return (
                    <div key={d.label} className="bg-amber-400/30 rounded-xl p-3">
                      <Icon className="w-4 h-4 mb-1 opacity-80" />
                      <div className="text-xs text-amber-100">{d.label}</div>
                      <div className="text-sm mt-0.5" style={{ fontWeight: 600 }}>{d.value}</div>
                    </div>
                  );
                })}
              </div>
              <p className="text-amber-100 text-xs mt-4">
                * This is an approximate estimate. Final quote depends on site conditions, design, and material choices.
              </p>
            </div>

            {/* Lead Capture */}
            {!submitted ? (
              <div className="bg-slate-800 rounded-2xl p-6">
                <h3 className="text-white mb-1" style={{ fontWeight: 600 }}>
                  Get Your Detailed Quote for Free
                </h3>
                <p className="text-slate-400 text-sm mb-4">
                  Our expert will call you with a precise estimate and site consultation.
                </p>
                <div className="space-y-3">
                  <input
                    type="text"
                    placeholder="Your Name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    className="w-full bg-slate-700 text-white placeholder-slate-400 rounded-lg px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-amber-500"
                  />
                  <input
                    type="tel"
                    placeholder="Phone Number"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="w-full bg-slate-700 text-white placeholder-slate-400 rounded-lg px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-amber-500"
                  />
                  <button
                    onClick={() => { if (name && phone) setSubmitted(true); }}
                    className="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-lg transition-colors"
                    style={{ fontWeight: 600 }}
                  >
                    Send My Estimate
                  </button>
                </div>
              </div>
            ) : (
              <div className="bg-green-900/40 border border-green-500/40 rounded-2xl p-6 text-center">
                <div className="text-green-400 text-3xl mb-2">✓</div>
                <h3 className="text-white" style={{ fontWeight: 600 }}>
                  Request Received!
                </h3>
                <p className="text-slate-400 text-sm mt-1">
                  Our team will contact you within 2 hours with your detailed estimate.
                </p>
                <a
                  href="https://wa.me/919876543210"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="mt-4 inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-5 py-2.5 rounded-lg text-sm transition-colors"
                >
                  <MessageCircle className="w-4 h-4" />
                  Chat on WhatsApp
                </a>
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
