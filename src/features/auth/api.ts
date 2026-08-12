import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { apiFetch, apiPostForm } from "@/lib/api"
import type { LoginResponse, Me } from "./types"

export function useMe() {
  return useQuery({
    queryKey: ["me"],
    queryFn: () => apiFetch<{ data: Me }>("/me.php").then((r) => r.data),
    retry: false,
    staleTime: 5 * 60 * 1000,
  })
}

export function useLogin() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ email, password }: { email: string; password: string }) =>
      apiPostForm<LoginResponse>("/login.php", { email, password }),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["me"] })
    },
  })
}

export function useLogout() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: () => apiFetch<LoginResponse>("/logout.php"),
    onSuccess: () => {
      qc.setQueryData(["me"], null)
      qc.clear()
    },
  })
}
