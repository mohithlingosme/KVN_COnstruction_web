import { useState } from "react";
import { MapPin, Phone, Mail, MessageCircle, Clock, Send } from "lucide-react";

export function ContactSection() {
  const [form, setForm] = useState({ name: "", phone: "", message: "" });
  const [sent, setSent] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (form.name && form.phone) setSent(true);
  };

  return (
    <section id="contact" className="py-20 bg-slate-900">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-12">
          <span className="text-amber-400 text-sm tracking-widest uppercase">Get In Touch</span>
          <h2 className="text-white mt-2" style={{ fontSize: "2rem", fontWeight: 700 }}>
            Let's Build Something Great Together
          </h2>
          <p className="text-slate-400 mt-3 max-w-xl mx-auto">
            Reach out for a free consultation, site visit, or any questions about your construction project.
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
          {/* Info */}
          <div className="space-y-6">
            {[
              {
                icon: MapPin,
                title: "Visit Our Office",
                lines: ["BuildRight Bengaluru", "4th Floor, Brigade Road,", "Bengaluru – 560 025, Karnataka"],
              },
              {
                icon: Phone,
                title: "Call / WhatsApp",
                lines: ["+91 98765 43210", "+91 80 4123 5678", "Mon–Sat: 9AM – 7PM"],
              },
              {
                icon: Mail,
                title: "Email Us",
                lines: ["info@buildrightblr.in", "projects@buildrightblr.in"],
              },
              {
                icon: Clock,
                title: "Working Hours",
                lines: ["Monday – Saturday: 9:00 AM – 7:00 PM", "Sunday: 10:00 AM – 2:00 PM"],
              },
            ].map(({ icon: Icon, title, lines }) => (
              <div key={title} className="flex items-start gap-4">
                <div className="w-10 h-10 bg-amber-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                  <Icon className="w-5 h-5 text-amber-400" />
                </div>
                <div>
                  <div className="text-white text-sm mb-1" style={{ fontWeight: 600 }}>{title}</div>
                  {lines.map((l) => (
                    <div key={l} className="text-slate-400 text-sm">{l}</div>
                  ))}
                </div>
              </div>
            ))}

            {/* WhatsApp CTA */}
            <a
              href="https://wa.me/919876543210?text=Hi%2C%20I'd%20like%20to%20know%20more%20about%20construction%20services%20in%20Bengaluru"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-flex items-center gap-3 bg-green-500 hover:bg-green-600 text-white px-6 py-3.5 rounded-xl transition-colors"
              style={{ fontWeight: 600 }}
            >
              <MessageCircle className="w-5 h-5" />
              Chat on WhatsApp — Quick Response
            </a>

            {/* Map placeholder */}
            <div className="bg-slate-800 rounded-2xl overflow-hidden h-48 flex items-center justify-center border border-slate-700">
              <div className="text-center text-slate-500">
                <MapPin className="w-8 h-8 mx-auto mb-2 text-amber-500" />
                <p className="text-sm">Brigade Road, Bengaluru</p>
                <p className="text-xs mt-1">Google Maps integration available</p>
              </div>
            </div>
          </div>

          {/* Contact Form */}
          <div className="bg-slate-800 rounded-2xl p-6 sm:p-8">
            <h3 className="text-white mb-6" style={{ fontSize: "1.2rem", fontWeight: 600 }}>
              Send Us a Message
            </h3>
            {!sent ? (
              <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                  <label className="block text-slate-400 text-sm mb-1.5">Full Name *</label>
                  <input
                    type="text"
                    placeholder="Your name"
                    value={form.name}
                    onChange={(e) => setForm((f) => ({ ...f, name: e.target.value }))}
                    className="w-full bg-slate-700 text-white placeholder-slate-500 px-4 py-3 rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-500"
                  />
                </div>
                <div>
                  <label className="block text-slate-400 text-sm mb-1.5">Phone Number *</label>
                  <input
                    type="tel"
                    placeholder="9876543210"
                    value={form.phone}
                    onChange={(e) => setForm((f) => ({ ...f, phone: e.target.value }))}
                    className="w-full bg-slate-700 text-white placeholder-slate-500 px-4 py-3 rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-500"
                  />
                </div>
                <div>
                  <label className="block text-slate-400 text-sm mb-1.5">Your Message</label>
                  <textarea
                    rows={4}
                    placeholder="Tell us about your project, location, and budget…"
                    value={form.message}
                    onChange={(e) => setForm((f) => ({ ...f, message: e.target.value }))}
                    className="w-full bg-slate-700 text-white placeholder-slate-500 px-4 py-3 rounded-lg text-sm outline-none focus:ring-2 focus:ring-amber-500 resize-none"
                  />
                </div>
                <button
                  type="submit"
                  className="w-full bg-amber-500 hover:bg-amber-600 text-white py-3.5 rounded-xl transition-colors flex items-center justify-center gap-2"
                  style={{ fontWeight: 600 }}
                >
                  <Send className="w-4 h-4" />
                  Send Message
                </button>
              </form>
            ) : (
              <div className="text-center py-10">
                <div className="text-green-400 text-4xl mb-3">✓</div>
                <h4 className="text-white mb-2" style={{ fontWeight: 600 }}>Message Sent!</h4>
                <p className="text-slate-400 text-sm">We'll get back to you within 2 hours during business hours.</p>
              </div>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
