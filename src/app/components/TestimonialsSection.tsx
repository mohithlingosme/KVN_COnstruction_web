import { Star, Quote } from "lucide-react";

const testimonials = [
  {
    id: 1,
    name: "Rajesh Kumar",
    location: "Whitefield, Bengaluru",
    project: "3BHK Villa, 3200 sqft",
    rating: 5,
    text: "BuildRight delivered our dream villa on time and within budget. The client portal gave us real-time updates — we never had to chase anyone. The quality of construction is exceptional. Highly recommend to anyone building in Bengaluru!",
    avatar: "RK",
  },
  {
    id: 2,
    name: "Priya Menon",
    location: "Koramangala, Bengaluru",
    project: "Row House, 2800 sqft",
    rating: 5,
    text: "Finally a builder who is transparent about costs! No hidden charges, no nasty surprises. The BBMP approval process was handled end-to-end. The cost estimator tool gave us an accurate figure and we signed the contract same day.",
    avatar: "PM",
  },
  {
    id: 3,
    name: "Suresh Rao",
    location: "HSR Layout, Bengaluru",
    project: "Modern Home, 2400 sqft",
    rating: 5,
    text: "I was worried about managing construction while working full-time. Their project manager gave us weekly WhatsApp updates, uploaded photos, and kept everything on schedule. Worth every rupee. House construction cost in Bangalore was exactly as estimated.",
    avatar: "SR",
  },
  {
    id: 4,
    name: "Anitha Sharma",
    location: "Indiranagar, Bengaluru",
    project: "Duplex, 3600 sqft",
    rating: 5,
    text: "We're NRI clients and managing construction remotely seemed daunting. BuildRight's client dashboard made it seamless — we approved milestone payments online, saw progress photos, and got all documents digitally. Absolutely professional!",
    avatar: "AS",
  },
];

export function TestimonialsSection() {
  return (
    <section id="testimonials" className="py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-12">
          <span className="text-amber-600 text-sm tracking-widest uppercase">Client Stories</span>
          <h2 className="text-slate-900 mt-2" style={{ fontSize: "2rem", fontWeight: 700 }}>
            What Our Clients Say
          </h2>
          <p className="text-slate-600 mt-3 max-w-xl mx-auto">
            500+ happy homeowners across Bengaluru. Real stories, real results.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {testimonials.map((t) => (
            <div
              key={t.id}
              className="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:border-amber-200 hover:shadow-md transition-all"
            >
              <div className="flex items-start justify-between mb-4">
                <div className="flex items-center gap-3">
                  <div className="w-11 h-11 bg-amber-500 rounded-full flex items-center justify-center text-white text-sm" style={{ fontWeight: 700 }}>
                    {t.avatar}
                  </div>
                  <div>
                    <div className="text-slate-900 text-sm" style={{ fontWeight: 600 }}>{t.name}</div>
                    <div className="text-slate-500 text-xs">{t.location}</div>
                  </div>
                </div>
                <Quote className="w-8 h-8 text-amber-200 flex-shrink-0" />
              </div>

              <div className="flex gap-0.5 mb-3">
                {Array.from({ length: t.rating }).map((_, i) => (
                  <Star key={i} className="w-4 h-4 fill-amber-400 text-amber-400" />
                ))}
              </div>

              <p className="text-slate-600 text-sm leading-relaxed mb-4">"{t.text}"</p>

              <div className="inline-block bg-amber-50 border border-amber-100 rounded-full px-3 py-1 text-amber-700 text-xs">
                {t.project}
              </div>
            </div>
          ))}
        </div>

        {/* Trust bar */}
        <div className="mt-12 bg-slate-900 rounded-2xl p-6 sm:p-8 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
          {[
            { value: "4.9/5", label: "Google Rating" },
            { value: "500+", label: "5-Star Reviews" },
            { value: "0", label: "Pending Disputes" },
            { value: "100%", label: "BBMP Compliance" },
          ].map((s) => (
            <div key={s.label}>
              <div className="text-amber-400" style={{ fontSize: "1.6rem", fontWeight: 800 }}>
                {s.value}
              </div>
              <div className="text-slate-400 text-sm mt-1">{s.label}</div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
