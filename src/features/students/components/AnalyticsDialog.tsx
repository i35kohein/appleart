import { BarChart3, BookOpen, CalendarCheck, GraduationCap, TrendingUp, Wrench } from "lucide-react"
import { useStudentAnalytics } from "@/features/students/detail-api"
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Progress } from "@/components/ui/progress"
import { Skeleton } from "@/components/ui/skeleton"
import { cn, pct } from "@/lib/utils"

const fmtDate = (s: string) => {
  const d = new Date(s)
  if (Number.isNaN(d.getTime())) return s.slice(0, 10)
  return d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" })
}

function Ring({ value }: { value: number }) {
  const r = 30
  const c = 2 * Math.PI * r
  return (
    <div className="relative size-20 shrink-0">
      <svg viewBox="0 0 80 80" className="size-20 -rotate-90">
        <circle cx="40" cy="40" r={r} fill="none" strokeWidth="9" className="stroke-muted" />
        <circle
          cx="40" cy="40" r={r} fill="none" strokeWidth="9" strokeLinecap="round"
          className="stroke-primary transition-all duration-700"
          strokeDasharray={`${(value / 100) * c} ${c}`}
        />
      </svg>
      <span className="absolute inset-0 flex items-center justify-center text-sm font-bold tabular-nums">{Math.round(value)}%</span>
    </div>
  )
}

function Stat({ icon, label, value, sub }: { icon: React.ReactNode; label: string; value: string; sub?: string }) {
  return (
    <div className="rounded-xl border bg-muted/20 px-3 py-2.5">
      <div className="flex items-center gap-1.5 text-[11px] text-muted-foreground">
        <span className="text-primary">{icon}</span> {label}
      </div>
      <div className="mt-0.5 truncate text-base font-semibold tabular-nums">{value}</div>
      {sub ? <div className="truncate text-[11px] text-muted-foreground/80">{sub}</div> : null}
    </div>
  )
}

