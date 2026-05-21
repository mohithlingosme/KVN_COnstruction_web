export async function sendAutomationEmail(payload: {
  to: string;
  subject: string;
  html: string;
}) {
  return {
    ok: false,
    skipped: true,
    payload,
    message:
      "Email automation provider is not configured. Connect Resend, SMTP, or Postmark in this adapter.",
  };
}
