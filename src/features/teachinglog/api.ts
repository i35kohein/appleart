import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { useToast } from "@/components/ui/toast"
import { apiFetch, apiPostForm } from "@/lib/api"

export type TeachingEffect = "effective" | "partial" | "not_effective"

export interface TeachingLogRow {
  id: number
  log_date: string
  student_id: number | null
  item_id: number | null
  effect: TeachingEffect
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

export interface TeachingLogInput {
  log_date: string
  student_id: number | null
  item_id: number | null
  effect: TeachingEffect
  note?: string
}

export function useSaveTeachingLog() {
  const qc = useQueryClient()
  const { toast } = useToast()
  return useMutation({
    mutationFn: (fields: { action: "add" | "delete"; id?: number } & TeachingLogInput) =>
      apiPostForm<{ status: string }>("/save_teaching_log.php", fields as Record<string, string | number>),
    onSuccess: (_data, vars) => {
      qc.invalidateQueries({ queryKey: ["teaching-log"] })
      qc.invalidateQueries({ queryKey: ["today"] })
      toast({ title: vars.action === "delete" ? "Entry deleted" : "Teaching log saved ✓", variant: "success" })
    },
    onError: (err) =>
      toast({ title: "Save failed", description: err instanceof Error ? err.message : undefined, variant: "error" }),
  })
}