export function AnalyticsDialog({ studentId, studentName, open, onOpenChange }: { studentId: number; studentName: string; open: boolean; onOpenChange: (o: boolean) => void }) {
  const { data: a, isLoading } = useStudentAnalytics(open ? studentId : null)

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="flex max-h-[85vh] flex-col overflow-hidden sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <span className="flex size-7 items-center justify-center rounded-lg bg-primary/10 text-primary"><BarChart3 className="size-4" /></span>
            Analytics — {studentName}
          </DialogTitle>
          <DialogDescription>တိုးတက်မှု၊ attendance၊ စာမေးပွဲ၊ သင်ယူနှုန်း၊ လက်တွေ့အတွေ့အကြုံ</DialogDescription>
        </DialogHeader>

        <div className="flex-1 space-y-5 overflow-y-auto pr-1">
          {isLoading || !a ? (
            <div className="space-y-3">
              <Skeleton className="h-24 w-full" />
              <Skeleton className="h-40 w-full" />
              <Skeleton className="h-24 w-full" />
            </div>
          ) : (
            <>
              {/* Stat row */}
              <div className="grid grid-cols-2 gap-2.5 sm:grid-cols-4">
                <Stat icon={<BookOpen className="size-3.5" />} label="Overall progress" value={`${a.done}/${a.total}`} sub={`${a.inProgress} in progress`} />
                <Stat icon={<GraduationCap className="size-3.5" />} label="Attendance" value={a.attendance.total ? `${pct(a.attendance.byStatus.Present ?? 0, a.attendance.total)}%` : "—"} sub={`${a.attendance.total} sessions`} />
                <Stat icon={<TrendingUp className="size-3.5" />} label="Exam" value={a.exam ? `${Number(a.exam.score)}/${Number(a.exam.max_score)}` : "—"} sub={a.exam?.exam_name ?? "No exam yet"} />
                <Stat icon={<Wrench className="size-3.5" />} label="Real repairs" value={String(a.repairCount)} sub={a.repairCount ? `${Object.keys(a.repairByTitle).length} types` : "No repairs yet"} />
              </div>

              {/* Progress rings + category bars */}
              <div className="grid gap-4 sm:grid-cols-[auto_minmax(0,1fr)]">
                <div className="flex flex-col items-center gap-1 rounded-xl border bg-muted/20 p-3">
                  <Ring value={pct(a.done, a.total)} />
                  <span className="text-xs font-medium">Completed</span>
                </div>
                <div className="space-y-2.5">
                  <TypeBar label="Course" done={a.byType.Course?.done ?? 0} total={a.byType.Course?.total ?? 0} />
                  <TypeBar label="Practical" done={a.byType.Practical?.done ?? 0} total={a.byType.Practical?.total ?? 0} />
                  <div className="space-y-2 pt-1">
                    {Object.entries(a.byCategory).map(([cat, v]) => (
                      <div key={cat} className="flex items-center gap-2 text-xs">
                        <span className="w-36 shrink-0 truncate text-muted-foreground">{cat}</span>
                        <Progress value={pct(v.done, v.total)} className="h-1.5 flex-1" />
                        <span className="w-14 shrink-0 text-right tabular-nums text-muted-foreground">{v.done}/{v.total}</span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              {/* Learning speed: lessons/week bar chart (last 8 weeks) */}
              <div>
                <h4 className="mb-2 flex items-center gap-1.5 text-xs font-semibold text-muted-foreground">
                  <TrendingUp className="size-3.5 text-primary" /> သင်ယူနှုန်း — lessons completed per week (နောက်ဆုံး 8 ပတ်)
                </h4>
                {a.weekly.every((w) => w.c === 0) ? (
                  <p className="rounded-lg border bg-muted/20 px-3 py-4 text-center text-xs text-muted-foreground">No completions yet.</p>
                ) : (
                  <div className="flex h-28 items-end gap-1.5 rounded-xl border bg-muted/20 px-3 pb-2 pt-3">
                    {a.weekly.map((w) => {
                      const max = Math.max(...a.weekly.map((x) => x.c), 1)
                      const h = w.c === 0 ? 3 : Math.max(8, (w.c / max) * 62)
                      return (
                        <div key={w.yw} className="flex flex-1 flex-col items-center gap-1">
                          <span className={cn("text-[10px] font-semibold tabular-nums", w.c === 0 ? "text-muted-foreground/50" : "text-foreground")}>{w.c}</span>
                          <div
                            className={cn("w-full rounded-t transition-all", w.c === 0 ? "bg-muted-foreground/20" : "bg-primary/80")}
                            style={{ height: `${h}px` }}
                          />
                          <span className="text-[9px] tabular-nums text-muted-foreground">
                            {new Date(w.start + "T00:00:00").toLocaleDateString("en-GB", { day: "numeric", month: "short" })}
                          </span>
                        </div>
                      )
                    })}
                  </div>
                )}
              </div>

              {/* Timeline */}
              <div>
                <h4 className="mb-2 flex items-center gap-1.5 text-xs font-semibold text-muted-foreground">
                  <CalendarCheck className="size-3.5 text-primary" /> ပြီးခဲ့တဲ့ သင်ခန်းစာတွေ (Timeline)
                </h4>
                {a.timeline.length === 0 ? (
                  <p className="rounded-lg border bg-muted/20 px-3 py-4 text-center text-xs text-muted-foreground">No completed lessons yet.</p>
                ) : (
                  <ul className="max-h-52 space-y-1 overflow-y-auto pr-1">
                    {a.timeline.map((t, i) => (
                      <li key={i} className="flex items-center gap-2 rounded-lg border bg-muted/20 px-3 py-1.5 text-xs">
                        <span className={cn("size-1.5 shrink-0 rounded-full", t.type === "Practical" ? "bg-amber-500" : "bg-primary")} />
                        <span className="min-w-0 flex-1 truncate font-medium">{t.title}</span>
                        <span className="shrink-0 text-muted-foreground/80">{t.category}</span>
                        <span className="shrink-0 tabular-nums text-muted-foreground">{fmtDate(t.completion_date)}</span>
                      </li>
                    ))}
                  </ul>
                )}
              </div>

              {/* Attendance monthly */}
              <div>
                <h4 className="mb-2 flex items-center gap-1.5 text-xs font-semibold text-muted-foreground">
                  <CalendarCheck className="size-3.5 text-primary" /> Attendance လအလိုက်
                </h4>
                {a.attendance.monthly.length === 0 ? (
                  <p className="rounded-lg border bg-muted/20 px-3 py-4 text-center text-xs text-muted-foreground">No attendance records.</p>
                ) : (
                  <div className="flex flex-wrap gap-2">
                    {a.attendance.monthly.map((m, i) => (
                      <div key={i} className="min-w-24 rounded-lg border bg-muted/20 px-3 py-2 text-center">
                        <div className="text-xs font-semibold">{m.ym}</div>
                        <div className="text-[11px] tabular-nums text-muted-foreground">{m.status}: {m.c}</div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      </DialogContent>
    </Dialog>
  )
}

function TypeBar({ label, done, total }: { label: string; done: number; total: number }) {
  return (
    <div className="flex items-center gap-2 text-xs">
      <span className="w-16 shrink-0 font-medium">{label}</span>
      <Progress value={pct(done, total)} className="h-2 flex-1" />
      <span className="w-14 shrink-0 text-right tabular-nums text-muted-foreground">{done}/{total}</span>
    </div>
  )
}
