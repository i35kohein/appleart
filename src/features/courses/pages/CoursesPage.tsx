import { useMemo, useState } from "react"
import { BookOpen, Check, ChevronDown, ChevronRight, Clock, History as HistoryIcon, Wrench } from "lucide-react"
import { useQueries } from "@tanstack/react-query"
import { apiFetch } from "@/lib/api"
import { useCurriculum, useInProgress, usePracticalHistory, useSavePracticalHistory, useSaveProgress, useStudentProgress, useStudents } from "@/features/students/api"
import type { CurriculumMaterial } from "@/features/students/types"
import { useTimeMachine } from "@/lib/timemachine"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardTitle } from "@/components/ui/card"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Progress } from "@/components/ui/progress"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Skeleton } from "@/components/ui/skeleton"
import { Tabs, TabsContent, UnderlineTabsList as TabsList, UnderlineTabsTrigger as TabsTrigger } from "@/components/ui/tabs"
import { Textarea } from "@/components/ui/textarea"
import { MaterialControls } from "@/features/courses/components/MaterialControls"
import { StudentAvatar } from "@/components/ui/avatar"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { cn, formatDate, pct } from "@/lib/utils"

type CurriculumType = "Course" | "Practical"

function CategoryGroup({
  category,
  items,
  completed,
  toggling,
  open,
  onOpenChange,
  tone = "course",
  onTagClick,
  completedByItem,
  inProgressByItem,
  onStatusClick,
  onStepClick,
  scopeCount,
  stepDoneByItem,
  stepsByItem,
  stepStudentsByItem,
  lessonDetailsOpen,
  onToggleLessonDetails,
  highlightId,
}: {
  category: string
  items: Array<{ id: number; title: string; tags: string; practice: string | null; details: string | null; sort_order: number; materials?: CurriculumMaterial[] }>
  completed: Set<number>
  toggling: string | null
  open: boolean
  onOpenChange: (open: boolean) => void
  tone?: "course" | "practical"
  onTagClick: (tag: string) => void
  completedByItem?: Map<number, Array<{ id: number; name: string }>>
  inProgressByItem?: Map<number, Array<{ id: number; name: string }>>
  onStatusClick: (itemId: number, status: "In Progress" | "Completed", revert: boolean) => void
  onStepClick: (itemId: number, idx: number, done: boolean) => void
  scopeCount: number
  stepDoneByItem?: Map<number, Set<number>>
  stepsByItem?: Map<number, { total: number; done: number }>
  stepStudentsByItem?: Map<number, Map<number, Array<{ id: number; name: string }>>>
  lessonDetailsOpen: Set<number>
  onToggleLessonDetails: (itemId: number) => void
  highlightId: number | null
}) {
  const done = items.filter((i) => completed.has(i.id)).length
  const dot = tone === "course" ? "bg-brand-blue" : "bg-brand-orange"

  return (
    <Card>
      <div className="cursor-pointer select-none px-4 py-2" onClick={() => onOpenChange(!open)}>
        <div className="flex items-center gap-2.5">
          <ChevronRight className={cn("size-4 shrink-0 text-muted-foreground transition-transform", open && "rotate-90")} />
          <span className={cn("size-2 shrink-0 rounded-full", dot)} />
          <CardTitle className="min-w-0 flex-1 truncate text-sm">{category}</CardTitle>
          <Progress value={pct(done, items.length)} className="h-1 w-28 shrink-0" />
          <span className="shrink-0 text-xs tabular-nums text-muted-foreground">{done}/{items.length}</span>
        </div>
      </div>
      {open && (
        <CardContent className="px-5 pb-4">
          <ul className="space-y-1">
            {items.map((item) => {
              const isDone = completed.has(item.id)
              const doneCount = completedByItem?.get(item.id)?.length ?? 0
              const progCount = inProgressByItem?.get(item.id)?.length ?? 0
              const isAllDone = scopeCount > 0 && doneCount === scopeCount
              const stepSet = stepDoneByItem?.get(item.id)
              const steps = stepsByItem?.get(item.id)
              const hasDetails = !!steps && steps.total > 0
              const stepDone = stepSet ?? new Set<number>()
              const stepDoneCount = steps?.done ?? 0
              const stepTotal = steps?.total ?? 0
              // Students who have completed ALL detail steps (ready for Mark done).
              const stepSets = (stepStudentsByItem?.get(item.id) ?? new Map<number, Array<{ id: number; name: string }>>())
              const readyStudentCount = stepTotal > 0 && stepSets.size > 0
                ? (() => {
                    const lists = [...stepSets.values()]
                    const first = lists[0]
                    if (!first || first.length === 0) return 0
                    const ids = first.map((st) => st.id)
                    return ids.filter((id) => lists.every((arr) => arr.some((st) => st.id === id))).length
                  })()
                : 0
              const anyStudentReady = readyStudentCount > 0
              // In progress = NOT completed AND at least one student in scope is learning it.
              const isAllProg = !isAllDone && progCount > 0
              return (
                <li
                  key={item.id}
                  data-lesson-id={item.id}
                  className={cn(
                    "rounded-lg border bg-muted/20 px-3 py-2 transition-colors",
                    highlightId === item.id && "border-primary/60 bg-primary/5 ring-2 ring-primary/30",
                  )}
                >
                  <div className="flex items-start gap-3">
                    <div className="min-w-0 flex-1">
                      <div className="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <span className={cn("min-w-0 text-sm", isDone ? "font-medium" : "text-muted-foreground")}>
                          {item.title}
                          {isAllProg && (
                            <Clock className="ml-1.5 inline size-3.5 shrink-0 align-[-2px] text-amber-500" aria-label="In progress" />
                          )}
                        </span>
                        {hasDetails && (
                          <button
                            type="button"
                            onClick={() => onToggleLessonDetails(item.id)}
                            aria-label={lessonDetailsOpen.has(item.id) ? "Collapse details" : "Expand details"}
                            aria-expanded={lessonDetailsOpen.has(item.id)}
                            className="shrink-0 cursor-pointer text-muted-foreground transition-colors hover:text-primary"
                          >
                            <ChevronDown className={cn("size-3.5 transition-transform", lessonDetailsOpen.has(item.id) && "rotate-180")} />
                          </button>
                        )}
                        {!hasDetails && completedByItem?.get(item.id)?.length ? (
                          <span className="flex flex-wrap items-center gap-x-2.5 gap-y-0.5">
                            {completedByItem.get(item.id)!.map((s) => (
                              <span key={s.id} className="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                {s.name}
                              </span>
                            ))}
                          </span>
                        ) : null}
                      </div>
                      {(!hasDetails || lessonDetailsOpen.has(item.id)) && item.practice ? (
                        <span className="block truncate text-xs text-muted-foreground/80">{item.practice}</span>
                      ) : null}
                      {lessonDetailsOpen.has(item.id) && item.details ? (
                        <ol className="mt-1 space-y-1">
                          {item.details.split(/\r?\n/).filter(Boolean).map((step, i) => {
                            const idx = i + 1
                            const done = stepDone.has(idx)
                            return (
                              <li key={idx} className="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-xs text-muted-foreground">
                                <button
                                  type="button"
                                  disabled={toggling === `${item.id}:${idx}`}
                                  onClick={() => onStepClick(item.id, idx, !done)}
                                  aria-label={`Mark step ${idx} of ${item.title}`}
                                  className={cn(
                                    "flex size-4 shrink-0 cursor-pointer items-center justify-center rounded-full border transition-colors",
                                    done
                                      ? "border-emerald-500 bg-emerald-500 text-white"
                                      : "border-border text-muted-foreground hover:border-emerald-400",
                                  )}
                                >
                                  {done && <Check className="size-2.5" />}
                                </button>
                                <span className={cn("min-w-0", done ? "font-medium text-foreground" : "text-muted-foreground")}>{step}</span>
                                {stepStudentsByItem?.get(item.id)?.get(idx)?.length ? (
                                  <span className="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    {stepStudentsByItem.get(item.id)!.get(idx)!.map((st) => (
                                      <span key={st.id} className="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        {st.name}
                                      </span>
                                    ))}
                                  </span>
                                ) : null}
                              </li>
                            )
                          })}
                        </ol>
                      ) : null}
                      {(!hasDetails || lessonDetailsOpen.has(item.id)) && item.tags ? (
                        <span className="mt-1 flex flex-wrap gap-x-2.5 gap-y-0.5">
                          {item.tags.split(/\s+/).filter(Boolean).map((t) => (
                            <button
                              key={t}
                              type="button"
                              onClick={() => onTagClick(t)}
                              className="cursor-pointer text-[10px] font-medium leading-none text-muted-foreground/60 transition-colors hover:text-primary"
                              aria-label={`Filter by tag ${t}`}
                            >
                              #{t}
                            </button>
                          ))}
                        </span>
                      ) : null}
                      {inProgressByItem?.get(item.id)?.length ? (
                        <span className="mt-1 flex flex-wrap items-center gap-x-2.5 gap-y-0.5">
                          {inProgressByItem.get(item.id)!.map((s) => (
                            <span key={s.id} className="text-[10px] font-semibold text-amber-600 dark:text-amber-400">
                              {s.name}
                            </span>
                          ))}
                        </span>
                      ) : null}
                    </div>
                    {hasDetails ? (
                      <div className="flex shrink-0 flex-col items-end gap-1">
                        <MaterialControls itemId={item.id} itemTitle={item.title} materials={item.materials} hideUpload />
                        <span className="text-[10px] font-semibold text-muted-foreground">
                          {stepDoneCount}/{stepTotal} steps
                        </span>
                        <Button
                          size="sm"
                          variant={isAllDone ? "default" : "outline"}
                          className={cn(
                            "h-7 w-[104px] shrink-0 text-xs",
                            !isAllDone && anyStudentReady && "border-emerald-500 text-emerald-600",
                            isAllDone && "border-emerald-500 bg-emerald-500 text-white hover:bg-emerald-600",
                          )}
                          disabled={toggling === String(item.id) || (!isAllDone && !anyStudentReady)}
                          title={!isAllDone && !anyStudentReady ? `Mark all ${stepTotal} detail steps first` : undefined}
                          onClick={() => onStatusClick(item.id, "Completed", isAllDone)}
                        >
                          {toggling === String(item.id)
                            ? "…"
                            : !isAllDone && !anyStudentReady
                              ? `Steps ${stepDoneCount}/${stepTotal}`
                              : isAllDone
                                ? "Mark pending"
                                : "Mark done"}
                        </Button>
                      </div>
                    ) : (
                      <div className="flex shrink-0 items-center gap-1">
                        <MaterialControls itemId={item.id} itemTitle={item.title} materials={item.materials} hideUpload />
                        <Button
                          size="sm"
                          variant={isAllProg ? "default" : "outline"}
                          className={cn("h-7 w-24 shrink-0 text-xs", isAllProg && "border-amber-500 bg-amber-500 text-white hover:bg-amber-600")}
                          disabled={toggling === String(item.id)}
                          onClick={() => onStatusClick(item.id, "In Progress", isAllProg)}
                        >
                          {toggling === String(item.id) ? "…" : "In progress"}
                        </Button>
                        <Button
                          size="sm"
                          variant={isAllDone ? "default" : "outline"}
                          className={cn("h-7 w-[104px] shrink-0 text-xs", isAllDone && "border-emerald-500 bg-emerald-500 text-white hover:bg-emerald-600")}
                          disabled={toggling === String(item.id)}
                          onClick={() => onStatusClick(item.id, "Completed", isAllDone)}
                        >
                          {toggling === String(item.id) ? "…" : isAllDone ? "Mark pending" : "Mark done"}
                        </Button>
                      </div>
                    )}
                  </div>
                </li>
              )
            })}
          </ul>
        </CardContent>
      )}
    </Card>
  )
}

