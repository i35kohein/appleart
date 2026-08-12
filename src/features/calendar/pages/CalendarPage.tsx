import { useMemo, useState } from "react"
import { Link } from "react-router-dom"
import { CalendarDays, CalendarPlus, ChevronLeft, ChevronRight, School, Trash2, Wrench } from "lucide-react"
import { useCalendar, useSaveCalendarProgress, useSaveTeacherSchedule, useDeleteTeacherSchedule, type CalendarDayEntry } from "@/features/calendar/api"
import { useStudents } from "@/features/students/api"
import { useTimeMachine } from "@/lib/timemachine"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Skeleton } from "@/components/ui/skeleton"
import { cn } from "@/lib/utils"

const MONTHS = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December",
]
const WEEKDAYS = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]

function todayStr() {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`
}

function LessonChip({
  label,
  item,
  studentId,
  onToggle,
  busy,
}: {
  label: "Course" | "Practical"
  item: { id: number; title: string; category: string; done: boolean } | null
  studentId: number
  onToggle: (studentId: number, itemId: number, done: boolean) => void
  busy: boolean
}) {
  if (!item) {
    return (
      <div className="flex items-center gap-1 rounded-md px-1.5 py-1 text-[11px] text-muted-foreground/70">
        {label === "Course" ? <School className="size-3" /> : <Wrench className="size-3" />}
        {label} complete 🎓
      </div>
    )
  }
  const color = label === "Course" ? "text-brand-blue dark:text-brand-blue" : "text-brand-orange dark:text-brand-orange"
  return (
    <button
      type="button"
      disabled={busy}
      onClick={(e) => {
        e.stopPropagation()
        onToggle(studentId, item.id, !item.done)
      }}
      title={item.done ? "Click to mark as not done" : "Click to mark as done"}
      className={cn(
        "flex w-full items-start gap-1.5 rounded-md px-1.5 py-1 text-left text-[11px] transition-colors hover:bg-accent disabled:opacity-60",
        item.done ? "opacity-80" : "",
      )}
    >
      <span className={cn("mt-px shrink-0", item.done ? "text-primary" : "text-muted-foreground")}>
        {item.done ? "✓" : "○"}
      </span>
      <span className="min-w-0">
        <span className={cn("mb-0.5 inline-block rounded px-1 py-px text-[9px] font-bold tracking-wide uppercase", color, item.done ? "opacity-80" : "")}>
          {label}{item.done ? " ✓" : ""}
        </span>
        <span className="block leading-tight font-medium text-foreground line-clamp-2">{item.title}</span>
      </span>
    </button>
  )
}

function DayCell({
  date,
  entries,
  scheduled,
  today,
  onToggle,
  busy,
  onAdd,
  onDeleteSchedule,
  deletingId,
}: {
  date: string
  entries: CalendarDayEntry[]
  scheduled: CalendarDayEntry["scheduled"]
  today: boolean
  onToggle: (studentId: number, itemId: number, done: boolean) => void
  busy: boolean
  onAdd: (date: string) => void
  onDeleteSchedule: (id: number) => void
  deletingId: number | null
}) {
  const dayNum = Number(date.slice(8))
  return (
    <div
      className={cn(
        "group flex min-h-20 flex-col rounded-lg border bg-card p-1 sm:min-h-24 md:p-1.5",
        today && "border-primary ring-1 ring-primary/40",
      )}
    >
      <div className={cn("mb-0.5 flex items-center justify-between px-0.5 text-[11px] font-semibold sm:mb-1 sm:text-xs", today ? "text-primary" : "text-muted-foreground")}>
        <span>{dayNum}</span>
        <div className="flex items-center gap-1">
          {today && <Badge variant="success" className="hidden h-4 px-1.5 text-[9px] sm:inline-flex">Today</Badge>}
          <button
            type="button"
            onClick={() => onAdd(date)}
            title="Add lesson on this day"
            className="flex size-4 items-center justify-center rounded text-muted-foreground/60 opacity-0 transition-opacity hover:bg-accent hover:text-foreground group-hover:opacity-100"
          >
            <CalendarPlus className="size-3.5" />
          </button>
        </div>
      </div>
      <div className="flex-1 space-y-1 overflow-hidden">
        {scheduled.map((s) => (
          <div key={`sched-${s.id}`} className="group/sched flex items-center gap-1 rounded bg-amber-100 px-1 py-0.5 text-[9px] font-semibold text-amber-800 dark:bg-amber-900/60 dark:text-amber-200 sm:text-[10px]" title={s.topic ?? ""}>
            <span className="min-w-0 flex-1 truncate">
              {s.start_time?.slice(0, 5) ?? ""} {s.lesson_type}: {s.topic}
            </span>
            <button
              type="button"
              disabled={deletingId === s.id}
              onClick={() => onDeleteSchedule(s.id)}
              title="Remove this scheduled lesson"
              className="shrink-0 rounded text-amber-800/50 opacity-0 transition-opacity hover:bg-amber-200 hover:text-amber-900 group-hover/sched:opacity-100 disabled:opacity-40 dark:text-amber-200/50 dark:hover:bg-amber-800 dark:hover:text-amber-100"
            >
              <Trash2 className="size-3" />
            </button>
          </div>
        ))}
        {entries.map((entry) => (
          <div key={entry.student_id} className="rounded-md bg-muted/40 px-1 py-0.5 sm:py-1">
            <Link
              to={`/students/${entry.student_id}`}
              className="block truncate text-[9px] font-semibold text-foreground hover:text-primary hover:underline sm:text-[10px]"
              title={entry.student_name}
            >
              {entry.student_name}
            </Link>
            {entry.is_training ? (
              <div className="mt-0.5 space-y-0.5">
                <LessonChip label="Course" item={entry.course} studentId={entry.student_id} onToggle={onToggle} busy={busy} />
                <LessonChip label="Practical" item={entry.practical} studentId={entry.student_id} onToggle={onToggle} busy={busy} />
              </div>
            ) : (
              <div className="px-1 py-0.5 text-[9px] text-muted-foreground/60 sm:text-[10px]">Rest day</div>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}

export function CalendarPage() {
  const now = new Date()
  const { date: tmDate } = useTimeMachine()
  const [month, setMonth] = useState(`${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`)
  const [studentId, setStudentId] = useState("")
  const { data: students } = useStudents()
  const { data: cal, isLoading } = useCalendar(month, studentId)
  const save = useSaveCalendarProgress()
  const saveSchedule = useSaveTeacherSchedule()
  const deleteSchedule = useDeleteTeacherSchedule()
  const [busyId, setBusyId] = useState<string | null>(null)
  const [deletingId, setDeletingId] = useState<number | null>(null)
  const [dialogOpen, setDialogOpen] = useState(false)
  const [form, setForm] = useState({
    schedule_date: tmDate,
    start_time: "10:00",
    end_time: "11:30",
    teacher_name: "",
    student_group: "Weekday",
    lesson_type: "Theory",
    topic: "",
    room_name: "",
    status: "Planned",
  })

  const openAddDialog = (date?: string) => {
    setForm((f) => ({ ...f, schedule_date: date ?? tmDate }))
    setDialogOpen(true)
  }

  const submitSchedule = () => {
    if (!form.topic.trim() || !form.teacher_name.trim()) return
    saveSchedule.mutate(
      {
        schedule_date: form.schedule_date,
        start_time: form.start_time,
        end_time: form.end_time || undefined,
        teacher_name: form.teacher_name.trim(),
        student_group: form.student_group,
        lesson_type: form.lesson_type,
        topic: form.topic.trim(),
        room_name: form.room_name.trim() || undefined,
        status: form.status,
      },
      {
        onSuccess: () => {
          setDialogOpen(false)
          setForm((f) => ({ ...f, topic: "", room_name: "", teacher_name: "" }))
        },
      },
    )
  }

  const handleDeleteSchedule = (id: number) => {
    setDeletingId(id)
    deleteSchedule.mutate(id, { onSettled: () => setDeletingId(null) })
  }

  const year = Number(month.slice(0, 4))
  const monthNum = Number(month.slice(5))
  const daysInMonth = cal?.days_in_month ?? new Date(year, monthNum, 0).getDate()
  const firstWeekday = new Date(year, monthNum - 1, 1).getDay()
  const tStr = todayStr()

  const scheduledByDate = useMemo(() => {
    const map = new Map<string, CalendarDayEntry["scheduled"]>()
    for (const s of cal?.teacher_schedule ?? []) {
      const d = s.schedule_date
      if (!map.has(d)) map.set(d, [])
      map.get(d)!.push(s)
    }
    return map
  }, [cal?.teacher_schedule])

  const dayKeys = useMemo(() => {
    const keys: Array<string | null> = []
    for (let i = 0; i < firstWeekday; i++) keys.push(null)
    for (let d = 1; d <= daysInMonth; d++) keys.push(`${month}-${String(d).padStart(2, "0")}`)
    return keys
  }, [month, firstWeekday, daysInMonth])

  const nav = (delta: number) => {
    const d = new Date(year, monthNum - 1 + delta, 1)
    setMonth(`${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`)
  }

  const handleToggle = (sid: number, itemId: number, done: boolean) => {
    setBusyId(`${sid}-${itemId}`)
    save.mutate(
      { student_id: sid, item_id: itemId, status: done ? "Completed" : "Pending", completion_date: tmDate },
      { onSettled: () => setBusyId(null) },
    )
  }

  const activeStudents = (students ?? []).filter((s) => s.is_active === 1)

  return (
    <div className="space-y-5">
      <div className="flex w-full flex-wrap items-center justify-end gap-2 rounded-lg border bg-card px-3 py-2.5">
          <Button variant="outline" className="flex-1 gap-1.5 sm:flex-none" onClick={() => openAddDialog()}>
            <CalendarPlus className="size-3.5" /> Add lesson
          </Button>
          <Select value={studentId} onValueChange={setStudentId}>
            <SelectTrigger className="w-full sm:w-52">
              <SelectValue placeholder="All trainees" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All trainees</SelectItem>
              {activeStudents.map((s) => (
                <SelectItem key={s.id} value={String(s.id)}>{s.name}</SelectItem>
              ))}
            </SelectContent>
          </Select>
      </div>

      {/* Month nav + legend */}
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div className="flex items-center gap-1">
          <Button variant="outline" size="icon" onClick={() => nav(-1)} aria-label="Previous month">
            <ChevronLeft />
          </Button>
          <div className="min-w-40 text-center">
            <div className="text-base font-semibold tracking-tight">{MONTHS[monthNum - 1]} {year}</div>
          </div>
          <Button variant="outline" size="icon" onClick={() => nav(1)} aria-label="Next month">
            <ChevronRight />
          </Button>
          <Button variant="ghost" size="sm" onClick={() => setMonth(tStr.slice(0, 7))} className="ml-1 text-xs">
            Today
          </Button>
        </div>
        <div className="flex items-center gap-3 text-xs text-muted-foreground">
          <span className="flex items-center gap-1.5"><School className="size-3.5 text-brand-blue" /> Course</span>
          <span className="flex items-center gap-1.5"><Wrench className="size-3.5 text-brand-orange" /> Practical</span>
          <span className="flex items-center gap-1.5"><CalendarDays className="size-3.5" /> {cal?.course_total ?? 0} course · {cal?.practical_total ?? 0} practical modules</span>
        </div>
      </div>

      {/* Grid */}
      {isLoading ? (
        <div className="grid grid-cols-7 gap-1 sm:gap-1.5">
          {Array.from({ length: 35 }).map((_, i) => (
            <Skeleton key={i} className="h-20 rounded-lg sm:h-24" />
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-7 gap-1 sm:gap-1.5">
          {WEEKDAYS.map((d) => (
            <div key={d} className="px-1 py-1 text-center text-xs font-semibold text-muted-foreground">{d}</div>
          ))}
          {dayKeys.map((key, i) =>
            key === null ? (
              <div key={`empty-${i}`} className="min-h-20 rounded-lg border border-dashed bg-muted/10 sm:min-h-24" />
            ) : (
              <DayCell
                key={key}
                date={key}
                entries={cal?.days[key] ?? []}
                scheduled={scheduledByDate.get(key) ?? []}
                today={key === tStr}
                onToggle={handleToggle}
                busy={busyId != null}
                onAdd={openAddDialog}
                onDeleteSchedule={handleDeleteSchedule}
                deletingId={deletingId}
              />
            ),
          )}
        </div>
      )}

      {/* Add / edit scheduled lesson dialog */}
      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Add scheduled lesson</DialogTitle>
          </DialogHeader>
          <div className="space-y-3">
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div className="space-y-1.5">
                <Label htmlFor="sched-date">Date *</Label>
                <Input id="sched-date" type="date" value={form.schedule_date} onChange={(e) => setForm({ ...form, schedule_date: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="sched-group">Group</Label>
                <Select value={form.student_group} onValueChange={(v) => setForm({ ...form, student_group: v })}>
                  <SelectTrigger id="sched-group" className="w-full"><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Weekday">Weekday</SelectItem>
                    <SelectItem value="Weekend">Weekend</SelectItem>
                    <SelectItem value="">All groups</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="sched-start">Start *</Label>
                <Input id="sched-start" type="time" value={form.start_time} onChange={(e) => setForm({ ...form, start_time: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="sched-end">End</Label>
                <Input id="sched-end" type="time" value={form.end_time} onChange={(e) => setForm({ ...form, end_time: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="sched-type">Lesson type</Label>
                <Select value={form.lesson_type} onValueChange={(v) => setForm({ ...form, lesson_type: v })}>
                  <SelectTrigger id="sched-type" className="w-full"><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Theory">Theory</SelectItem>
                    <SelectItem value="Practical">Practical</SelectItem>
                    <SelectItem value="Live Repair">Live Repair</SelectItem>
                    <SelectItem value="Exam">Exam</SelectItem>
                    <SelectItem value="Other">Other</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="sched-status">Status</Label>
                <Select value={form.status} onValueChange={(v) => setForm({ ...form, status: v })}>
                  <SelectTrigger id="sched-status" className="w-full"><SelectValue /></SelectTrigger>
                  <SelectContent>
                    <SelectItem value="Planned">Planned</SelectItem>
                    <SelectItem value="Taught">Taught</SelectItem>
                    <SelectItem value="Cancelled">Cancelled</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="sched-teacher">Teacher *</Label>
              <Input id="sched-teacher" placeholder="e.g. Ko Hein" value={form.teacher_name} onChange={(e) => setForm({ ...form, teacher_name: e.target.value })} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="sched-topic">Topic *</Label>
              <Input id="sched-topic" placeholder="e.g. Schematic Basic — XZZ Drawing" value={form.topic} onChange={(e) => setForm({ ...form, topic: e.target.value })} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="sched-room">Room</Label>
              <Input id="sched-room" placeholder="e.g. Lab 1" value={form.room_name} onChange={(e) => setForm({ ...form, room_name: e.target.value })} />
            </div>
            {saveSchedule.isError && (
              <p className="text-xs text-destructive">{saveSchedule.error.message}</p>
            )}
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDialogOpen(false)}>Cancel</Button>
            <Button onClick={submitSchedule} disabled={saveSchedule.isPending || !form.topic.trim() || !form.teacher_name.trim()}>
              {saveSchedule.isPending ? "Saving…" : "Save lesson"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}
