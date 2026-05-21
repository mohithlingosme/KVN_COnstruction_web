import { useState } from "react";
import { MapPin, IndianRupee } from "lucide-react";

const filters = ["All", "Residential", "Commercial", "Interior", "Renovation"];

const projects = [
  {
    id: 1,
    title: "Whitefield Villa",
    category: "Residential",
    location: "Whitefield, Bengaluru",
    area: "3,200 sqft",
    budget: "₹1.2 Cr",
    duration: "14 months",
    image: "https://images.unsplash.com/photo-1774685110718-c5b4fe026144?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxsdXh1cnklMjB2aWxsYSUyMHJlc2lkZW50aWFsJTIwaG9tZSUyMG1vZGVybiUyMGFyY2hpdGVjdHVyZXxlbnwxfHx8fDE3NzkzNTY3NTN8MA&ixlib=rb-4.1.0&q=80&w=600",
    quote: "They delivered exactly what they promised, on time!",
    client: "Rajesh K.",
  },
  {
    id: 2,
    title: "Koramangala Row House",
    category: "Residential",
    location: "Koramangala, Bengaluru",
    area: "2,800 sqft",
    budget: "₹98 Lakhs",
    duration: "12 months",
    image: "https://images.unsplash.com/photo-1771871027939-d6c26e5d4f64?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwyfHxsdXh1cnklMjB2aWxsYSUyMHJlc2lkZW50aWFsJTIwaG9tZSUyMG1vZGVybiUyMGFyY2hpdGVjdHVyZXxlbnwxfHx8fDE3NzkzNTY3NTN8MA&ixlib=rb-4.1.0&q=80&w=600",
    quote: "Transparent pricing with no hidden costs!",
    client: "Priya M.",
  },
  {
    id: 3,
    title: "HSR Layout Modern Home",
    category: "Residential",
    location: "HSR Layout, Bengaluru",
    area: "2,400 sqft",
    budget: "₹85 Lakhs",
    duration: "11 months",
    image: "https://images.unsplash.com/photo-1778166166355-ad024539b696?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwzfHxsdXh1cnklMjB2aWxsYSUyMHJlc2lkZW50aWFsJTIwaG9tZSUyMG1vZGVybiUyMGFyY2hpdGVjdHVyZXxlbnwxfHx8fDE3NzkzNTY3NTN8MA&ixlib=rb-4.1.0&q=80&w=600",
    quote: "Best builder in Bengaluru, hands down.",
    client: "Suresh R.",
  },
  {
    id: 4,
    title: "Indiranagar Duplex",
    category: "Residential",
    location: "Indiranagar, Bengaluru",
    area: "3,600 sqft",
    budget: "₹1.4 Cr",
    duration: "16 months",
    image: "https://images.unsplash.com/photo-1778856951796-d1192dab6d54?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHw0fHxsdXh1cnklMjB2aWxsYSUyMHJlc2lkZW50aWFsJTIwaG9tZSUyMG1vZGVybiUyMGFyY2hpdGVjdHVyZXxlbnwxfHx8fDE3NzkzNTY3NTN8MA&ixlib=rb-4.1.0&q=80&w=600",
    quote: "From foundation to handover — flawless execution.",
    client: "Anitha S.",
  },
  {
    id: 5,
    title: "JP Nagar Interior",
    category: "Interior",
    location: "JP Nagar, Bengaluru",
    area: "1,800 sqft",
    budget: "₹32 Lakhs",
    duration: "4 months",
    image: "https://images.unsplash.com/photo-1771327811795-6197403af846?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxpbnRlcmlvciUyMGRlc2lnbiUyMGxpdmluZyUyMHJvb20lMjBtb2Rlcm4lMjBob21lJTIwSW5kaWF8ZW58MXx8fHwxNzc5MzU2NzU5fDA&ixlib=rb-4.1.0&q=80&w=600",
    quote: "Beautiful interiors within budget and on schedule.",
    client: "Vivek T.",
  },
  {
    id: 6,
    title: "Jayanagar Renovation",
    category: "Renovation",
    location: "Jayanagar, Bengaluru",
    area: "2,200 sqft",
    budget: "₹45 Lakhs",
    duration: "6 months",
    image: "https://images.unsplash.com/photo-1686569860484-b0b79f5d7959?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwyfHxpbnRlcmlvciUyMGRlc2lnbiUyMGxpdmluZyUyMHJvb20lMjBtb2Rlcm4lMjBob21lJTIwSW5kaWF8ZW58MXx8fHwxNzc5MzU2NzU5fDA&ixlib=rb-4.1.0&q=80&w=600",
    quote: "Our old house feels completely new. Excellent work!",
    client: "Meera P.",
  },
];

export function ProjectsSection() {
  const [activeFilter, setActiveFilter] = useState("All");

  const filtered =
    activeFilter === "All"
      ? projects
      : projects.filter((p) => p.category === activeFilter);

  return (
    <section id="projects" className="py-20 bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="text-center mb-10">
          <span className="text-amber-600 text-sm tracking-widest uppercase">Our Portfolio</span>
          <h2 className="text-slate-900 mt-2" style={{ fontSize: "2rem", fontWeight: 700 }}>
            Projects That Speak For Themselves
          </h2>
          <p className="text-slate-600 mt-3 max-w-2xl mx-auto">
            From starter homes to luxury villas across Bengaluru — every project delivered with pride.
          </p>
        </div>

        {/* Filters */}
        <div className="flex flex-wrap justify-center gap-3 mb-10">
          {filters.map((f) => (
            <button
              key={f}
              onClick={() => setActiveFilter(f)}
              className={`px-5 py-2 rounded-full text-sm transition-all ${
                activeFilter === f
                  ? "bg-amber-500 text-white shadow-md"
                  : "bg-gray-100 text-slate-600 hover:bg-gray-200"
              }`}
            >
              {f}
            </button>
          ))}
        </div>

        {/* Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filtered.map((project) => (
            <div
              key={project.id}
              className="group rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all"
            >
              <div className="relative overflow-hidden aspect-[4/3]">
                <img
                  src={project.image}
                  alt={project.title}
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
                <span className="absolute top-3 left-3 bg-amber-500 text-white text-xs px-3 py-1 rounded-full">
                  {project.category}
                </span>
              </div>
              <div className="p-5">
                <h3 className="text-slate-900" style={{ fontSize: "1rem", fontWeight: 600 }}>
                  {project.title}
                </h3>
                <div className="flex items-center gap-1 text-slate-500 text-xs mt-1 mb-3">
                  <MapPin className="w-3 h-3" />
                  {project.location}
                </div>
                <div className="grid grid-cols-3 gap-2 mb-4">
                  {[
                    { label: "Area", value: project.area },
                    { label: "Budget", value: project.budget },
                    { label: "Duration", value: project.duration },
                  ].map((d) => (
                    <div key={d.label} className="bg-gray-50 rounded-lg p-2 text-center">
                      <div className="text-slate-900 text-xs" style={{ fontWeight: 600 }}>{d.value}</div>
                      <div className="text-slate-400 text-xs mt-0.5">{d.label}</div>
                    </div>
                  ))}
                </div>
                <blockquote className="text-slate-500 text-sm italic border-l-2 border-amber-400 pl-3">
                  "{project.quote}"
                  <footer className="text-amber-600 text-xs mt-1 not-italic">— {project.client}</footer>
                </blockquote>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
