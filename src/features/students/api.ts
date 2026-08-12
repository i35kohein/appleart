import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { useToast } from "@/components/ui/toast"
import { apiFetch, apiPostForm } from "@/lib/api"
import type { Student, StudentInput } from "./types"

export function useStudents(includeAll = false) {
  return useQuery({
    queryKey: ["students", includeAll ? "all" : "active"],
    queryFn: () => apiFetch<{ data: Student[] }>(`/get_students.php${includeAll ? "?all=1" : ""}`).then((r) => r.data),
  })
}

export function useCurriculum() {
  return useQuery({
    queryKey: ["curriculum"],
    queryFn: () => apiFetch<{ data: import("./types").CurriculumItem[] }>("/get_curriculum.php").then((r) => r.data),
  })
}

export function useToday() {
  return useQuery({
    queryKey: ["today"],
    queryFn: () => apiFetch<import("./types").TodayResponse>("/get_today.php"),
  })
}

export function useStudentProgress(studentId: number | null) {
  return useQuery({
    queryKey: ["student-progress", studentId],
    enabled: studentId != null,
    queryFn: () =>
      apiFetch<{ data: Array<{ item_id: number; detail_idx: number | null; completion_date: string; trainer_name: string }> }>(
        `/get_student_progress.php?student_id=${studentId}`,
      ).then((r) => r.data),
  })
}

export function useItemProgress(itemId: number | null) {
  return useQuery({
    queryKey: ["item-progress", itemId],
    enabled: itemId != null,
    queryFn: () =>
      apiFetch<{ data: Array<{ id: number; name: string; phone: string | null; shop_name: string | null; completion_date: string; trainer_name: string }> }>(
        `/get_item_progress.php?item_id=${itemId}`,
      ).then((r) => r.data),
  })
}

export interface InProgressRow {
  student_id: number
  student_name: string
  item_id: number
  item_title: string
  status: string
  completion_date: string
  steps_done: number
}

/** Rows where a lesson is currently marked "In Progress" — omit studentId for all students. */
export function useInProgress(studentId: number | null) {
  return useQuery({
    queryKey: ["in-progress", studentId ?? "all"],
    queryFn: () =>
      apiFetch<{ data: InProgressRow[] }>(
        `/get_in_progress.php${studentId != null ? `?student_id=${studentId}` : ""}`,
      ).then((r) => r.data),
  })
}

export interface PracticalHistoryRow {
  id: number
  student_id: number
  student_name: string
  item_id: number | null
  item_title: string
  repair_date: string
  note: string
  created_at: string
  source?: string
  trainer_name?: string | null
}

/** Practical repair history — all entries or filtered by item/student. */
export function usePracticalHistory(itemId?: number | null, studentId?: number | null) {
  return useQuery({
    queryKey: ["practical-history", itemId ?? "all", studentId ?? "all"],
    queryFn: () => {
      const params = new URLSearchParams()
      if (itemId != null) params.set("item_id", String(itemId))
      if (studentId != null) params.set("student_id", String(studentId))
      const qs = params.toString()
      return apiFetch<{ data: PracticalHistoryRow[] }>(`/get_practical_history.php${qs ? `?${qs}` : ""}`).then((r) => r.data)
    },
  })
}

export function useSavePracticalHistory() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { action: "add" | "delete"; id?: number; student_id?: number; item_id?: number; repair_date?: string; title?: string; note?: string }) =>
      apiPostForm<{ status: string }>("/save_practical_history.php", fields as Record<string, string | number>),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["practical-history"] })
    },
  })
}

/** Upload a course material file for a curriculum lesson. */
export function useUploadMaterial() {
  const qc = useQueryClient()
  const { toast } = useToast()
  return useMutation({
    mutationFn: ({ itemId, file }: { itemId: number; file: File }) =>
      apiPostForm<{ status: string }>("/upload_material.php", { item_id: itemId, file }),
    onSuccess: (_data, vars) => {
      qc.invalidateQueries({ queryKey: ["curriculum"] })
      toast({ title: "Material uploaded ✓", description: vars.file.name, variant: "success" })
    },
    onError: (err) => {
      toast({ title: "Upload failed", description: err instanceof Error ? err.message : "Please try again", variant: "error" })
    },
  })
}

/** Delete a course material file. */
export function useDeleteMaterial() {
  const qc = useQueryClient()
  const { toast } = useToast()
  return useMutation({
    mutationFn: (id: number) => apiPostForm<{ status: string }>("/delete_material.php", { id }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["curriculum"] })
      toast({ title: "Material deleted", variant: "success" })
    },
    onError: (err) => {
      toast({ title: "Delete failed", description: err instanceof Error ? err.message : "Please try again", variant: "error" })
    },
  })
}

/** Create or update a trainee. Mutate with `{ id?: number; input: StudentInput }`. */
export function useSaveStudent() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ id, input }: { id?: number; input: StudentInput }) => {
      const fields: Record<string, string | number> = {
        name: input.name,
        phone: input.phone,
        email: input.email,
        address: input.address,
        shop_name: input.shop_name,
        is_active: input.is_active,
        role: input.role,
        rollcall_group: input.rollcall_group,
      }
      if (input.enrollment_date) fields.enrollment_date = input.enrollment_date
      if (id != null) fields.id = id
      return apiPostForm<{ status: string }>(id != null ? "/edit_student.php" : "/add_student.php", fields)
    },
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["students"] })
      qc.invalidateQueries({ queryKey: ["today"] })
    },
  })
}

export function useDeleteStudent() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => apiPostForm<{ status: string }>("/delete_student.php", { id }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["students"] })
      qc.invalidateQueries({ queryKey: ["today"] })
    },
  })
}

export function useSaveProgress() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { student_id: number; item_id: number; status: string; comment?: string; trainer_name?: string; completion_date?: string; detail_idx?: number | null }) =>
      apiPostForm<{ status: string }>("/update_progress.php", fields),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["students"] })
      qc.invalidateQueries({ queryKey: ["student-portal"] })
      qc.invalidateQueries({ queryKey: ["student-analytics"] })
      qc.invalidateQueries({ queryKey: ["today"] })
      qc.invalidateQueries({ queryKey: ["student-progress"] })
      qc.invalidateQueries({ queryKey: ["item-progress"] })
      qc.invalidateQueries({ queryKey: ["in-progress"] })
      qc.invalidateQueries({ queryKey: ["curriculum"] })
    },
  })
}
