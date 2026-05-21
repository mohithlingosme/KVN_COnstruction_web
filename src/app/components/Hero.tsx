import { CheckCircle, MessageCircle, Calendar, ArrowDown } from "lucide-react";

const stats = [
  { value: "500+", label: "Projects Completed" },
  { value: "12+", label: "Years Experience" },
  { value: "98%", label: "Client Satisfaction" },
  { value: "₹1800", label: "Starting /sqft" },
];

export function Hero() {
  const scrollTo = (href: string) => {
    const el = document.querySelector(href);
    if (el) el.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <section
      id="home"
      className="relative min-h-screen flex flex-col justify-center overflow-hidden"
    >
      {/* Background image */}
      <div className="absolute inset-0">
        <img
          src="https://images.unsplash.com/photo-1629692747458-b3360f3267d0?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxob3VzZSUyMGNvbnN0cnVjdGlvbiUyMGJ1aWxkaW5nJTIwQmFuZ2Fsb3JlJTIwSW5kaWF8ZW58MXx8fHwxNzc5MzU2NzUyfDA&ixlib=rb-4.1.0&q=80&w=1080"
          alt="Construction site in Bengaluru"
          className="w-full h-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-slate-900/70 to-slate-900/40" />
      </div>

      {/* Content */}
      <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16">
        <div className="max-w-2xl">
          {/* Badge */}
          <div className="inline-flex items-center gap-2 bg-amber-500/20 border border-amber-500/40 rounded-full px-4 py-1.5 mb-6">
            <span className="w-2 h-2 bg-amber-400 rounded-full animate-pulse" />
            <span className="text-amber-300 text-sm">Bengaluru's #1 Trusted Builder</span>
          </div>

          <h1
            className="text-white mb-6"
            style={{ fontSize: "clamp(2.2rem, 5vw, 3.5rem)", fontWeight: 800, lineHeight: 1.15 }}
          >
            Build Your{" "}
            <span className="text-amber-400">Dream Home</span> in Bengaluru
          </h1>

          <p className="text-slate-300 mb-4 max-w-lg" style={{ fontSize: "1.1rem" }}>
            Transparent pricing. Expert construction. On-time delivery. From villa to commercial —
            we deliver quality that lasts generations.
          </p>

          {/* Trust points */}
          <div className="flex flex-col sm:flex-row gap-3 mb-8">
            {["BBMP Approved", "ISO Certified", "Vastu Compliant"].map((item) => (
              <div key={item} className="flex items-center gap-2 text-slate-300 text-sm">
                <CheckCircle className="w-4 h-4 text-amber-400 flex-shrink-0" />
                {item}
              </div>
            ))}
          </div>

          {/* CTAs */}
          <div className="flex flex-col sm:flex-row gap-4">
            <button
              onClick={() => scrollTo("#estimator")}
              className="bg-amber-500 hover:bg-amber-600 text-white px-8 py-4 rounded-lg transition-colors flex items-center justify-center gap-2"
              style={{ fontWeight: 600 }}
            >
              <Calendar className="w-5 h-5" />
              Get Free Cost Estimate
            </button>
            <a
              href="https://wa.me/919876543210?text=Hi%2C%20I'm%20interested%20in%20construction%20services%20in%20Bengaluru"
              target="_blank"
              rel="noopener noreferrer"
              className="bg-green-500 hover:bg-green-600 text-white px-8 py-4 rounded-lg transition-colors flex items-center justify-center gap-2"
              style={{ fontWeight: 600 }}
            >
              <MessageCircle className="w-5 h-5" />
              WhatsApp Us
            </a>
          </div>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-16">
          {stats.map((stat) => (
            <div
              key={stat.label}
              className="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-4 text-center"
            >
              <div className="text-amber-400" style={{ fontSize: "1.75rem", fontWeight: 800 }}>
                {stat.value}
              </div>
              <div className="text-slate-300 text-sm mt-1">{stat.label}</div>
            </div>
          ))}
        </div>
      </div>

      {/* Scroll indicator */}
      <button
        onClick={() => scrollTo("#services")}
        className="absolute bottom-8 left-1/2 -translate-x-1/2 text-white/60 hover:text-white transition-colors animate-bounce"
      >
        <ArrowDown className="w-6 h-6" />
      </button>
    </section>
  );
}
