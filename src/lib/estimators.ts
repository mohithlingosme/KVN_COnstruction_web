type EstimatorInput = {
  plotSize: number;
  floors: number;
  materialQuality: "standard" | "premium" | "luxury";
  location: "north-bengaluru" | "east-bengaluru" | "south-bengaluru" | "central-bengaluru";
  interiorRequirements: "shell" | "essential" | "full-luxury";
};

const materialMultipliers = {
  standard: 1900,
  premium: 2350,
  luxury: 2950,
} as const;

const locationMultipliers = {
  "north-bengaluru": 1,
  "east-bengaluru": 1.04,
  "south-bengaluru": 1.08,
  "central-bengaluru": 1.12,
} as const;

const interiorMultipliers = {
  shell: 1,
  essential: 1.14,
  "full-luxury": 1.32,
} as const;

export function calculateEstimate(input: EstimatorInput) {
  const baseCost =
    input.plotSize *
    input.floors *
    materialMultipliers[input.materialQuality] *
    locationMultipliers[input.location] *
    interiorMultipliers[input.interiorRequirements];

  const timelineMonths = Math.max(
    7,
    Math.round(input.floors * 3.5 + input.plotSize / 450),
  );

  const recommendation =
    baseCost > 25000000
      ? "Signature Luxe"
      : baseCost > 15000000
        ? "Premium Turnkey"
        : "Smart Build";

  return {
    budget: Math.round(baseCost),
    timelineMonths,
    recommendation,
    materialEstimate:
      input.materialQuality === "luxury"
        ? "High-grade steel, premium concrete mix, imported fixtures"
        : input.materialQuality === "premium"
          ? "TMT steel, branded cement, engineered finishes"
          : "Optimized structural package with standard branded materials",
  };
}
