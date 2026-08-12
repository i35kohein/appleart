import { createContext, useContext, useMemo, useState, type ReactNode } from "react"

export function todayStr(): string {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`
}

interface TimeMachineValue {
  /** Selected working date (YYYY-MM-DD). Defaults to today. */
  date: string
  setDate: (date: string) => void
  /** True when a non-today date is selected (time travelling). */
  isTimeTraveling: boolean
  /** Back to today. */
  reset: () => void
}

const TimeMachineContext = createContext<TimeMachineValue | null>(null)

const STORAGE_KEY = "appleart_time_machine_date"

export function TimeMachineProvider({ children }: { children: ReactNode }) {
  const [date, setDateState] = useState<string>(() => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY)
      if (saved && /^\d{4}-\d{2}-\d{2}$/.test(saved)) return saved
    } catch {
      /* ignore */
    }
    return todayStr()
  })

  const setDate = (d: string) => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(d)) return
    setDateState(d)
    try {
      localStorage.setItem(STORAGE_KEY, d)
    } catch {
      /* ignore */
    }
  }

  const reset = () => setDate(todayStr())

  const value = useMemo<TimeMachineValue>(
    () => ({ date, setDate, isTimeTraveling: date !== todayStr(), reset }),
    [date],
  )

  return <TimeMachineContext.Provider value={value}>{children}</TimeMachineContext.Provider>
}

export function useTimeMachine(): TimeMachineValue {
  const ctx = useContext(TimeMachineContext)
  if (!ctx) throw new Error("useTimeMachine must be used within TimeMachineProvider")
  return ctx
}