export function CoursesPage() {
  const students = useStudents()
  const curriculum = useCurriculum()
  const save = useSaveProgress()
  const { date: tmDate } = useTimeMachine()
  const [studentId, setStudentId] = useState<string>("")
  const [tab, setTab] = useState<CurriculumType | "history">("Course")
  const [toggling, setToggling] = useState<string | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [tagFilter, setTagFilter] = useState<string | null>(null)
  const [categoryTab, setCategoryTab] = useState<string>("")
  // Per-lesson detail steps collapse (default collapsed) — only lessons WITH details get it.
  const [lessonDetailsOpen, setLessonDetailsOpen] = useState<Set<number>>(new Set())
  const [highlightId, setHighlightId] = useState<number | null>(null)
  const [statusModal, setStatusModal] = useState<{ itemId: number; status: "In Progress" | "Completed"; itemTitle: string; before: Set<number> } | null>(null)
  const [statusChecked, setStatusChecked] = useState<Set<number>>(new Set())
  const jumpToLesson = (itemId: number) => {
    const item = items.find((i) => i.id === itemId)
    if (!item) return
    setTab(item.type as CurriculumType)
    setCategoryTab(item.category)
    setTagFilter(null)
    setLessonDetailsOpen((prev) => {
      const n = new Set(prev)
      n.add(itemId)
      return n
    })
    setHighlightId(itemId)
    window.setTimeout(() => {
      document.querySelector(`[data-lesson-id="${itemId}"]`)?.scrollIntoView({ behavior: "smooth", block: "center" })
    }, 200)
    window.setTimeout(() => setHighlightId(null), 2600)
  }

  const toggleLessonDetails = (itemId: number) => {
    setLessonDetailsOpen((prev) => {
      const n = new Set(prev)
      if (n.has(itemId)) n.delete(itemId)
      else n.add(itemId)
      return n
    })
  }
  const [stepModal, setStepModal] = useState<{ itemId: number; idx: number; itemTitle: string; before: Set<number> } | null>(null)
  const [stepChecked, setStepChecked] = useState<Set<number>>(new Set())
  // category -> open state (controlled so Expand/Collapse All can drive every group)
  const [openMap, setOpenMap] = useState<Record<string, boolean>>({})

  const allOpen = Object.keys(openMap).length > 0 && Object.values(openMap).every(Boolean)

  const setAllOpen = (open: boolean) => {
    setOpenMap(Object.fromEntries(categories.map(([category]) => [category, open])))
  }

  const activeStudents = (students.data ?? []).filter((s) => s.is_active === 1)
  // "" = auto (All students), "all" = all active students, otherwise a student id.
  const selection: number | "all" | null =
    studentId === "all" ? "all" : studentId !== "" ? Number(studentId) : "all"
  const selectedId = selection === "all" ? null : selection
  const { data: progress, isLoading: progressLoading } = useStudentProgress(selectedId)
  const { data: inProgressRows } = useInProgress(selection === "all" ? null : selectedId)
  const { data: historyRows } = usePracticalHistory(null, selection === "all" ? null : selectedId)
  const saveHistory = useSavePracticalHistory()
  const [historyOpen, setHistoryOpen] = useState(false)
  const [histStudents, setHistStudents] = useState<Set<number>>(new Set())
  const toggleHistStudent = (studentId: number) => {
    setHistStudents((prev) => {
      const n = new Set(prev)
      if (n.has(studentId)) n.delete(studentId)
      else n.add(studentId)
      return n
    })
  }
  const [histTitle, setHistTitle] = useState("")
  const [histDate, setHistDate] = useState(tmDate)
  const [histNote, setHistNote] = useState("")

  // When "All students" is selected, load every active student's progress; a lesson counts
  // as done only when ALL of them have completed it.
  const allProgress = useQueries({
    queries:
      selection === "all"
        ? activeStudents.map((s) => ({
            queryKey: ["student-progress", s.id] as const,
            queryFn: () =>
              apiFetch<{ data: Array<{ item_id: number; detail_idx: number | null; completion_date: string; trainer_name: string }> }>(
                `/get_student_progress.php?student_id=${s.id}`,
              ).then((r) => r.data),
          }))
        : [],
  })
  const allLoaded = allProgress.length === 0 || allProgress.every((q) => q.isSuccess)

  // In "All students" mode: item_id -> students who completed it (shown on lesson rows).
  const completedByItem = useMemo(() => {
    const map = new Map<number, Array<{ id: number; name: string }>>()
    if (selection !== "all") {
      // Single-student mode: chips + done counts come from that student's own progress.
      const student = activeStudents.find((s) => s.id === selectedId)
      if (student) {
        for (const p of progress ?? []) {
          if (p.detail_idx != null) continue
          const arr = map.get(p.item_id) ?? []
          arr.push({ id: student.id, name: student.name })
          map.set(p.item_id, arr)
        }
      }
      return map
    }
    activeStudents.forEach((s, i) => {
      const q = allProgress[i]
      for (const p of (q?.data ?? []) as Array<{ item_id: number; detail_idx: number | null }>) {
        if (p.detail_idx != null) continue // step rows are not lesson completion
        const arr = map.get(p.item_id) ?? []
        arr.push({ id: s.id, name: s.name })
        map.set(p.item_id, arr)
      }
    })
    return map
  }, [selection, activeStudents, allProgress])

  // item_id -> students currently marked "In Progress" on that lesson (both modes).
  const inProgressByItem = useMemo(() => {
    const map = new Map<number, Array<{ id: number; name: string }>>()
    const activeIds = new Set(activeStudents.map((s) => s.id))
    for (const r of inProgressRows ?? []) {
      if (selection !== "all" && r.student_id !== selectedId) continue
      if (selection === "all" && !activeIds.has(r.student_id)) continue
      const arr = map.get(r.item_id) ?? []
      arr.push({ id: r.student_id, name: r.student_name })
      map.set(r.item_id, arr)
    }
    return map
  }, [inProgressRows, selection, selectedId, activeStudents])

  // Summary of who is currently being taught what (across both tabs).
  const inProgressSummary = useMemo(() => {
    const rows: Array<{ item_id: number; item_title: string; students: Array<{ id: number; name: string }> }> = []
    const byItem = new Map<number, { item_title: string; students: Array<{ id: number; name: string }> }>()
    const activeIds = new Set(activeStudents.map((s) => s.id))
    for (const r of inProgressRows ?? []) {
      if (selection !== "all" && r.student_id !== selectedId) continue
      if (selection === "all" && !activeIds.has(r.student_id)) continue
      const entry = byItem.get(r.item_id) ?? { item_title: r.item_title, students: [] }
      if (!entry.students.some((x) => x.id === r.student_id)) entry.students.push({ id: r.student_id, name: r.student_name })
      byItem.set(r.item_id, entry)
    }
    for (const [item_id, e] of byItem) rows.push({ item_id, item_title: e.item_title, students: e.students })
    return rows
  }, [inProgressRows, selection, selectedId, activeStudents])

  const lessonDone = (rows: Array<{ item_id: number; detail_idx: number | null }>) =>
    rows.filter((p) => p.detail_idx == null).map((p) => p.item_id)

  const completed = useMemo(() => {
    if (selection !== "all") return new Set(lessonDone(progress ?? []))
    if (!allLoaded) return new Set<number>()
    const perStudent = allProgress.map((q) => new Set(lessonDone((q.data ?? []) as Array<{ item_id: number; detail_idx: number | null }>)))
    if (perStudent.length === 0) return new Set<number>()
    return new Set([...perStudent[0]].filter((id) => perStudent.every((s) => s.has(id))))
  }, [selection, progress, allProgress, allLoaded])

  const items = curriculum.data ?? []

  // Detail steps: item_id -> step indexes completed by EVERY student in scope.
  const stepDoneByItem = useMemo(() => {
    const map = new Map<number, Set<number>>()
    if (selection === "all") {
      if (!allLoaded) return map
      const perStudent = allProgress.map((q) => {
        const m = new Map<number, number[]>()
        for (const p of (q.data ?? []) as Array<{ item_id: number; detail_idx: number | null }>) {
          if (p.detail_idx == null) continue
          const arr = m.get(p.item_id) ?? []
          arr.push(p.detail_idx)
          m.set(p.item_id, arr)
        }
        return m
      })
      for (const item of items) {
        const total = (item.details ?? "").split(/\r?\n/).filter(Boolean).length
        if (total === 0) continue
        const set = new Set<number>()
        for (let idx = 1; idx <= total; idx++) {
          if (perStudent.length > 0 && perStudent.every((m) => (m.get(item.id) ?? []).includes(idx))) set.add(idx)
        }
        map.set(item.id, set)
      }
    } else {
      for (const p of progress ?? []) {
        if (p.detail_idx == null) continue
        const set = map.get(p.item_id) ?? new Set<number>()
        set.add(p.detail_idx)
        map.set(p.item_id, set)
      }
    }
    return map
  }, [selection, allLoaded, allProgress, progress, items])

  // Detail steps summary per item: { total, done }.
  const stepsByItem = useMemo(() => {
    const map = new Map<number, { total: number; done: number }>()
    for (const item of items) {
      const total = (item.details ?? "").split(/\r?\n/).filter(Boolean).length
      if (total === 0) continue
      map.set(item.id, { total, done: stepDoneByItem.get(item.id)?.size ?? 0 })
    }
    return map
  }, [items, stepDoneByItem])

  const allHistorySorted = useMemo(() => [...(historyRows ?? [])].sort((a, b) => (a.repair_date < b.repair_date ? 1 : -1)), [historyRows])

  // Group by repair session (title + note + date) — show affected students beside it like the course list.
  const groupedHistory = useMemo(() => {
    const map = new Map<string, { key: string; item_title: string; repair_date: string; note: string; trainer: string | null; students: Array<{ id: number; name: string }>; ids: number[] }>()
    for (const h of allHistorySorted) {
      const key = `${h.item_title}|${h.note}|${h.repair_date}|${h.source ?? ""}`
      const g = map.get(key) ?? { key, item_title: h.item_title, repair_date: h.repair_date, note: h.note, trainer: h.trainer_name ?? null, students: [], ids: [] }
      if (h.student_name && !g.students.some((x) => x.id === h.student_id)) g.students.push({ id: h.student_id, name: h.student_name })
      g.ids.push(h.id)
      map.set(key, g)
    }
    return [...map.values()]
  }, [allHistorySorted])

  const deleteHistoryGroup = (ids: number[]) => {
    if (!window.confirm("Delete this repair history?")) return
    void Promise.allSettled(ids.map((id) => saveHistory.mutateAsync({ action: "delete", id })))
  }

  const openHistoryModal = () => {
    setHistStudents(new Set())
    setHistTitle("")
    setHistDate(tmDate)
    setHistNote("")
    setHistoryOpen(true)
  }

  const submitHistory = () => {
    const title = histTitle.trim()
    const note = histNote.trim()
    if (!title && !note) return
    const jobs =
      histStudents.size === 0
        ? [saveHistory.mutateAsync({ action: "add", student_id: 0, item_id: 0, repair_date: histDate, title, note })]
        : [...histStudents].map((sid) =>
            saveHistory.mutateAsync({ action: "add", student_id: sid, item_id: 0, repair_date: histDate, title, note }),
          )
    void Promise.allSettled(jobs).then(() => setHistoryOpen(false))
  }


  // item_id -> step_idx -> students who marked THAT step (shown inline next to each step).
  const stepStudentsByItem = useMemo(() => {
    const map = new Map<number, Map<number, Array<{ id: number; name: string }>>>()
    if (selection === "all") {
      activeStudents.forEach((s, i) => {
        const q = allProgress[i]
        for (const p of (q?.data ?? []) as Array<{ item_id: number; detail_idx: number | null }>) {
          if (p.detail_idx == null) continue
          const m = map.get(p.item_id) ?? new Map<number, Array<{ id: number; name: string }>>()
          const arr = m.get(p.detail_idx) ?? []
          arr.push({ id: s.id, name: s.name })
          m.set(p.detail_idx, arr)
          map.set(p.item_id, m)
        }
      })
    } else {
      const student = activeStudents.find((s) => s.id === selectedId)
      if (student) {
        for (const p of progress ?? []) {
          if (p.detail_idx == null) continue
          const m = map.get(p.item_id) ?? new Map<number, Array<{ id: number; name: string }>>()
          const arr = m.get(p.detail_idx) ?? []
          arr.push({ id: student.id, name: student.name })
          m.set(p.detail_idx, arr)
          map.set(p.item_id, m)
        }
      }
    }
    return map
  }, [selection, allProgress, progress, activeStudents, selectedId])

  const byType = (type: CurriculumType) =>
    items.filter((i) => i.type === type).sort((a, b) => a.sort_order - b.sort_order)

  // Course/practical tabs only — the Repair History tab has its own view.
  const lessonTab = tab === "history" ? "Course" : tab

  // All unique tags across the current tab's lessons (for filter chips).
  const allTags = useMemo(() => {
    const s = new Set<string>()
    for (const item of byType(lessonTab)) {
      for (const t of (item.tags ?? "").split(/\s+/).filter(Boolean)) s.add(t)
    }
    return [...s].sort()
  }, [items, lessonTab])

  const filteredItems = useMemo(() => {
    const list = byType(lessonTab)
    if (!tagFilter) return list
    return list.filter((i) => (i.tags ?? "").split(/\s+/).includes(tagFilter))
  }, [items, lessonTab, tagFilter])

  const categories = useMemo(() => {
    const map = new Map<string, Array<{ id: number; title: string; tags: string; practice: string | null; details: string | null; sort_order: number; materials?: CurriculumMaterial[] }>>()
    for (const item of filteredItems) {
      if (!map.has(item.category)) map.set(item.category, [])
      map.get(item.category)!.push(item)
    }
    return [...map.entries()]
  }, [filteredItems])

  const total = filteredItems.length
  const doneTotal = filteredItems.filter((i) => completed.has(i.id)).length

  // Sub-tab: one category at a time instead of stacking all (less scrolling).
  const activeCategory = categories.some(([c]) => c === categoryTab)
    ? categoryTab
    : categories[0]?.[0] ?? null

  const applyStepToStudents = async (itemId: number, idx: number, studentIds: number[], done: boolean) => {
    if (studentIds.length === 0) return
    setError(null)
    setToggling(`${itemId}:${idx}`)
    const results = await Promise.allSettled(
      studentIds.map((sid) =>
        save.mutateAsync({
          student_id: sid,
          item_id: itemId,
          status: done ? "Completed" : "Pending",
          detail_idx: idx,
          trainer_name: "Instructor",
          completion_date: tmDate,
        }),
      ),
    )
    const failed = results.find((r) => r.status === "rejected")
    if (failed) setError((failed as PromiseRejectedResult).reason?.message ?? "Failed to update progress")
    setToggling(null)
  }

  // Step clicked: All-students mode opens the Active Student picker; otherwise toggle directly.
  const handleStepClick = (itemId: number, idx: number, done: boolean) => {
    if (selection == null) return
    if (selection === "all" && activeStudents.length > 1) {
      const before = new Set<number>()
      activeStudents.forEach((s, i) => {
        const q = allProgress[i]
        const has = (q?.data ?? []).some(
          (p) => (p as { item_id: number; detail_idx: number | null }).item_id === itemId && (p as { detail_idx: number | null }).detail_idx === idx,
        )
        if (has) before.add(s.id)
      })
      const itemTitle = items.find((i) => i.id === itemId)?.title ?? ""
      setStepChecked(before)
      setStepModal({ itemId, idx, itemTitle, before })
      return
    }
    const target = selection === "all" ? activeStudents[0]?.id : selection
    if (target == null) return
    void applyStepToStudents(itemId, idx, [target], done)
  }

  const applyStepModal = async () => {
    if (!stepModal) return
    const { itemId, idx, before } = stepModal
    const markIds = activeStudents.filter((s) => stepChecked.has(s.id)).map((s) => s.id)
    const unmarkIds = activeStudents.filter((s) => !stepChecked.has(s.id) && before.has(s.id)).map((s) => s.id)
    setStepModal(null)
    const jobs = [
      ...markIds.map((sid) =>
        save.mutateAsync({
          student_id: sid,
          item_id: itemId,
          status: "Completed",
          detail_idx: idx,
          trainer_name: "Instructor",
          completion_date: tmDate,
        }),
      ),
      ...unmarkIds.map((sid) =>
        save.mutateAsync({
          student_id: sid,
          item_id: itemId,
          status: "Pending",
          detail_idx: idx,
          trainer_name: "Instructor",
          completion_date: tmDate,
        }),
      ),
    ]
    if (jobs.length === 0) return
    setToggling(`${itemId}:${idx}`)
    const results = await Promise.allSettled(jobs)
    const failed = results.find((r) => r.status === "rejected")
    if (failed) setError((failed as PromiseRejectedResult).reason?.message ?? "Failed to update progress")
    setToggling(null)
  }

  const toggleStepChecked = (studentId: number) => {
    setStepChecked((prev) => {
      const next = new Set(prev)
      if (next.has(studentId)) next.delete(studentId)
      else next.add(studentId)
      return next
    })
  }

  const applyStatusToStudents = async (itemId: number, status: "Pending" | "In Progress" | "Completed", studentIds: number[]) => {
    if (studentIds.length === 0) return
    setError(null)
    setToggling(String(itemId))
    const results = await Promise.allSettled(
      studentIds.map((sid) =>
        save.mutateAsync({
          student_id: sid,
          item_id: itemId,
          status,
          trainer_name: "Instructor",
          completion_date: tmDate,
        }),
      ),
    )
    const failed = results.find((r) => r.status === "rejected")
    if (failed) setError((failed as PromiseRejectedResult).reason?.message ?? "Failed to update progress")
    setToggling(null)
  }

  // In-progress / Mark done clicked: revert clears everyone; otherwise All-students mode opens the student picker modal.
  const handleStatusClick = (itemId: number, status: "In Progress" | "Completed", revert: boolean) => {
    if (selection == null) return
    if (revert) {
      const targets = selection === "all" ? activeStudents.map((s) => s.id) : [selection]
      if (targets.length > 0) void applyStatusToStudents(itemId, "Pending", targets)
      return
    }
    if (selection === "all" && activeStudents.length > 1) {
      const before = new Set<number>()
      const list = status === "Completed" ? completedByItem.get(itemId) : inProgressByItem.get(itemId)
      for (const s of list ?? []) before.add(s.id)
      const itemTitle = items.find((i) => i.id === itemId)?.title ?? ""
      setStatusChecked(before)
      setStatusModal({ itemId, status, itemTitle, before })
      return
    }
    const target = selection === "all" ? activeStudents[0]?.id : selection
    if (target == null) return
    void applyStatusToStudents(itemId, status, [target])
  }

  const applyStatusModal = async () => {
    if (!statusModal) return
    const { itemId, status, before } = statusModal
    const markIds = activeStudents.filter((s) => statusChecked.has(s.id)).map((s) => s.id)
    const unmarkIds = activeStudents.filter((s) => !statusChecked.has(s.id) && before.has(s.id)).map((s) => s.id)
    setStatusModal(null)
    const jobs = [
      ...markIds.map((sid) =>
        save.mutateAsync({
          student_id: sid,
          item_id: itemId,
          status,
          trainer_name: "Instructor",
          completion_date: tmDate,
        }),
      ),
      ...unmarkIds.map((sid) =>
        save.mutateAsync({
          student_id: sid,
          item_id: itemId,
          status: "Pending",
          trainer_name: "Instructor",
          completion_date: tmDate,
        }),
      ),
    ]
    if (jobs.length === 0) return
    setToggling(String(itemId))
    const results = await Promise.allSettled(jobs)
    const failed = results.find((r) => r.status === "rejected")
    if (failed) setError((failed as PromiseRejectedResult).reason?.message ?? "Failed to update progress")
    setToggling(null)
  }

  const toggleStatusChecked = (studentId: number) => {
    setStatusChecked((prev) => {
      const n = new Set(prev)
      if (n.has(studentId)) n.delete(studentId)
      else n.add(studentId)
      return n
    })
  }

  return (
    <div className="space-y-6">
      {error && (
        <Alert variant="destructive">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <Tabs
        value={tab}
        onValueChange={(v) => {
          setTab(v as CurriculumType)
          setCategoryTab("")
          setTagFilter(null)
        }}
      >
        <div className="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
          <div className="flex flex-wrap items-center gap-3">
            <TabsList className="gap-2">
              <TabsTrigger value="Course" className="gap-1.5"><BookOpen className="size-3.5" /> Courses</TabsTrigger>
              <TabsTrigger value="Practical" className="gap-1.5"><Wrench className="size-3.5" /> Practical</TabsTrigger>
              <TabsTrigger value="history" className="gap-1.5"><HistoryIcon className="size-3.5" /> Repair History</TabsTrigger>
            </TabsList>
            <Button
              variant="ghost"
              size="icon"
              onClick={() => setAllOpen(!allOpen)}
              aria-label={allOpen ? "Collapse all" : "Expand all"}
              title={allOpen ? "Collapse all" : "Expand all"}
            >
              <ChevronRight className={cn("size-4 transition-transform", allOpen && "rotate-90")} />
            </Button>
            {activeStudents.length > 0 && (
              <Select value={selection === null ? "" : String(selection)} onValueChange={setStudentId}>
                <SelectTrigger className="w-44">
                  <SelectValue placeholder="Select trainee" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All students</SelectItem>
                  {activeStudents.map((s) => (
                    <SelectItem key={s.id} value={String(s.id)}>
                      {s.name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            )}
          </div>
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <span className="tabular-nums">{doneTotal}/{total}</span>
            <Progress value={pct(doneTotal, total)} className="h-1.5 w-28" />
          </div>
        </div>

        {tab === "history" ? (
          <div className="mt-4 space-y-2">
            <div className="flex items-center justify-between">
              <p className="text-sm text-muted-foreground">All repair records — ဘယ်သူ့ကို ဘယ်နေ့ ဘာပြင်ထားလဲ</p>
              <Button size="sm" variant="outline" className="h-8 text-xs" onClick={openHistoryModal}>
                + Add entry
              </Button>
            </div>
            {allHistorySorted.length === 0 ? (
              <p className="py-10 text-center text-sm text-muted-foreground">No repair records yet.</p>
            ) : (
              groupedHistory.map((g) => (
                <div key={g.key} className="rounded-lg border bg-muted/20 px-3 py-2">
                  <div className="flex items-start gap-3">
                    <div className="min-w-0 flex-1">
                      <div className="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <span className="min-w-0 text-sm font-medium">{g.item_title || g.note}</span>
                        {g.students.map((s) => (
                          <span key={s.id} className="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                            {s.name}
                          </span>
                        ))}
                      </div>
                      <span className="block truncate text-xs text-muted-foreground/80">
                        {formatDate(g.repair_date)}
                        {g.trainer ? ` · by ${g.trainer}` : ""}
                      </span>
                      {g.item_title && g.note ? (
                        <span className="mt-1 block text-xs text-muted-foreground/80">{g.note}</span>
                      ) : null}
                    </div>
                    <button
                      type="button"
                      onClick={() => deleteHistoryGroup(g.ids)}
                      aria-label={`Delete history ${g.key}`}
                      className="shrink-0 cursor-pointer text-muted-foreground transition-colors hover:text-destructive"
                    >
                      ×
                    </button>
                  </div>
                </div>
              ))
            )}
          </div>
        ) : (
        <>
        {/* Tag filter chips */}
        {allTags.length > 0 && (
          <div className="mt-3 flex flex-wrap items-center gap-1.5">
            <button
              type="button"
              onClick={() => setTagFilter(null)}
              className={cn(
                "cursor-pointer rounded-full border px-2.5 py-1 text-xs font-medium transition-colors",
                !tagFilter
                  ? "border-primary bg-primary text-white"
                  : "border-border text-muted-foreground hover:bg-accent",
              )}
            >
              All
            </button>
            {allTags.map((t) => (
              <button
                key={t}
                type="button"
                onClick={() => setTagFilter(tagFilter === t ? null : t)}
                className={cn(
                  "cursor-pointer rounded-full border px-2.5 py-1 text-xs font-medium transition-colors",
                  tagFilter === t
                    ? "border-primary bg-primary text-white"
                    : "border-border text-muted-foreground hover:bg-accent",
                )}
              >
                #{t}
              </button>
            ))}
          </div>
        )}

        {/* Training in progress — who is learning what right now */}
        {inProgressSummary.length > 0 && (
          <div className="mt-3 rounded-xl border border-amber-300/60 bg-amber-50 px-3 py-2.5 dark:border-amber-500/30 dark:bg-amber-500/10">
            <p className="flex items-center gap-1.5 text-xs font-bold text-amber-700 dark:text-amber-400">
              <Clock className="size-3.5" /> Training in progress
            </p>
            <ul className="mt-1.5 space-y-1">
              {inProgressSummary.map((r) => (
                <li key={r.item_id}>
                  <button
                    type="button"
                    onClick={() => jumpToLesson(r.item_id)}
                    className="flex w-full cursor-pointer flex-wrap items-center gap-x-2 gap-y-0.5 rounded-md px-1 py-0.5 text-left text-xs transition-colors hover:bg-amber-100/70 dark:hover:bg-amber-500/15"
                  >
                    <span className="font-medium text-foreground underline-offset-2 hover:underline">{r.item_title}</span>
                    <span className="text-muted-foreground">—</span>
                    <span className="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                      {r.students.map((s) => (
                        <span key={s.id} className="font-semibold text-amber-700 dark:text-amber-400">
                          {s.name}
                        </span>
                      ))}
                    </span>
                  </button>
                </li>
              ))}
            </ul>
          </div>
        )}

        {curriculum.isLoading || progressLoading ? (
          <div className="mt-4 space-y-3">
            {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-24 w-full" />)}
          </div>
        ) : tagFilter && categories.length === 0 ? (
          <p className="py-10 text-center text-sm text-muted-foreground">No lessons match #{tagFilter}.</p>
        ) : categories.length === 0 ? (
          <p className="py-10 text-center text-sm text-muted-foreground">No {tab.toLowerCase()} modules in the curriculum yet.</p>
        ) : (
          <>
            {/* Category sub-tabs — underline style, distinct from #tag pills */}
            {categories.length > 1 && (
              <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 border-b border-border">
                {categories.map(([category]) => (
                  <button
                    key={category}
                    type="button"
                    onClick={() => setCategoryTab(category)}
                    className={cn(
                      "-mb-px cursor-pointer border-b-2 px-1 pb-1.5 pt-0.5 text-sm transition-colors",
                      activeCategory === category
                        ? "border-primary font-semibold text-primary"
                        : "border-transparent text-muted-foreground hover:text-foreground",
                    )}
                  >
                    {category}
                  </button>
                ))}
              </div>
            )}
            {(["Course", "Practical"] as const).map((t) => {
              const entry = categories.find(([c]) => c === activeCategory)
              if (!entry) return null
              const [category, catItems] = entry
              return (
                <TabsContent key={t} value={t} className="mt-4">
                  <CategoryGroup
                    category={category}
                    items={catItems}
                    completed={completed}
                    toggling={toggling}
                    open={openMap[category] ?? true}
                    onOpenChange={(o) => setOpenMap((m) => ({ ...m, [category]: o }))}
                    tone={t === "Course" ? "course" : "practical"}
                    onTagClick={(tag) => setTagFilter(tag)}
                    completedByItem={completedByItem}
                    inProgressByItem={inProgressByItem}
                    onStatusClick={handleStatusClick}
                    onStepClick={handleStepClick}
                    scopeCount={selection === "all" ? activeStudents.length : 1}
                    stepDoneByItem={stepDoneByItem}
                    stepsByItem={stepsByItem}
                    stepStudentsByItem={stepStudentsByItem}
                    lessonDetailsOpen={lessonDetailsOpen}
                    onToggleLessonDetails={toggleLessonDetails}
                    highlightId={highlightId}
                  />
                </TabsContent>
              )
            })}
          </>
        )}
        </>
        )}
      </Tabs>

      {students.data && students.data.length === 0 && (
        <p className="py-10 text-center text-sm text-muted-foreground">Add trainees first to start marking progress.</p>
      )}

      {/* Student picker for In progress / Mark done (All students mode) */}
      <Dialog open={statusModal != null} onOpenChange={(o) => !o && setStatusModal(null)}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>
              {statusModal?.status === "Completed" ? "Mark done" : "In progress"} — {statusModal?.itemTitle}
            </DialogTitle>
            <DialogDescription>ဘယ် active trainee တွေကို ဒီ status ပေးမလဲ ရွေးပါ။</DialogDescription>
          </DialogHeader>
          <div className="max-h-72 space-y-1 overflow-y-auto pr-1">
            {activeStudents.map((s) => {
              const checked = statusChecked.has(s.id)
              return (
                <button
                  key={s.id}
                  type="button"
                  onClick={() => toggleStatusChecked(s.id)}
                  className={cn(
                    "flex w-full cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2 text-left text-sm transition-colors",
                    checked ? "border-emerald-400 bg-emerald-50 dark:bg-emerald-500/10" : "border-border hover:bg-accent",
                  )}
                >
                  <StudentAvatar name={s.name} className="size-6" />
                  <span className="min-w-0 flex-1">
                    <span className="block truncate font-medium">{s.name}</span>
                    {s.address ? <span className="block truncate text-xs text-muted-foreground">{s.address}</span> : null}
                  </span>
                </button>
              )
            })}
          </div>
          <DialogFooter className="gap-2">
            <Button variant="outline" onClick={() => setStatusModal(null)}>Cancel</Button>
            <Button onClick={() => void applyStatusModal()} disabled={statusChecked.size === 0}>
              Save ({statusChecked.size})
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Add practical repair history — free-form: just write and save */}
      <Dialog open={historyOpen} onOpenChange={(o) => !o && setHistoryOpen(false)}>
        <DialogContent className="sm:max-w-sm">
          <DialogHeader>
            <DialogTitle>Add repair history</DialogTitle>
            <DialogDescription>ဘာပြင်လိုက်လဲ ရေးပြီး Save လုပ်ပါ — lesson နဲ့ မဆိုင်ဘူး။</DialogDescription>
          </DialogHeader>
          <div className="space-y-3">
            <div className="space-y-1.5">
              <Label htmlFor="hist-title">Header</Label>
              <Input
                id="hist-title"
                value={histTitle}
                onChange={(e) => setHistTitle(e.target.value)}
                placeholder="e.g. Battery လဲခြင်း"
              />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="hist-note">Paragraph</Label>
              <Textarea
                id="hist-note"
                value={histNote}
                onChange={(e) => setHistNote(e.target.value)}
                placeholder="အသေးစိတ် — e.g. BMS ပြဿနာဖြစ်လို့ လဲလိုက်တယ်"
                rows={3}
              />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="hist-date">Date</Label>
              <Input id="hist-date" type="date" value={histDate} onChange={(e) => setHistDate(e.target.value)} />
            </div>
            <div className="space-y-1.5">
              <Label>Students (optional — မရွေးလည်း ရတယ်)</Label>
              <div className="max-h-56 space-y-1 overflow-y-auto pr-1">
                {activeStudents.map((s) => {
                  const checked = histStudents.has(s.id)
                  return (
                    <button
                      key={s.id}
                      type="button"
                      onClick={() => toggleHistStudent(s.id)}
                      className={cn(
                        "flex w-full cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2 text-left text-sm transition-colors",
                        checked ? "border-emerald-400 bg-emerald-50 dark:bg-emerald-500/10" : "border-border hover:bg-accent",
                      )}
                    >
                      <StudentAvatar name={s.name} className="size-6" />
                      <span className="min-w-0 flex-1 truncate font-medium">{s.name}</span>
                    </button>
                  )
                })}
              </div>
            </div>
          </div>
          <DialogFooter className="gap-2">
            <Button variant="outline" onClick={() => setHistoryOpen(false)}>Cancel</Button>
            <Button onClick={submitHistory} disabled={(!histTitle.trim() && !histNote.trim()) || saveHistory.isPending}>
              Save
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Active Student picker for detail steps (All students mode) */}
      <Dialog open={stepModal != null} onOpenChange={(o) => !o && setStepModal(null)}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Step {stepModal?.idx} — {stepModal?.itemTitle}</DialogTitle>
            <DialogDescription>ဘယ် active trainee တွေကို ဒီ step အတွက် mark မလဲ ရွေးပါ။</DialogDescription>
          </DialogHeader>
          <div className="max-h-72 space-y-1 overflow-y-auto pr-1">
            {activeStudents.map((s) => {
              const checked = stepChecked.has(s.id)
              return (
                <button
                  key={s.id}
                  type="button"
                  onClick={() => toggleStepChecked(s.id)}
                  className={cn(
                    "flex w-full cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2 text-left text-sm transition-colors",
                    checked ? "border-emerald-400 bg-emerald-50 dark:bg-emerald-500/10" : "border-border hover:bg-accent",
                  )}
                >
                  <StudentAvatar name={s.name} className="size-6" />
                  <span className="min-w-0 flex-1 truncate font-medium">{s.name}</span>
                </button>
              )
            })}
          </div>
          <DialogFooter className="gap-2">
            <Button variant="outline" onClick={() => setStepModal(null)}>Cancel</Button>
            <Button onClick={() => void applyStepModal()} disabled={stepChecked.size === 0}>
              Mark step ({stepChecked.size})
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}
