import { useMemo, useState } from "react"
import { BookOpenCheck, CalendarDays, FilterX, Plus, Trash2, TrendingUp } from "lucide-react"
import { useCurriculum, useStudents } from "@/features/students/api"
import { EFFECT_LABELS, type TeachingEffect, useSaveTeachingLog, useTeachingLog } from "@/features/teachinglog/api"
import { todayStr, useTimeMachine } from "@/lib/timemachine"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Skeleton } from "@/components/ui/skeleton"
import { Textarea } from "@/components/ui/textarea"
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

interface AddEntryDialogProps {
  open: boolean
  onOpenChange: (open: boolean) => void
}

function AddEntryDialog({ open, onOpenChange }: AddEntryDialogProps) {
  const students = useStudents()
  const curriculum = useCurriculum()
  const save = useSaveTeachingLog()
  const { date: workingDate } = useTimeMachine()
  const [form, setForm] = useState({
    log_date: workingDate || todayStr(),
    student_id: "",
    item_id: "",
    effect: "effective" as TeachingEffect,
    note: "",
  })
  const [lastOpen, setLastOpen] = useState(false)
  const [added, setAdded] = useState(0)

  // Reset form each time the dialog opens.
  if (open && !lastOpen) {
    setLastOpen(true)
    setAdded(0)
    setForm({ log_date: workingDate || todayStr(), student_id: "", item_id: "", effect: "effective", note: "" })
  }
  if (!open && lastOpen) setLastOpen(false)

  const studentList = students.data ?? []
  const lessonList = curriculum.data ?? []

  const submit = () => {
    if (form.student_id === "" && form.item_id === "") return
    save.mutate(
      {
        action: "add",
        log_date: form.log_date || workingDate || todayStr(),
        student_id: form.student_id !== "" ? Number(form.student_id) : null,
        item_id: form.item_id !== "" ? Number(form.item_id) : null,
        effect: form.effect,
        note: form.note || undefined,
      },
      {
        onSuccess: () => {
          setAdded((n) => n + 1)
          setForm((f) => ({ ...f, note: "", effect: "effective" }))
        },
      },
    )
  }

  const done = () => {
    setAdded(0)
    onOpenChange(false)
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Log teaching session</DialogTitle>
          <DialogDescription>Record what was taught, to whom, and how it went.</DialogDescription>
        </DialogHeader>

        <div className="grid gap-4 py-2">
          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-1.5">
              <Label htmlFor="tl-date">Date</Label>
              <Input
                id="tl-date"
                type="date"
                value={form.log_date}
                max={todayStr()}
                onChange={(e) => setForm((f) => ({ ...f, log_date: e.target.value }))}
              />
            </div>
            <div className="space-y-1.5">
              <Label>Effect</Label>
              <Select value={form.effect} onValueChange={(v) => setForm((f) => ({ ...f, effect: v as TeachingEffect }))}>
                <SelectTrigger aria-label="Effect">
                  <SelectValue />
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
          </div>

          <div className="space-y-1.5">
            <Label htmlFor="tl-student">Trainee</Label>
            <Select value={form.student_id} onValueChange={(v) => setForm((f) => ({ ...f, student_id: v }))}>
              <SelectTrigger id="tl-student" aria-label="Trainee">
                <SelectValue placeholder="Pick a trainee" />
              </SelectTrigger>
              <SelectContent>
                {studentList.map((s) => (
                  <SelectItem key={s.id} value={String(s.id)}>
                    {s.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="space-y-1.5">
            <Label htmlFor="tl-lesson">Lesson taught</Label>
            <Select value={form.item_id} onValueChange={(v) => setForm((f) => ({ ...f, item_id: v }))}>
              <SelectTrigger id="tl-lesson" aria-label="Lesson taught">
                <SelectValue placeholder="Pick a lesson" />
              </SelectTrigger>
              <SelectContent>
                {lessonList.map((c) => (
                  <SelectItem key={c.id} value={String(c.id)}>
                    {c.title}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="space-y-1.5">
            <Label htmlFor="tl-note">Note (optional)</Label>
            <Textarea
              id="tl-note"
              rows={2}
              placeholder="e.g. understood quickly / needs more practice on this"
              value={form.note}
              onChange={(e) => setForm((f) => ({ ...f, note: e.target.value }))}
            />
          </div>

          {added > 0 && (
            <p className="text-sm text-green-600 dark:text-green-400">
              ✓ {added} entr{added === 1 ? "y" : "ies"} logged — add another or close.
            </p>
          )}
        </div>

        <DialogFooter className="gap-2">
          <Button variant="outline" onClick={done}>
            Done
          </Button>
          <Button onClick={submit} disabled={form.student_id === "" && form.item_id === ""}>
            <Plus className="size-4" /> Log entry
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

export function TeachingLogPage() {
  const students = useStudents()
  const [filters, setFilters] = useState<{ from: string; to: string; student_id: string; effect: string }>({
    from: "",
    to: "",
    student_id: "",
    effect: "",
  })
  const [addOpen, setAddOpen] = useState(false)
  const save = useSaveTeachingLog()
  const [confirmId, setConfirmId] = useState<number | null>(null)

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
    for (const r of rows) counts[r.effect]++
    const effectiveRate = total > 0 ? Math.round((counts.effective / total) * 100) : 0
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
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold">Teaching Log</h1>
          <p className="text-sm text-muted-foreground">Who was taught what, when — and how it went.</p>
        </div>
        <Button onClick={() => setAddOpen(true)}>
          <Plus className="size-4" /> Log session
        </Button>
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
          title="No teaching logs yet"
          description={
            hasFilters
              ? "Nothing matches these filters."
              : "Log your first session — pick a trainee, lesson and effect."
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
                          <span className="font-medium">{e.student_name ?? "—"}</span>
                          <span className="text-muted-foreground">→</span>
                          <span className="text-sm">{e.item_title ?? "—"}</span>
                          <Badge className={EFFECT_BADGE[e.effect]}>
                            <span className={`mr-1 inline-block size-1.5 rounded-full ${EFFECT_DOT[e.effect]}`} />
                            {EFFECT_LABELS[e.effect]}
                          </Badge>
                        </div>
                        {e.note && <p className="mt-1 text-sm text-muted-foreground">{e.note}</p>}
                      </div>
                      <Button
                        variant="ghost"
                        size="icon"
                        className="size-8 shrink-0 text-muted-foreground hover:text-destructive"
                        aria-label={`Delete entry for ${e.student_name ?? "unknown"}`}
                        onClick={() => setConfirmId(e.id)}
                      >
                        <Trash2 className="size-4" />
                      </Button>
                    </li>
                  ))}
                </ul>
              </CardContent>
            </Card>
          ))}
        </div>
      )}

      {/* Delete confirm */}
      <Dialog open={confirmId != null} onOpenChange={(o) => !o && setConfirmId(null)}>
        <DialogContent className="sm:max-w-sm">
          <DialogHeader>
            <DialogTitle>Delete this entry?</DialogTitle>
            <DialogDescription>This removes the teaching log entry. This cannot be undone.</DialogDescription>
          </DialogHeader>
          <DialogFooter className="gap-2">
            <Button variant="outline" onClick={() => setConfirmId(null)}>
              Cancel
            </Button>
            <Button
              variant="destructive"
              onClick={() => {
                if (confirmId != null) save.mutate({ action: "delete", id: confirmId })
                setConfirmId(null)
              }}
            >
              Delete
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <AddEntryDialog open={addOpen} onOpenChange={setAddOpen} />
    </div>
  )
}
