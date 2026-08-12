import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { apiFetch, apiPostForm } from "@/lib/api"
export interface CalendarItem {
  id: number
  title: string
  category: string
  done: boolean
}

export interface CalendarDayEntry {
  student_id: number
  student_name: string
  group: "Weekday" | "Weekend"
  is_training: boolean
  course: CalendarItem | null
  practical: CalendarItem | null
  scheduled: Array<{
    id: number
    schedule_date: string
    start_time: string | null
    end_time: string | null
    teacher_name: string | null
    student_group: string | null
    lesson_type: string | null
    topic: string | null
    status: string | null
  }>
}

export interface CalendarResponse {
  status: "success"
  month: string
  year: number
  month_num: number
  days_in_month: number
  schedules: Record<string, { days: number[]; start_time: string; end_time: string }>
  students: Array<{ id: number; name: string; rollcall_group: string; enrollment_date: string | null; is_active: number }>
  course_total: number
  practical_total: number
  days: Record<string, CalendarDayEntry[]>
  stats: Array<{
    student_id: number
    student_name: string
    group: string
    course_done: number
    course_total: number
    practical_done: number
    practical_total: number
  }>
  teacher_schedule: CalendarDayEntry["scheduled"]
}

export function useCalendar(month: string, studentId: string) {
  return useQuery({
    queryKey: ["calendar", month, studentId],
    queryFn: () =>
      apiFetch<CalendarResponse>(
        `/get_calendar.php?month=${month}${studentId ? `&student_id=${studentId}` : ""}`,
      ),
  })
}

export function useSaveCalendarProgress() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { student_id: number; item_id: number; status: string; completion_date?: string }) =>
      apiPostForm<{ status: string }>("/update_progress.php", fields),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["calendar"] })
      qc.invalidateQueries({ queryKey: ["today"] })
      qc.invalidateQueries({ queryKey: ["students"] })
      qc.invalidateQueries({ queryKey: ["student-progress"] })
    },
  })
}

export interface TeacherScheduleInput {
  action?: "save" | "delete"
  id?: number
  schedule_date: string
  start_time: string
  end_time?: string
  teacher_name: string
  student_group?: string
  lesson_type?: string
  topic: string
  room_name?: string
  status?: string
}

export function useSaveTeacherSchedule() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (input: TeacherScheduleInput) =>
      apiPostForm<{ status: string; id?: number }>("/save_teacher_schedule.php", {
        ...input,
        action: input.action ?? "save",
      }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["calendar"] })
      qc.invalidateQueries({ queryKey: ["today"] })
    },
  })
}

export function useDeleteTeacherSchedule() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) =>
      apiPostForm<{ status: string }>("/save_teacher_schedule.php", {
        action: "delete",
        id,
      }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["calendar"] })
      qc.invalidateQueries({ queryKey: ["today"] })
    },
  })
}
