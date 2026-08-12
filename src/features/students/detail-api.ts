import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { apiFetch, apiPostForm } from "@/lib/api"
import type { Student } from "./types"

/** All students — used to find one by id on the detail page. */
export function useStudentById(id: string | undefined) {
  return useQuery({
    queryKey: ["students"],
    queryFn: () => apiFetch<{ data: Student[] }>("/get_students.php").then((r) => r.data),
    select: (all) => all.find((s) => s.id === Number(id)),
    enabled: id != null && id !== "",
  })
}

export interface AttendanceRecord {
  id: number
  student_id: number
  status: "Present" | "Absent" | "Late"
  created_at: string
}

export function useAttendance(studentId: number | null, limit = 30) {
  return useQuery({
    queryKey: ["attendance", studentId, limit],
    enabled: studentId != null,
    queryFn: () =>
      apiFetch<{ data: AttendanceRecord[] }>(`/get_attendance.php?student_id=${studentId}`).then((r) => r.data.slice(0, limit)),
  })
}

export interface PaymentPlan {
  student_id: number
  name: string
  phone: string | null
  email: string | null
  photo_path: string | null
  is_active: number
  rollcall_group: "Weekday" | "Weekend"
  total_amount: string
  first_amount: string
  first_paid_at: string | null
  second_amount: string
  second_paid_at: string | null
  reminder_date: string | null
  note: string | null
  updated_at: string | null
}

export interface PaymentInput {
  student_id: number
  total_amount: string
  first_amount: string
  second_amount: string
  first_paid_at: string
  second_paid_at: string
  reminder_date: string
  note: string
}

export function usePayments() {
  return useQuery({
    queryKey: ["payments"],
    queryFn: () => apiFetch<{ data: PaymentPlan[] }>("/get_payments.php").then((r) => r.data),
  })
}

export function useSavePayment() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (input: PaymentInput) => apiPostForm<{ status: string }>("/save_payment.php", input as unknown as Record<string, string | number | Blob | null | undefined>),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["payments"] })
    },
  })
}

export interface ExamResult {
  id: number
  student_id: number
  exam_name: string
  score: string
  max_score: string
  exam_date: string | null
  note: string | null
}

export interface ExamInput {
  student_id: number
  exam_name: string
  score: string
  max_score: string
  exam_date: string
  note: string
}

export function useExam(studentId: number | null) {
  return useQuery({
    queryKey: ["exam", studentId],
    enabled: studentId != null,
    queryFn: () =>
      apiFetch<{ data: ExamResult | null }>(`/get_student_exam.php?student_id=${studentId}`).then((r) => r.data),
  })
}

export function useSaveExam() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (input: ExamInput) => apiPostForm<{ status: string }>("/save_student_exam.php", input as unknown as Record<string, string | number | Blob | null | undefined>),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["exam"] })
    },
  })
}

export interface RepairNote {
  id: number
  student_id: number
  repair_title: string
  comment: string | null
  trainer_name: string | null
  created_at: string
}

export function useRepairs(studentId: number | null) {
  return useQuery({
    queryKey: ["repairs", studentId],
    enabled: studentId != null,
    queryFn: () =>
      apiFetch<{ data: RepairNote[] }>(`/get_real_world_repairs.php?student_id=${studentId}`).then((r) => r.data),
  })
}

export interface ProgressHistoryRow {
  id: number
  student_id: number
  item_id: number
  status: string
  completion_date: string
  trainer_name: string | null
  created_at: string
  title: string
  type: "Course" | "Practical"
}

export function useHistory(studentId: number | null) {
  return useQuery({
    queryKey: ["history", studentId],
    enabled: studentId != null,
    queryFn: () =>
      apiFetch<{ data: ProgressHistoryRow[] }>(`/get_history.php?student_id=${studentId}`).then((r) => r.data),
  })
}

export function useSaveRepair() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { student_id: number; repair_title: string; comment?: string; trainer_name?: string }) =>
      apiPostForm<{ status: string }>("/save_real_world_repair.php", {
        student_ids: JSON.stringify([fields.student_id]),
        repair_title: fields.repair_title,
        comment: fields.comment ?? "",
        trainer_name: fields.trainer_name ?? "Instructor",
      }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["repairs"] })
    },
  })
}

/** Per-student analytics — progress, attendance, exam, learning speed, repairs, timeline. */
export interface StudentAnalytics {
  total: number
  done: number
  inProgress: number
  byCategory: Record<string, { total: number; done: number }>
  byType: Record<string, { total: number; done: number }>
  attendance: { total: number; byStatus: Record<string, number>; monthly: Array<{ ym: string; status: string; c: number }> }
  exam: { exam_name: string; score: string; max_score: string; exam_date: string } | null
  weekly: Array<{ yw: number; c: number; start: string; end: string }>
  repairCount: number
  repairByTitle: Record<string, number>
  timeline: Array<{ title: string; category: string; type: string; completion_date: string }>
}

export function useStudentAnalytics(studentId: number | null) {
  return useQuery({
    queryKey: ["student-analytics", studentId],
    enabled: studentId != null,
    queryFn: () =>
      apiFetch<{ data: StudentAnalytics }>(`/get_student_analytics.php?student_id=${studentId}`).then((r) => r.data),
  })
}
