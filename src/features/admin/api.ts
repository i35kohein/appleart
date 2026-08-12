import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { apiFetch, apiPostForm } from "@/lib/api"

export interface Trainer {
  id: number
  name: string
  role: string
  photo_path: string | null
}

export interface AppUser {
  id: number
  name: string
  email: string
  role: string
}

export interface SettingsData {
  show_today_schedule?: boolean
  show_rollcall_lessons?: boolean
  rollcall_schedule?: { days: number[]; start_time: string; end_time: string; enabled?: boolean }
  rollcall_schedules: {
    Weekday: { days: number[]; start_time: string; end_time: string; enabled?: boolean }
    Weekend: { days: number[]; start_time: string; end_time: string; enabled?: boolean }
  }
  [key: string]: unknown
}

export function useTrainers() {
  return useQuery({
    queryKey: ["trainers"],
    queryFn: () => apiFetch<{ data: Trainer[] }>("/get_trainers.php").then((r) => r.data),
  })
}

export function useUsers() {
  return useQuery({
    queryKey: ["users"],
    queryFn: () => apiFetch<{ data: AppUser[] }>("/get_users.php").then((r) => r.data),
  })
}

export function useSettings() {
  return useQuery({
    queryKey: ["settings"],
    queryFn: () => apiFetch<{ data: SettingsData }>("/get_settings.php").then((r) => r.data),
  })
}

export function useSaveTrainer() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { id?: number; name: string; role: string }) =>
      apiPostForm<{ status: string }>("/save_trainer.php", fields),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["trainers"] }),
  })
}

export function useDeleteTrainer() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => apiPostForm<{ status: string }>("/delete_trainer.php", { id }),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["trainers"] }),
  })
}

export function useSaveCurriculumItem() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { id?: number; type: string; category: string; title: string; tags?: string; practice?: string; details?: string }) =>
      apiPostForm<{ status: string }>("/save_curriculum.php", fields),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["curriculum"] })
      qc.invalidateQueries({ queryKey: ["calendar"] })
    },
  })
}

export function useDeleteCurriculumItem() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => apiPostForm<{ status: string }>("/delete_curriculum.php", { id }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["curriculum"] })
      qc.invalidateQueries({ queryKey: ["calendar"] })
    },
  })
}

export function useSaveAiSettings() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { ai_api_key: string; ai_model: string; ai_base_url: string }) =>
      apiPostForm<{ status: string }>("/save_ai_settings.php", fields),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["settings"] })
    },
  })
}

export function useSaveRollcallSettings() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { show_today_schedule?: number; show_rollcall_lessons?: number } & SettingsData["rollcall_schedules"]) => {
      const { show_today_schedule, show_rollcall_lessons, ...schedules } = fields
      const body: Record<string, string | number> = { schedules: JSON.stringify(schedules) }
      if (show_today_schedule != null) body.show_today_schedule = show_today_schedule
      if (show_rollcall_lessons != null) body.show_rollcall_lessons = show_rollcall_lessons
      return apiPostForm<{ status: string }>("/save_rollcall_settings.php", body)
    },
    onSuccess: () => qc.invalidateQueries({ queryKey: ["settings"] }),
  })
}

export function useRegisterUser() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { name: string; email: string; password: string; role: string }) =>
      apiPostForm<{ status: string }>("/register_user.php", fields),
    onSuccess: () => qc.invalidateQueries({ queryKey: ["users"] }),
  })
}
