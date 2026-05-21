import type { Role } from "@/types/domain";

import { createSupabaseServerClient } from "@/lib/supabase/server";

export async function getCurrentUserRole(): Promise<Role | null> {
  try {
    const supabase = await createSupabaseServerClient();
    const {
      data: { user },
    } = await supabase.auth.getUser();

    const role = user?.user_metadata?.role as Role | undefined;
    return role || null;
  } catch {
    return null;
  }
}
