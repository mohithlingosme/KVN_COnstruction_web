import Script from "next/script";

export function SchemaScript({
  id,
  data,
}: {
  id: string;
  data: Record<string, unknown>;
}) {
  return (
    <Script
      id={id}
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: JSON.stringify(data) }}
    />
  );
}
