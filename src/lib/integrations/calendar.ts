import { google } from "googleapis";

export async function createCalendarEvent({
  title,
  description,
  start,
  end,
  attendees = [],
}: {
  title: string;
  description: string;
  start: string;
  end: string;
  attendees?: string[];
}) {
  const clientEmail = process.env.GOOGLE_SERVICE_ACCOUNT_EMAIL;
  const privateKey = process.env.GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY?.replace(
    /\\n/g,
    "\n",
  );
  const calendarId = process.env.GOOGLE_CALENDAR_ID;

  if (!clientEmail || !privateKey || !calendarId) {
    return {
      ok: false,
      skipped: true,
      message: "Google Calendar credentials are not configured.",
    };
  }

  const auth = new google.auth.JWT({
    email: clientEmail,
    key: privateKey,
    scopes: ["https://www.googleapis.com/auth/calendar"],
  });

  const calendar = google.calendar({ version: "v3", auth });

  const response = await calendar.events.insert({
    calendarId,
    requestBody: {
      summary: title,
      description,
      start: { dateTime: start, timeZone: "Asia/Kolkata" },
      end: { dateTime: end, timeZone: "Asia/Kolkata" },
      attendees: attendees.map((email) => ({ email })),
    },
  });

  return {
    ok: true,
    skipped: false,
    data: response.data,
  };
}
