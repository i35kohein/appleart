import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { apiFetch, apiPostForm } from "@/lib/api"

export interface FinanceRow {
  id: number
  [key: string]: string | number | null
}

export interface FinanceData {
  assets: FinanceRow[]
  expenses: FinanceRow[]
  shares: FinanceRow[]
  money_out: FinanceRow[]
  income: Array<{
    student_id: number
    student_name: string
    total_amount: string
    first_amount: string
    first_paid_at: string | null
    second_amount: string
    second_paid_at: string | null
    reminder_date: string | null
    note: string | null
    paid_amount: number
    remaining_amount: number
  }>
}

export function useFinance() {
  return useQuery({
    queryKey: ["finance"],
    queryFn: () => apiFetch<{ data: FinanceData }>("/get_finance.php").then((r) => r.data),
  })
}

export function useSaveFinance() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (fields: { action: "add" | "delete"; type: "assets" | "expenses" | "shares" | "money_out"; id?: number } & Record<string, string | number>) =>
      apiPostForm<{ status: string }>("/save_finance.php", fields),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ["finance"] })
    },
  })
}
