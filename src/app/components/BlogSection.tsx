import { ArrowRight, Clock } from "lucide-react";

const posts = [
  {
    id: 1,
    category: "Cost Guide",
    title: "House Construction Cost in Bangalore 2024: Complete Breakdown",
    excerpt:
      "From foundation to finishing, we break down every cost component involved in building a home in Bengaluru — material, labour, approvals, and more.",
    readTime: "8 min read",
    date: "May 10, 2026",
    image: "https://images.unsplash.com/photo-1685464196386-d27db832ba25?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxjb25zdHJ1Y3Rpb24lMjB3b3JrZXIlMjBidWlsZGluZyUyMHNpdGUlMjBjb25jcmV0ZXxlbnwxfHx8fDE3NzkzNTY3NTl8MA&ixlib=rb-4.1.0&q=80&w=400",
    slug: "#blog",
  },
  {
    id: 2,
    category: "Vastu & Design",
    title: "Vastu Shastra for New Homes: What Bengaluru Builders Must Follow",
    excerpt:
      "Understanding key Vastu principles for modern homes in Bengaluru — main entrance direction, kitchen placement, bedroom orientation, and more.",
    readTime: "6 min read",
    date: "Apr 28, 2026",
    image: "https://images.unsplash.com/photo-1774685110718-c5b4fe026144?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxsdXh1cnklMjB2aWxsYSUyMHJlc2lkZW50aWFsJTIwaG9tZSUyMG1vZGVybiUyMGFyY2hpdGVjdHVyZXxlbnwxfHx8fDE3NzkzNTY3NTN8MA&ixlib=rb-4.1.0&q=80&w=400",
    slug: "#blog",
  },
  {
    id: 3,
    category: "Legal & Approvals",
    title: "BBMP Building Plan Approval Process: Step-by-Step Guide 2024",
    excerpt:
      "A complete guide to getting BBMP building plan sanction in Bengaluru — documents required, timeline, fees, and common pitfalls to avoid.",
    readTime: "10 min read",
    date: "Apr 12, 2026",
    image: "https://images.unsplash.com/photo-1710262590721-1164c011b97d?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHw0fHxob3VzZSUyMGNvbnN0cnVjdGlvbiUyMGJ1aWxkaW5nJTIwQmFuZ2Fsb3JlJTIwSW5kaWF8ZW58MXx8fHwxNzc5MzU2NzUyfDA&ixlib=rb-4.1.0&q=80&w=400",
    slug: "#blog",
  },
];

const categories = ["All Posts", "Cost Guide", "Vastu & Design", "Legal & Approvals", "Smart Home", "Budgeting"];

export function BlogSection() {
  return (
    <section id="blog" className="py-20 bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
          <div>
            <span className="text-amber-600 text-sm tracking-widest uppercase">Knowledge Hub</span>
            <h2 className="text-slate-900 mt-2" style={{ fontSize: "2rem", fontWeight: 700 }}>
              Construction Insights & Guides
            </h2>
          </div>
          <button className="text-amber-600 hover:text-amber-700 text-sm flex items-center gap-1 group">
            View all articles
            <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
          </button>
        </div>

        {/* Categories */}
        <div className="flex flex-wrap gap-2 mb-8">
          {categories.map((c) => (
            <button
              key={c}
              className={`px-4 py-1.5 rounded-full text-sm transition-all ${
                c === "All Posts"
                  ? "bg-amber-500 text-white"
                  : "bg-white border border-gray-200 text-slate-600 hover:border-amber-300"
              }`}
            >
              {c}
            </button>
          ))}
        </div>

        {/* Posts grid */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {posts.map((post) => (
            <article
              key={post.id}
              className="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-md hover:border-amber-200 transition-all group"
            >
              <div className="aspect-[16/9] overflow-hidden">
                <img
                  src={post.image}
                  alt={post.title}
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
              </div>
              <div className="p-5">
                <div className="flex items-center gap-3 mb-3">
                  <span className="bg-amber-50 text-amber-700 text-xs px-3 py-1 rounded-full border border-amber-100">
                    {post.category}
                  </span>
                  <div className="flex items-center gap-1 text-slate-400 text-xs">
                    <Clock className="w-3 h-3" />
                    {post.readTime}
                  </div>
                </div>
                <h3
                  className="text-slate-900 mb-2 leading-snug group-hover:text-amber-700 transition-colors"
                  style={{ fontSize: "0.95rem", fontWeight: 600 }}
                >
                  {post.title}
                </h3>
                <p className="text-slate-500 text-sm mb-4 leading-relaxed line-clamp-2">
                  {post.excerpt}
                </p>
                <div className="flex items-center justify-between">
                  <span className="text-slate-400 text-xs">{post.date}</span>
                  <button className="text-amber-600 text-sm flex items-center gap-1 group/btn">
                    Read more
                    <ArrowRight className="w-3.5 h-3.5 group-hover/btn:translate-x-1 transition-transform" />
                  </button>
                </div>
              </div>
            </article>
          ))}
        </div>

        {/* SEO keywords callout */}
        <div className="mt-10 bg-white rounded-2xl p-6 border border-gray-100 flex flex-wrap gap-2 items-center">
          <span className="text-slate-500 text-sm mr-2">Popular searches:</span>
          {[
            "House construction cost Bangalore",
            "Best builders in Bengaluru",
            "Villa construction Bangalore",
            "2BHK construction cost Bengaluru",
            "BBMP approval Bangalore",
            "Turnkey home builders Bangalore",
          ].map((kw) => (
            <button
              key={kw}
              className="text-xs bg-slate-50 border border-slate-200 text-slate-600 px-3 py-1.5 rounded-full hover:border-amber-300 hover:text-amber-700 transition-colors"
            >
              {kw}
            </button>
          ))}
        </div>
      </div>
    </section>
  );
}
