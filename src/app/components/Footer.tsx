import { Building2, Phone, Mail, MapPin, MessageCircle } from "lucide-react";

const links = {
  Services: ["Residential Construction", "Commercial Construction", "Renovation", "Interior Design", "BBMP Approvals", "Financial Aid"],
  Company: ["About Us", "Our Projects", "Packages", "Testimonials", "Blog", "Careers"],
  Legal: ["Privacy Policy", "Terms of Service", "Refund Policy", "RERA Registration"],
};

export function Footer() {
  const scrollTo = (href: string) => {
    const el = document.querySelector(href);
    if (el) el.scrollIntoView({ behavior: "smooth" });
  };

  return (
    <footer className="bg-slate-950 text-slate-400">
      {/* Top CTA band */}
      <div className="bg-amber-500">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div>
            <div className="text-white" style={{ fontSize: "1.2rem", fontWeight: 700 }}>
              Ready to Build Your Dream Home?
            </div>
            <div className="text-amber-100 text-sm">Get a free estimate today. No commitment required.</div>
          </div>
          <div className="flex gap-3">
            <button
              onClick={() => scrollTo("#estimator")}
              className="bg-white text-amber-600 hover:bg-amber-50 px-5 py-2.5 rounded-lg text-sm transition-colors"
              style={{ fontWeight: 600 }}
            >
              Free Estimate
            </button>
            <a
              href="https://wa.me/919876543210"
              target="_blank"
              rel="noopener noreferrer"
              className="bg-green-500 hover:bg-green-600 text-white px-5 py-2.5 rounded-lg text-sm flex items-center gap-2 transition-colors"
              style={{ fontWeight: 600 }}
            >
              <MessageCircle className="w-4 h-4" />
              WhatsApp
            </a>
          </div>
        </div>
      </div>

      {/* Main footer */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-10">
          {/* Brand */}
          <div className="lg:col-span-2">
            <div className="flex items-center gap-2 mb-4">
              <div className="w-8 h-8 bg-amber-500 rounded flex items-center justify-center">
                <Building2 className="w-5 h-5 text-white" />
              </div>
              <span className="text-white" style={{ fontSize: "1.1rem", fontWeight: 700 }}>
                BuildRight <span className="text-amber-400">Bengaluru</span>
              </span>
            </div>
            <p className="text-sm leading-relaxed mb-5 max-w-xs">
              Bengaluru's trusted construction partner since 2012. Building transparent, high-quality homes
              for 500+ families across the city.
            </p>
            <div className="space-y-2 text-sm">
              <div className="flex items-center gap-2">
                <MapPin className="w-4 h-4 text-amber-500 flex-shrink-0" />
                Brigade Road, Bengaluru – 560 025
              </div>
              <div className="flex items-center gap-2">
                <Phone className="w-4 h-4 text-amber-500 flex-shrink-0" />
                +91 98765 43210
              </div>
              <div className="flex items-center gap-2">
                <Mail className="w-4 h-4 text-amber-500 flex-shrink-0" />
                info@buildrightblr.in
              </div>
            </div>
          </div>

          {/* Links */}
          {Object.entries(links).map(([section, items]) => (
            <div key={section}>
              <h4 className="text-white text-sm mb-4" style={{ fontWeight: 600 }}>
                {section}
              </h4>
              <ul className="space-y-2.5">
                {items.map((item) => (
                  <li key={item}>
                    <button className="text-sm hover:text-amber-400 transition-colors text-left">
                      {item}
                    </button>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        {/* Certifications */}
        <div className="mt-10 pt-8 border-t border-slate-800">
          <div className="flex flex-wrap gap-4 justify-center mb-6">
            {["BBMP Registered", "RERA Compliant", "ISO 9001:2015", "Green Building Certified"].map((cert) => (
              <div
                key={cert}
                className="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-xs text-slate-400"
              >
                ✓ {cert}
              </div>
            ))}
          </div>
          <div className="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600">
            <div>© 2026 BuildRight Bengaluru. All rights reserved.</div>
            <div className="text-center">
              Serving: Whitefield · Koramangala · HSR Layout · Indiranagar · JP Nagar · Jayanagar · Marathahalli
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
