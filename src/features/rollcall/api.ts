import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { apiFetch, apiPostForm } from "@/lib/api"
import type { TodayResponse } from "@/features/students/types"

export function useToday() {
  return useQuery({
    queryKey: ["today"],
    queryFn: () => apiFetch<TodayResponse>("/get_today.php"),
  })
}

export interface AttendanceLog {
  id: number
  student_id: number
  status: "Present" | "Absent" | "Late"
  created_at: string
  name: string
  photo_path: string | null
  is_active: number
}

/** All attendance for a month (YYYY-MM), joined with student names. */
export function useAttendanceMonth(month: string) {
  return useQuery({
    queryKey: ["attendance-month", month],
    queryFn: () =>
      apiFetch<{ data: AttendanceLog[] }>(`/get_attendance_all.php?month=${month}`).then((r) => r.data),
  })
}

export function useMarkRollcall() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ student_id, status, date }: { student_id: number; status: "Present" | "Absent" | "Late"; date?: string }) => {
      const fields: Record<string, string | number> = { student_id, status }
      if (date) fields.date = date
      return apiPostForm<{ status: string }>("/save_rollcall.php", fields)
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["attendance"] })
      qc.invalidateQueries({ queryKey: ["attendance-month"] })
      qc.invalidateQueries({ queryKey: ["today"] })
    },
  })
}
