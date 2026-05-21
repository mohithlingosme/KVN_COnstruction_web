import { useState } from "react";
import { Calendar, Clock, User, Phone, MessageSquare, CheckCircle2 } from "lucide-react";

const services = [
  "Residential Construction",
  "Commercial Construction",
  "Renovation & Remodeling",
  "Interior Design",
  "Documentation & Approvals",
  "Financial Assistance",
  "Site Inspection",
  "Other",
];

const timeSlots = ["9:00 AM", "10:00 AM", "11:00 AM", "12:00 PM", "2:00 PM", "3:00 PM", "4:00 PM", "5:00 PM"];

export function BookingSection() {
  const [form, setForm] = useState({
    name: "",
    phone: "",
    service: "",
    date: "",
    time: "",
    message: "",
  });
  const [submitted, setSubmitted] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const validate = () => {
    const e: Record<string, string> = {};
    if (!form.name.trim()) e.name = "Name is required";
    if (!form.phone.trim() || !/^\d{10}$/.test(form.phone.replace(/\s/g, "")))
      e.phone = "Valid 10-digit phone required";
    if (!form.service) e.service = "Please select a service";
    if (!form.date) e.date = "Please pick a date";
    if (!form.time) e.time = "Please pick a time";
    return e;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const errs = validate();
    if (Object.keys(errs).length > 0) { setErrors(errs); return; }
    setSubmitted(true);
  };

  const update = (key: string, value: string) => {
    setForm((f) => ({ ...f, [key]: value }));
    setErrors((e) => { const n = { ...e }; delete n[key]; return n; });
  };

  const today = new Date().toISOString().split("T")[0];

  if (submitted) {
    return (
      <section id="booking" className="py-20 bg-amber-50">
        <div className="max-w-lg mx-auto px-4 text-center">
          <CheckCircle2 className="w-16 h-16 text-green-500 mx-auto mb-4" />
          <h2 className="text-slate-900 mb-2" style={{ fontSize: "1.8rem", fontWeight: 700 }}>
            Appointment Booked!
          </h2>
          <p className="text-slate-600 mb-2">
            Thank you, <strong>{form.name}</strong>! We've received your booking for{" "}
            <strong>{form.date}</strong> at <strong>{form.time}</strong>.
          </p>
          <p className="text-slate-500 text-sm mb-6">
            Our team will confirm your appointment via WhatsApp and call within 1 hour.
          </p>
          <a
            href={`https://wa.me/919876543210?text=Hi%2C%20I%20booked%20an%20appointment%20for%20${form.date}%20at%20${form.time}`}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition-colors"
          >
            Confirm via WhatsApp
          </a>
        </div>
      </section>
    );
  }

  return (
    <section id="booking" className="py-20 bg-amber-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
          {/* Left */}
          <div>
            <span className="text-amber-600 text-sm tracking-widest uppercase">Book Now</span>
            <h2 className="text-slate-900 mt-2 mb-4" style={{ fontSize: "2rem", fontWeight: 700 }}>
              Schedule a Free Consultation
            </h2>
            <p className="text-slate-600 mb-8">
              Meet our construction experts at your site or at our Bengaluru office. We'll assess
              your requirements and provide a detailed plan — completely free.
            </p>

            {/* What to expect */}
            <div className="space-y-4">
              {[
                { icon: User, title: "Meet Our Expert", desc: "Our senior architect/engineer visits your site or meets at our office." },
                { icon: Calculator, title: "Detailed Assessment", desc: "We evaluate your plot, requirements, and provide a precise cost breakdown." },
                { icon: Calendar, title: "Project Plan", desc: "Receive a custom timeline, milestone plan, and package recommendation." },
              ].map(({ icon: Icon, title, desc }) => (
                <div key={title} className="flex items-start gap-4">
                  <div className="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <Icon className="w-5 h-5 text-amber-600" />
                  </div>
                  <div>
                    <div className="text-slate-900 text-sm" style={{ fontWeight: 600 }}>{title}</div>
                    <div className="text-slate-500 text-sm">{desc}</div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Form */}
          <form
            onSubmit={handleSubmit}
            className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-4"
          >
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-slate-700 text-sm mb-1.5">Full Name *</label>
                <div className="relative">
                  <User className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <input
                    type="text"
                    placeholder="Rajesh Kumar"
                    value={form.name}
                    onChange={(e) => update("name", e.target.value)}
                    className={`w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-400 ${errors.name ? "border-red-400" : "border-gray-200"}`}
                  />
                </div>
                {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
              </div>
              <div>
                <label className="block text-slate-700 text-sm mb-1.5">Phone Number *</label>
                <div className="relative">
                  <Phone className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <input
                    type="tel"
                    placeholder="9876543210"
                    value={form.phone}
                    onChange={(e) => update("phone", e.target.value)}
                    className={`w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-400 ${errors.phone ? "border-red-400" : "border-gray-200"}`}
                  />
                </div>
                {errors.phone && <p className="text-red-500 text-xs mt-1">{errors.phone}</p>}
              </div>
            </div>

            <div>
              <label className="block text-slate-700 text-sm mb-1.5">Service Required *</label>
              <select
                value={form.service}
                onChange={(e) => update("service", e.target.value)}
                className={`w-full px-4 py-2.5 border rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-400 bg-white ${errors.service ? "border-red-400" : "border-gray-200"}`}
              >
                <option value="">Select a service…</option>
                {services.map((s) => (
                  <option key={s} value={s}>{s}</option>
                ))}
              </select>
              {errors.service && <p className="text-red-500 text-xs mt-1">{errors.service}</p>}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-slate-700 text-sm mb-1.5">Preferred Date *</label>
                <div className="relative">
                  <Calendar className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <input
                    type="date"
                    min={today}
                    value={form.date}
                    onChange={(e) => update("date", e.target.value)}
                    className={`w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-400 ${errors.date ? "border-red-400" : "border-gray-200"}`}
                  />
                </div>
                {errors.date && <p className="text-red-500 text-xs mt-1">{errors.date}</p>}
              </div>
              <div>
                <label className="block text-slate-700 text-sm mb-1.5">Preferred Time *</label>
                <div className="relative">
                  <Clock className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <select
                    value={form.time}
                    onChange={(e) => update("time", e.target.value)}
                    className={`w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-400 bg-white ${errors.time ? "border-red-400" : "border-gray-200"}`}
                  >
                    <option value="">Pick a slot…</option>
                    {timeSlots.map((t) => (
                      <option key={t} value={t}>{t}</option>
                    ))}
                  </select>
                </div>
                {errors.time && <p className="text-red-500 text-xs mt-1">{errors.time}</p>}
              </div>
            </div>

            <div>
              <label className="block text-slate-700 text-sm mb-1.5">Message (Optional)</label>
              <div className="relative">
                <MessageSquare className="absolute left-3 top-3 w-4 h-4 text-slate-400" />
                <textarea
                  placeholder="Tell us about your project — plot size, location, budget range…"
                  rows={3}
                  value={form.message}
                  onChange={(e) => update("message", e.target.value)}
                  className="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-400 resize-none"
                />
              </div>
            </div>

            <button
              type="submit"
              className="w-full bg-amber-500 hover:bg-amber-600 text-white py-3.5 rounded-xl transition-colors"
              style={{ fontWeight: 600 }}
            >
              Book Free Consultation
            </button>
            <p className="text-center text-slate-400 text-xs">
              No spam. We respect your privacy. We'll only contact you about your project.
            </p>
          </form>
        </div>
      </div>
    </section>
  );
}

// Fix missing import
function Calculator({ className }: { className?: string }) {
  return (
    <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2}>
      <rect x="4" y="2" width="16" height="20" rx="2" />
      <line x1="8" y1="6" x2="16" y2="6" />
      <line x1="8" y1="10" x2="8" y2="10" strokeLinecap="round" strokeWidth={3} />
      <line x1="12" y1="10" x2="12" y2="10" strokeLinecap="round" strokeWidth={3} />
      <line x1="16" y1="10" x2="16" y2="10" strokeLinecap="round" strokeWidth={3} />
      <line x1="8" y1="14" x2="8" y2="14" strokeLinecap="round" strokeWidth={3} />
      <line x1="12" y1="14" x2="12" y2="14" strokeLinecap="round" strokeWidth={3} />
      <line x1="16" y1="14" x2="16" y2="18" strokeLinecap="round" strokeWidth={3} />
      <line x1="8" y1="18" x2="8" y2="18" strokeLinecap="round" strokeWidth={3} />
      <line x1="12" y1="18" x2="12" y2="18" strokeLinecap="round" strokeWidth={3} />
    </svg>
  );
}
