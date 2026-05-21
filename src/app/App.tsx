import { Navbar } from "./components/Navbar";
import { Hero } from "./components/Hero";
import { ServicesSection } from "./components/ServicesSection";
import { ProjectsSection } from "./components/ProjectsSection";
import { CostEstimator } from "./components/CostEstimator";
import { PackagesSection } from "./components/PackagesSection";
import { TestimonialsSection } from "./components/TestimonialsSection";
import { BookingSection } from "./components/BookingSection";
import { FAQSection } from "./components/FAQSection";
import { BlogSection } from "./components/BlogSection";
import { ContactSection } from "./components/ContactSection";
import { Footer } from "./components/Footer";
import { MessageCircle } from "lucide-react";

export default function App() {
  return (
    <div className="min-h-screen">
      <Navbar />
      <Hero />
      <ServicesSection />
      <ProjectsSection />
      <CostEstimator />
      <PackagesSection />
      <TestimonialsSection />
      <BookingSection />
      <FAQSection />
      <BlogSection />
      <ContactSection />
      <Footer />

      {/* Floating WhatsApp button */}
      <a
        href="https://wa.me/919876543210?text=Hi%2C%20I'm%20interested%20in%20construction%20services%20in%20Bengaluru"
        target="_blank"
        rel="noopener noreferrer"
        className="fixed bottom-6 right-6 z-50 bg-green-500 hover:bg-green-600 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all hover:scale-110"
        title="Chat on WhatsApp"
      >
        <MessageCircle className="w-7 h-7" />
      </a>
    </div>
  );
}
