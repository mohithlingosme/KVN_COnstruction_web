import { z } from "zod";

export const leadSchema = z.object({
  name: z.string().min(2),
  email: z.string().email(),
  phone: z.string().min(10),
  requirement: z.string().min(10),
  budgetRange: z.string().min(1),
});

export const callbackSchema = z.object({
  name: z.string().min(2),
  phone: z.string().min(10),
  preferredSlot: z.string().min(2),
});

export const appointmentSchema = z.object({
  name: z.string().min(2),
  email: z.string().email(),
  phone: z.string().min(10),
  date: z.string().min(1),
  time: z.string().min(1),
  service: z.string().min(1),
  notes: z.string().optional(),
});

export const estimatorSchema = z.object({
  plotSize: z.coerce.number().min(400),
  floors: z.coerce.number().min(1).max(8),
  materialQuality: z.enum(["standard", "premium", "luxury"]),
  location: z.enum([
    "north-bengaluru",
    "east-bengaluru",
    "south-bengaluru",
    "central-bengaluru",
  ]),
  interiorRequirements: z.enum(["shell", "essential", "full-luxury"]),
});
