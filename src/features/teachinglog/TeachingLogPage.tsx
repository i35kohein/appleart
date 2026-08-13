import { useMemo, useState } from "react"
import { BookOpenCheck, CalendarDays, FilterX, TrendingUp, Wrench } from "lucide-react"
import { useStudents } from "@/features/students/api"
import { EFFECT_LABELS, type TeachingEffect, useTeachingLog } from "@/features/teachinglog/api"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Skeleton } from "@/components/ui/skeleton"
import { EmptyState } from "@/components/common/feedback"

const EFFECT_BADGE: Record<TeachingEffect, string> = {
  effective: "bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300",
  partial: "bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300",
  not_effective: "bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300",
}

const EFFECT_DOT: Record<TeachingEffect, string> = {
  effective: "bg-green-500",
  partial: "bg-amber-500",
  not_effective: "bg-red-500",
}

const EFFECT_OPTIONS: TeachingEffect[] = ["effective", "partial", "not_effective"]

function weekdayOf(dateStr: string): string {
  const d = new Date(`${dateStr}T00:00:00`)
  return d.toLocaleDateString("en-US", { weekday: "long" })
}

export function TeachingLogPage() {
  const students = useStudents()
  const [filters, setFilters] = useState<{ from: string; to: string; student_id: string; effect: string }>({
    from: "",
    to: "",
    student_id: "",
    effect: "",
  })

  const log = useTeachingLog({
    from: filters.from || undefined,
    to: filters.to || undefined,
    student_id: filters.student_id ? Number(filters.student_id) : undefined,
    effect: (filters.effect as TeachingEffect) || undefined,
  })

  const rows = log.data ?? []

  const stats = useMemo(() => {
    const total = rows.length
    const counts: Record<TeachingEffect, number> = { effective: 0, partial: 0, not_effective: 0 }
    let withEffect = 0
    for (const r of rows) {
      if (r.effect) {
        counts[r.effect]++
        withEffect++
      }
    }
    const effectiveRate = withEffect > 0 ? Math.round((counts.effective / withEffect) * 100) : 0
    return { total, counts, effectiveRate }
  }, [rows])

  const byDate = useMemo(() => {
    const map = new Map<string, typeof rows>()
    for (const r of rows) {
      const list = map.get(r.log_date) ?? []
      list.push(r)
      map.set(r.log_date, list)
    }
    return [...map.entries()]
  }, [rows])

  const hasFilters = filters.from !== "" || filters.to !== "" || filters.student_id !== "" || filters.effect !== ""

  const clearFilters = () => setFilters({ from: "", to: "", student_id: "", effect: "" })

  return (
    <div className="space-y-4 p-4 md:p-6">
      {/* Header */}
      <div>
        <h1 className="text-xl font-semibold">Teaching Log</h1>
        <p className="text-sm text-muted-foreground">
          Everything from training, by date — lessons taught (steps + completions) and repairs practiced.
        </p>
      </div>

      {/* Summary */}
      <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
        <Card>
          <CardContent className="flex items-center gap-3 p-4">
            <div className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <BookOpenCheck className="size-5" />
            </div>
            <div>
              <p className="text-2xl font-semibold leading-none">{stats.total}</p>
              <p className="mt-1 text-xs text-muted-foreground">Entries</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="flex items-center gap-3 p-4">
            <div className="flex size-10 items-center justify-center rounded-lg bg-green-500/10 text-green-600">
              <TrendingUp className="size-5" />
            </div>
            <div>
              <p className="text-2xl font-semibold leading-none">{stats.effectiveRate}%</p>
              <p className="mt-1 text-xs text-muted-foreground">Effective rate</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="flex items-center gap-3 p-4">
            <div className="size-3 rounded-full bg-amber-500" />
            <div>
              <p className="text-2xl font-semibold leading-none">{stats.counts.partial}</p>
              <p className="mt-1 text-xs text-muted-foreground">Partial</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="flex items-center gap-3 p-4">
            <div className="size-3 rounded-full bg-red-500" />
            <div>
              <p className="text-2xl font-semibold leading-none">{stats.counts.not_effective}</p>
              <p className="mt-1 text-xs text-muted-foreground">Not effective</p>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Filters */}
      <Card>
        <CardContent className="grid grid-cols-2 gap-3 p-4 md:grid-cols-4">
          <div className="space-y-1">
            <Label className="text-xs text-muted-foreground">From</Label>
            <Input type="date" value={filters.from} onChange={(e) => setFilters((f) => ({ ...f, from: e.target.value }))} />
          </div>
          <div className="space-y-1">
            <Label className="text-xs text-muted-foreground">To</Label>
            <Input type="date" value={filters.to} onChange={(e) => setFilters((f) => ({ ...f, to: e.target.value }))} />
          </div>
          <div className="space-y-1">
            <Label className="text-xs text-muted-foreground">Trainee</Label>
            <Select value={filters.student_id} onValueChange={(v) => setFilters((f) => ({ ...f, student_id: v }))}>
              <SelectTrigger aria-label="Filter by trainee">
                <SelectValue placeholder="All trainees" />
              </SelectTrigger>
              <SelectContent>
                {(students.data ?? []).map((s) => (
                  <SelectItem key={s.id} value={String(s.id)}>
                    {s.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-1">
            <Label className="text-xs text-muted-foreground">Effect</Label>
            <Select value={filters.effect} onValueChange={(v) => setFilters((f) => ({ ...f, effect: v }))}>
              <SelectTrigger aria-label="Filter by effect">
                <SelectValue placeholder="All effects" />
              </SelectTrigger>
              <SelectContent>
                {EFFECT_OPTIONS.map((e) => (
                  <SelectItem key={e} value={e}>
                    {EFFECT_LABELS[e]}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          {hasFilters && (
            <Button variant="ghost" className="col-span-2 justify-self-start md:col-span-4" onClick={clearFilters}>
              <FilterX className="size-4" /> Clear filters
            </Button>
          )}
        </CardContent>
      </Card>

      {/* Timeline */}
      {log.isLoading ? (
        <div className="space-y-3">
          <Skeleton className="h-16 w-full" />
          <Skeleton className="h-16 w-full" />
          <Skeleton className="h-16 w-full" />
        </div>
      ) : rows.length === 0 ? (
        <EmptyState
          icon={<CalendarDays className="size-8" />}
          title="No teaching log entries yet"
          hint={
            hasFilters
              ? "Nothing matches these filters."
              : "Mark lessons in Courses or log repairs — everything appears here automatically."
          }
        />
      ) : (
        <div className="space-y-4">
          {byDate.map(([date, entries]) => (
            <Card key={date}>
              <CardContent className="p-4">
                <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                  <div className="flex items-center gap-2">
                    <CalendarDays className="size-4 text-muted-foreground" />
                    <span className="font-semibold">{date}</span>
                    <span className="text-sm text-muted-foreground">{weekdayOf(date)}</span>
                  </div>
                  <div className="flex items-center gap-3 text-xs text-muted-foreground">
                    <span className="flex items-center gap-1">
                      <span className="size-2 rounded-full bg-green-500" /> {entries.filter((e) => e.effect === "effective").length}
                    </span>
                    <span className="flex items-center gap-1">
                      <span className="size-2 rounded-full bg-amber-500" /> {entries.filter((e) => e.effect === "partial").length}
                    </span>
                    <span className="flex items-center gap-1">
                      <span className="size-2 rounded-full bg-red-500" /> {entries.filter((e) => e.effect === "not_effective").length}
                    </span>
                  </div>
                </div>

                <ul className="divide-y">
                  {entries.map((e) => (
                    <li key={e.id} className="flex items-start justify-between gap-3 py-2.5">
                      <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                          {e.kind === "repair" && <Wrench className="size-3.5 shrink-0 text-primary/70" aria-label="Repair" />}
                          {e.kind === "steps" && <BookOpenCheck className="size-3.5 shrink-0 text-primary/70" aria-label="Steps taught" />}
                          <span className="font-medium">{e.student_name ?? "—"}</span>
                          <span className="text-muted-foreground">→</span>
                          <span className="text-sm">{e.item_title ?? "—"}</span>
                          {e.kind === "steps" && e.step_names ? (
                            <span className="text-xs text-muted-foreground">({e.step_names})</span>
                          ) : null}
                          {e.effect ? (
                            <Badge className={EFFECT_BADGE[e.effect]}>
                              <span className={`mr-1 inline-block size-1.5 rounded-full ${EFFECT_DOT[e.effect]}`} />
                              {EFFECT_LABELS[e.effect]}
                            </Badge>
                          ) : null}
                        </div>
                        {e.note && <p className="mt-1 text-sm text-muted-foreground">{e.note}</p>}
                      </div>
                    </li>
                  ))}
                </ul>
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  )
}
