import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { apiFetch, apiPostForm } from "@/lib/api"
import type { CurriculumItem } from "@/features/students/types"

export interface PortalStudent {
  id: number
  name: string
  email: string | null
  phone: string | null
  photo_path: string | null
  rollcall_group: string | null
}

export interface PortalProgressRow {
  id: number
  item_id: number
  detail_idx: number | null
  status: "Pending" | "In Progress" | "Completed"
  completion_date: string
}

export interface PortalData {
  student: PortalStudent
  curriculum: CurriculumItem[]
  progress: PortalProgressRow[]
}

/** Current logged-in student (session). Returns { student } or throws when not logged in. */
export function useStudentMe() {
  return useQuery({
    queryKey: ["student-me"],
    queryFn: () => apiFetch<{ student: PortalStudent }>("/student_me.php").then((r) => r.student),
    retry: false,
  })
}

export function useStudentLogin() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { email: string; password: string; remember?: boolean }) =>
      apiPostForm<{ status: string; student: PortalStudent }>("/student_login.php", { ...fields, remember: fields.remember ? "1" : "0" }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["student-me"] })
      qc.invalidateQueries({ queryKey: ["student-portal"] })
    },
  })
}

export function useStudentLogout() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: () => apiPostForm<{ status: string }>("/student_logout.php", {}),
    onSuccess: () => {
      qc.setQueryData(["student-me"], () => undefined)
      qc.removeQueries({ queryKey: ["student-portal"] })
      qc.invalidateQueries({ queryKey: ["student-me"] })
    },
  })
}

export function useStudentPortalData() {
  const { data: student } = useStudentMe()
  return useQuery({
    queryKey: ["student-portal"],
    enabled: student != null,
    queryFn: () => apiFetch<{ data: PortalData }>("/student_portal_data.php").then((r) => r.data),
  })
}

/** Self-registration — creates an online-role e-learning account, auto-login. */
export function useStudentSignup() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { name: string; email: string; phone: string; password: string }) =>
      apiPostForm<{ status: string; student: PortalStudent }>("/student_signup.php", fields),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["student-me"] })
      qc.invalidateQueries({ queryKey: ["student-portal"] })
    },
  })
}

/** Update own profile (name / email / phone). */
export function useStudentUpdateProfile() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { name: string; email: string; phone: string }) =>
      apiPostForm<{ status: string }>("/student_update_profile.php", fields),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["student-me"] })
      qc.invalidateQueries({ queryKey: ["student-portal"] })
    },
  })
}

/** Change own password (requires current password). */
export function useStudentChangePassword() {
  return useMutation({
    mutationFn: (fields: { current_password: string; new_password: string }) =>
      apiPostForm<{ status: string }>("/student_change_password.php", fields),
  })
}

/** Student marks their OWN progress (lesson-level or step). */
export function useStudentUpdateProgress() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { student_id: number; item_id: number; status: string; detail_idx?: number | null }) =>
      apiPostForm<{ status: string }>("/update_progress.php", {
        student_id: fields.student_id,
        item_id: fields.item_id,
        status: fields.status,
        detail_idx: fields.detail_idx ?? "",
      }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["student-portal"] })
    },
  })
}
