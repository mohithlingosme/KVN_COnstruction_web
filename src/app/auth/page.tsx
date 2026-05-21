import { OTPForm } from "@/components/auth/otp-form";
import { buildMetadata } from "@/lib/metadata";

export const metadata = buildMetadata({
  title: "Authentication",
  path: "/auth",
  description: "OTP-based login entrypoint for admin team members and clients.",
});

export default function AuthPage() {
  return (
    <div className="container flex min-h-screen items-center justify-center py-16">
      <OTPForm />
    </div>
  );
}
