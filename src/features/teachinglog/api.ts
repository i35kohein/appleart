import { useQuery } from "@tanstack/react-query"
import { apiFetch } from "@/lib/api"

export type TeachingEffect = "effective" | "partial" | "not_effective"

export interface TeachingLogRow {
  id: number
  log_date: string
  student_id: number | null
  item_id: number | null
  effect: TeachingEffect | null
  note: string | null
  created_at: string
  student_name: string | null
  item_title: string | null
  item_type: string | null
}

export interface TeachingLogFilters {
  from?: string
  to?: string
  student_id?: number
  effect?: TeachingEffect
}

export const EFFECT_LABELS: Record<TeachingEffect, string> = {
  effective: "Effective",
  partial: "Partial",
  not_effective: "Not effective",
}

export function useTeachingLog(filters: TeachingLogFilters) {
  return useQuery({
    queryKey: ["teaching-log", filters.from ?? "all", filters.to ?? "all", filters.student_id ?? "all", filters.effect ?? "all"],
    queryFn: () => {
      const params = new URLSearchParams()
      if (filters.from) params.set("from", filters.from)
      if (filters.to) params.set("to", filters.to)
      if (filters.student_id) params.set("student_id", String(filters.student_id))
      if (filters.effect) params.set("effect", filters.effect)
      const qs = params.toString()
      return apiFetch<{ data: TeachingLogRow[] }>(`/get_teaching_log.php${qs ? `?${qs}` : ""}`).then((r) => r.data)
    },
  })
}
