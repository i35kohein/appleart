import { useEffect, useMemo, useRef, useState } from "react"
import { Link } from "react-router-dom"
import { Maximize, MonitorPlay, RotateCcw, School, Wrench } from "lucide-react"
import { useToday } from "@/features/students/api"
import { useSettings } from "@/features/admin/api"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Progress } from "@/components/ui/progress"
import { Skeleton } from "@/components/ui/skeleton"
import { cn } from "@/lib/utils"

const ROTATE_MS = 8000

export function TodayScreenPage() {
  const { data: settings } = useSettings()
  const { data: today, isLoading } = useToday()
  const [selected, setSelected] = useState<Set<number>>(new Set())
  const [idx, setIdx] = useState(0)
  const [auto, setAuto] = useState(true)
  const stageRef = useRef<HTMLDivElement>(null)

  const students = today?.students ?? []

  // default-select all training students once loaded
  useEffect(() => {
    if (students.length > 0) {
      setSelected((prev) => {
        if (prev.size > 0) return prev
        return new Set(students.filter((s) => s.is_training).map((s) => s.student_id))
      })
    }
  }, [today]) // eslint-disable-line react-hooks/exhaustive-deps

  const rotation = useMemo(
    () => students.filter((s) => selected.has(s.student_id)),
    [students, selected],
  )

  // auto-rotate
  useEffect(() => {
    if (!auto || rotation.length <= 1) return
    const t = setInterval(() => setIdx((i) => (i + 1) % rotation.length), ROTATE_MS)
    return () => clearInterval(t)
  }, [auto, rotation.length])

  const toggleStudent = (id: number) => {
    setSelected((prev) => {
      const next = new Set(prev)
      if (next.has(id)) next.delete(id)
      else next.add(id)
      return next
    })
    setIdx(0)
  }

  const goFullscreen = () => {
    stageRef.current?.requestFullscreen?.().catch(() => {})
  }

  if (isLoading) {
    return (
      <div className="space-y-4">
        <Skeleton className="h-10 w-72" />
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          {Array.from({ length: 4 }).map((_, i) => <Skeleton key={i} className="h-64 rounded-2xl" />)}
        </div>
      </div>
    )
  }

  const current = rotation[idx] ?? rotation[0]

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-center justify-end gap-2">
          <Button variant={auto ? "default" : "outline"} onClick={() => setAuto((a) => !a)}>
            <RotateCcw className="size-4" /> Auto-rotate {auto ? "on" : "off"}
          </Button>
          <Button onClick={goFullscreen}>
            <Maximize className="size-4" /> Fullscreen
          </Button>
      </div>

      {/* Student selection */}
      <div className="flex flex-wrap items-center gap-2 rounded-lg border bg-card p-3">
        <span className="text-xs font-medium text-muted-foreground">Show:</span>
        {students.map((s) => (
          <button
            key={s.student_id}
            type="button"
            onClick={() => toggleStudent(s.student_id)}
            className={cn(
              "rounded-full border px-3 py-1 text-xs font-medium transition-colors",
              selected.has(s.student_id)
                ? "border-primary bg-primary text-white"
                : "border-border text-muted-foreground hover:bg-accent",
            )}
          >
            {s.student_name}
          </button>
        ))}
        {students.length === 0 && <span className="text-sm text-muted-foreground">No trainees enrolled.</span>}
      </div>

      {/* Stage */}
      {rotation.length === 0 ? (
        <div className="flex h-72 flex-col items-center justify-center gap-2 rounded-2xl border border-dashed text-center">
          <MonitorPlay className="size-10 text-muted-foreground/40" />
          <p className="text-sm font-medium">Select at least one trainee</p>
        </div>
      ) : (
        <div ref={stageRef} className="overflow-hidden rounded-2xl border bg-card">
          <div className="flex items-center justify-between gap-3 border-b px-5 py-3">
            <div className="flex min-w-0 items-center gap-3">
              <div className="flex size-10 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">
                {current.student_name.slice(0, 2).toUpperCase()}
              </div>
              <div className="min-w-0">
                <div className="truncate text-lg font-semibold tracking-tight">{current.student_name}</div>
                <div className="text-xs text-muted-foreground">{current.group} · {current.phone ?? "—"}</div>
              </div>
            </div>
            <div className="flex items-center gap-1.5">
              {rotation.map((s, i) => (
                <button
                  key={s.student_id}
                  type="button"
                  onClick={() => setIdx(i)}
                  aria-label={`Show ${s.student_name}`}
                  className={cn(
                    "h-2 rounded-full transition-all",
                    i === idx ? "w-6 bg-primary" : "w-2 bg-muted-foreground/30 hover:bg-muted-foreground/50",
                  )}
                />
              ))}
            </div>
          </div>

          <div className="grid gap-4 p-5 md:grid-cols-2">
            {current.is_training ? (
              <>
                <div className="rounded-xl border bg-muted/20 p-4">
                  <div className="mb-2 flex items-center gap-2 text-sm font-semibold">
                    <School className="size-4 text-primary" /> Next course
                  </div>
                  {current.course ? (
                    <>
                      <div className="text-xl font-semibold tracking-tight">{current.course.title}</div>
                      <div className="mt-1 text-xs text-muted-foreground">{current.course.category}</div>
                    </>
                  ) : (
                    <div className="text-lg font-medium text-muted-foreground">🎓 All courses complete</div>
                  )}
                  <div className="mt-4 flex items-center gap-2">
                    <Progress value={current.course_total ? (current.course_done / current.course_total) * 100 : 0} className="h-1.5 flex-1" />
                    <span className="text-xs tabular-nums text-muted-foreground">{current.course_done}/{current.course_total}</span>
                  </div>
                </div>

                <div className="rounded-xl border bg-muted/20 p-4">
                  <div className="mb-2 flex items-center gap-2 text-sm font-semibold">
                    <Wrench className="size-4 text-amber-600" /> Next practical
                  </div>
                  {current.practical ? (
                    <>
                      <div className="text-xl font-semibold tracking-tight">{current.practical.title}</div>
                      <div className="mt-1 text-xs text-muted-foreground">{current.practical.category}</div>
                    </>
                  ) : (
                    <div className="text-lg font-medium text-muted-foreground">🎓 All practicals complete</div>
                  )}
                  <div className="mt-4 flex items-center gap-2">
                    <Progress value={current.practical_total ? (current.practical_done / current.practical_total) * 100 : 0} className="h-1.5 flex-1" />
                    <span className="text-xs tabular-nums text-muted-foreground">{current.practical_done}/{current.practical_total}</span>
                  </div>
                </div>
              </>
            ) : (
              <div className="flex items-center justify-center rounded-xl border p-10 md:col-span-2">
                <Badge variant="secondary" className="py-2 text-base">Rest day</Badge>
              </div>
            )}

            {Boolean(settings?.show_today_schedule) && current.scheduled.length > 0 && (
              <div className="rounded-xl border bg-amber-50 p-4 dark:bg-amber-950/40 md:col-span-2">
                <div className="mb-2 text-sm font-semibold text-amber-800 dark:text-amber-300">Scheduled sessions today</div>
                <div className="flex flex-wrap gap-2">
                  {current.scheduled.map((s, si) => (
                    <span key={si} className="rounded-md bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">
                      {s.start_time?.slice(0, 5)} {s.lesson_type}: {s.topic}
                    </span>
                  ))}
                </div>
              </div>
            )}
          </div>

          <div className="flex items-center justify-between border-t px-5 py-2.5 text-xs text-muted-foreground">
            <span>Auto-rotate every {ROTATE_MS / 1000}s · {idx + 1} / {rotation.length}</span>
            <Link to="/calendar" className="hover:text-primary hover:underline">Open calendar →</Link>
          </div>
        </div>
      )}
    </div>
  )
}
