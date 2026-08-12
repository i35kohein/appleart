import { useMemo, useState } from "react"
import { ChevronLeft, ChevronRight } from "lucide-react"
import { useAttendanceMonth, useMarkRollcall } from "@/features/rollcall/api"
import type { AttendanceLog } from "@/features/rollcall/api"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Skeleton } from "@/components/ui/skeleton"
import { StudentAvatar } from "@/components/ui/avatar"
import { cn } from "@/lib/utils"
import { EmptyState } from "@/components/common/feedback"

const MONTHS = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December",
]
const WEEKDAYS = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]

type MarkStatus = "Present" | "Absent" | "Late"

function todayStr() {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`
}

export function RollCallCalendar() {
  const now = new Date()
  const [month, setMonth] = useState(`${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`)
  const [selectedDay, setSelectedDay] = useState<string | null>(null)
  const { data: logs, isLoading } = useAttendanceMonth(month)
  const mark = useMarkRollcall()

  const year = Number(month.slice(0, 4))
  const monthNum = Number(month.slice(5))
  const daysInMonth = new Date(year, monthNum, 0).getDate()
  const firstWeekday = new Date(year, monthNum - 1, 1).getDay()
  const tStr = todayStr()

  // logs grouped by date
  const byDate = useMemo(() => {
    const map = new Map<string, AttendanceLog[]>()
    for (const log of logs ?? []) {
      const key = log.created_at.slice(0, 10)
      if (!map.has(key)) map.set(key, [])
      map.get(key)!.push(log)
    }
    return map
  }, [logs])

  const dayKeys = useMemo(() => {
    const keys: Array<string | null> = []
    for (let i = 0; i < firstWeekday; i++) keys.push(null)
    for (let d = 1; d <= daysInMonth; d++) keys.push(`${month}-${String(d).padStart(2, "0")}`)
    return keys
  }, [month, firstWeekday, daysInMonth])

  const nav = (delta: number) => {
    const d = new Date(year, monthNum - 1 + delta, 1)
    setMonth(`${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`)
    setSelectedDay(null)
  }

  const dayLogs = selectedDay ? (byDate.get(selectedDay) ?? []) : []
  const selectedCounts = {
    Present: dayLogs.filter((l) => l.status === "Present").length,
    Late: dayLogs.filter((l) => l.status === "Late").length,
    Absent: dayLogs.filter((l) => l.status === "Absent").length,
  }

  const setDayStatus = (studentId: number, status: MarkStatus) => {
    if (!selectedDay) return
    mark.mutate({ student_id: studentId, status, date: selectedDay })
  }

  return (
    <div className="space-y-4">
      {/* Month nav */}
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-1">
          <Button variant="outline" size="icon" onClick={() => nav(-1)} aria-label="Previous month">
            <ChevronLeft />
          </Button>
          <div className="min-w-40 text-center text-base font-semibold tracking-tight">
            {MONTHS[monthNum - 1]} {year}
          </div>
          <Button variant="outline" size="icon" onClick={() => nav(1)} aria-label="Next month">
            <ChevronRight />
          </Button>
          <Button variant="ghost" size="sm" onClick={() => { setMonth(tStr.slice(0, 7)); setSelectedDay(null) }} className="ml-1 text-xs">
            Today
          </Button>
        </div>
        <div className="flex items-center gap-3 text-xs text-muted-foreground">
          <span className="flex items-center gap-1.5"><span className="size-2 rounded-full bg-emerald-500" /> Present</span>
          <span className="flex items-center gap-1.5"><span className="size-2 rounded-full bg-amber-500" /> Late</span>
          <span className="flex items-center gap-1.5"><span className="size-2 rounded-full bg-destructive" /> Absent</span>
        </div>
      </div>

      {isLoading ? (
        <div className="grid grid-cols-7 gap-1.5">
          {Array.from({ length: 35 }).map((_, i) => <Skeleton key={i} className="h-20 rounded-lg" />)}
        </div>
      ) : (
        <div className="grid grid-cols-7 gap-1.5">
          {WEEKDAYS.map((d) => (
            <div key={d} className="px-1 py-1 text-center text-xs font-semibold text-muted-foreground">{d}</div>
          ))}
          {dayKeys.map((key, i) => {
            if (key === null) return <div key={`e-${i}`} className="min-h-20 rounded-lg border border-dashed bg-muted/10" />
            const dayLogs = byDate.get(key) ?? []
            const counts = {
              Present: dayLogs.filter((l) => l.status === "Present").length,
              Late: dayLogs.filter((l) => l.status === "Late").length,
              Absent: dayLogs.filter((l) => l.status === "Absent").length,
            }
            const isToday = key === tStr
            return (
              <button
                key={key}
                type="button"
                onClick={() => setSelectedDay(key)}
                className={cn(
                  "flex min-h-20 flex-col rounded-lg border bg-card p-1.5 text-left transition-colors hover:bg-accent",
                  isToday && "border-primary ring-1 ring-primary/40",
                  selectedDay === key && "border-primary bg-primary/10 dark:bg-primary/20",
                )}
              >
                <div className={cn("mb-1 text-xs font-semibold", isToday ? "text-emerald-600" : "text-muted-foreground")}>
                  {Number(key.slice(8))}
                </div>
                {dayLogs.length === 0 ? (
                  <span className="mt-1 text-[10px] text-muted-foreground/50">No records</span>
                ) : (
                  <div className="space-y-0.5">
                    {counts.Present > 0 && (
                      <span className="flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400">
                        <span className="size-1.5 rounded-full bg-emerald-500" /> {counts.Present} present
                      </span>
                    )}
                    {counts.Late > 0 && (
                      <span className="flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400">
                        <span className="size-1.5 rounded-full bg-amber-500" /> {counts.Late} late
                      </span>
                    )}
                    {counts.Absent > 0 && (
                      <span className="flex items-center gap-1 text-[10px] text-red-600 dark:text-red-400">
                        <span className="size-1.5 rounded-full bg-destructive" /> {counts.Absent} absent
                      </span>
                    )}
                  </div>
                )}
              </button>
            )
          })}
        </div>
      )}

      {/* Day detail dialog */}
      <Dialog open={selectedDay != null} onOpenChange={(o) => !o && setSelectedDay(null)}>
        <DialogContent className="sm:max-w-lg">
          <DialogHeader>
            <DialogTitle>Roll call — {selectedDay}</DialogTitle>
            <DialogDescription>
              {dayLogs.length > 0
                ? `${selectedCounts.Present} present · ${selectedCounts.Late} late · ${selectedCounts.Absent} absent`
                : "No attendance recorded for this day."}
            </DialogDescription>
          </DialogHeader>

          <div className="max-h-[55vh] space-y-1.5 overflow-y-auto pr-1">
            {isLoading ? (
              <Skeleton className="h-32 w-full" />
            ) : dayLogs.length === 0 ? (
              <EmptyState title="No records for this day." className="py-8" />
            ) : (
              dayLogs.map((log) => (
                <div key={log.id} className="flex items-center gap-3 rounded-lg border bg-muted/20 p-2.5">
                  <StudentAvatar name={log.name} photoPath={log.photo_path} className="size-8" />
                  <div className="min-w-0 flex-1">
                    <div className="truncate text-sm font-medium">{log.name}</div>
                    <div className="text-[11px] text-muted-foreground">
                      {log.created_at.slice(11, 16)} {log.is_active === 1 ? "" : "· inactive"}
                    </div>
                  </div>
                  <div className="flex gap-1">
                    {(["Present", "Late", "Absent"] as MarkStatus[]).map((status) => (
                      <button
                        key={status}
                        type="button"
                        onClick={() => setDayStatus(log.student_id, status)}
                        disabled={mark.isPending}
                        className={cn(
                          "rounded-md border px-2 py-1 text-[11px] font-medium transition-colors disabled:opacity-50",
                          log.status === status
                            ? status === "Present"
                              ? "border-emerald-500 bg-emerald-600 text-white"
                              : status === "Late"
                                ? "border-amber-500 bg-amber-500 text-white"
                                : "border-destructive bg-destructive text-white"
                            : "border-border text-muted-foreground hover:bg-accent",
                        )}
                      >
                        {status}
                      </button>
                    ))}
                  </div>
                </div>
              ))
            )}
          </div>
        </DialogContent>
      </Dialog>
    </div>
  )
}
