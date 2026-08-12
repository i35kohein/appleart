import { useState } from "react"
import { Link, useParams } from "react-router-dom"
import {
  ArrowLeft,
  BarChart3,
  BookOpen,
  CalendarCheck,
  CreditCard,
  GraduationCap,
  Save,
  ShoppingBag,
  Stethoscope,
  Wrench,
} from "lucide-react"
import {
  useAttendance,
  useExam,
  useHistory,
  usePayments,
  useRepairs,
  useSaveExam,
  useSavePayment,
  useSaveRepair,
  useStudentById,
} from "@/features/students/detail-api"
import { useCurriculum, useStudentProgress } from "@/features/students/api"
import { StudentAvatar } from "@/components/ui/avatar"
import { AnalyticsDialog } from "@/features/students/components/AnalyticsDialog"
import { IconBadge } from "@/components/common/icon-badge"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Progress } from "@/components/ui/progress"
import { Skeleton } from "@/components/ui/skeleton"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { cn, formatDate, pct } from "@/lib/utils"

function useCurriculumByIds() {
  const { data: items } = useCurriculum()
  const byId = new Map((items ?? []).map((i) => [i.id, i]))
  return { items: items ?? [], byId }
}

function Field({ label, value }: { label: string; value: string | null | undefined }) {
  return (
    <div>
      <div className="text-xs text-muted-foreground">{label}</div>
      <div className="text-sm font-medium">{value || "—"}</div>
    </div>
  )
}

function ExamCard({ studentId }: { studentId: number }) {
  const { data: exam, isLoading } = useExam(studentId)
  const save = useSaveExam()
  const [open, setOpen] = useState(false)
  const [form, setForm] = useState({ exam_name: "Final Exam", score: "", max_score: "100", exam_date: "", note: "" })

  const start = () => {
    setForm({
      exam_name: exam?.exam_name ?? "Final Exam",
      score: exam?.score ?? "",
      max_score: exam?.max_score ?? "100",
      exam_date: exam?.exam_date ?? "",
      note: exam?.note ?? "",
    })
    setOpen(true)
  }

  const submit = () => {
    save.mutate(
      {
        student_id: studentId,
        exam_name: form.exam_name,
        score: form.score,
        max_score: form.max_score,
        exam_date: form.exam_date,
        note: form.note,
      },
      { onSuccess: () => setOpen(false) },
    )
  }

  if (isLoading) return <Skeleton className="h-24 w-full" />

  const scoreNum = Number(exam?.score ?? 0)
  const maxNum = Number(exam?.max_score ?? 100)
  const grade = maxNum > 0 ? Math.round((scoreNum / maxNum) * 100) : 0

  return (
    <Card>
      <CardHeader className="flex-row items-center justify-between px-5">
        <CardTitle className="flex items-center gap-2 text-base">
          <IconBadge icon={GraduationCap} color="purple" size="sm" /> Exam
        </CardTitle>
        <Button size="sm" variant="outline" onClick={start}>
          {exam ? "Edit exam" : "Record exam"}
        </Button>
      </CardHeader>
      <CardContent className="px-5 pt-0">
        {exam ? (
          <div className="space-y-3">
            <div className="flex items-end justify-between">
              <div>
                <div className="text-sm font-semibold">{exam.exam_name}</div>
                <div className="text-xs text-muted-foreground">{exam.exam_date ? formatDate(exam.exam_date) : "No date"}</div>
              </div>
              <div className="text-right">
                <div className="text-2xl font-semibold tabular-nums tracking-tight">
                  {exam.score}
                  <span className="text-sm font-normal text-muted-foreground"> / {exam.max_score}</span>
                </div>
                <Badge variant={grade >= 60 ? "success" : grade >= 40 ? "warning" : "destructive"}>{grade}%</Badge>
              </div>
            </div>
            {exam.note && <p className="text-xs text-muted-foreground">{exam.note}</p>}
          </div>
        ) : (
          <p className="py-2 text-sm text-muted-foreground">No exam recorded yet.</p>
        )}

        {open && (
          <div className="mt-4 space-y-3 rounded-lg border p-4">
            <div className="grid gap-3 sm:grid-cols-2">
              <div className="space-y-1.5">
                <Label htmlFor="exam-name">Exam name</Label>
                <Input id="exam-name" value={form.exam_name} onChange={(e) => setForm({ ...form, exam_name: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="exam-date">Date</Label>
                <Input id="exam-date" type="date" value={form.exam_date} onChange={(e) => setForm({ ...form, exam_date: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="exam-score">Score</Label>
                <Input id="exam-score" type="number" min={0} value={form.score} onChange={(e) => setForm({ ...form, score: e.target.value })} />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="exam-max">Max score</Label>
                <Input id="exam-max" type="number" min={1} value={form.max_score} onChange={(e) => setForm({ ...form, max_score: e.target.value })} />
              </div>
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="exam-note">Note</Label>
              <Input id="exam-note" value={form.note} onChange={(e) => setForm({ ...form, note: e.target.value })} />
            </div>
            {save.isError && <Alert variant="destructive"><AlertDescription>{save.error.message}</AlertDescription></Alert>}
            <div className="flex justify-end gap-2">
              <Button variant="outline" size="sm" onClick={() => setOpen(false)}>Cancel</Button>
              <Button size="sm" onClick={submit} disabled={save.isPending}>
                <Save /> {save.isPending ? "Saving…" : "Save exam"}
              </Button>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  )
}

function PaymentCard({ studentId }: { studentId: number }) {
  const { data: plans } = usePayments()
  const save = useSavePayment()
  const plan = (plans ?? []).find((p) => p.student_id === studentId)
  const [open, setOpen] = useState(false)
  const [showForm, setShowForm] = useState(false)
  const [form, setForm] = useState({
    total_amount: "",
    first_amount: "",
    first_paid_at: "",
    second_amount: "",
    second_paid_at: "",
    reminder_date: "",
    note: "",
  })

  const start = () => {
    setForm({
      total_amount: plan?.total_amount ?? "",
      first_amount: plan?.first_amount ?? "",
      first_paid_at: plan?.first_paid_at ?? "",
      second_amount: plan?.second_amount ?? "",
      second_paid_at: plan?.second_paid_at ?? "",
      reminder_date: plan?.reminder_date ?? "",
      note: plan?.note ?? "",
    })
    setShowForm(false)
    setOpen(true)
  }

  const startEdit = () => {
    setForm({
      total_amount: plan?.total_amount ?? "",
      first_amount: plan?.first_amount ?? "",
      first_paid_at: plan?.first_paid_at ?? "",
      second_amount: plan?.second_amount ?? "",
      second_paid_at: plan?.second_paid_at ?? "",
      reminder_date: plan?.reminder_date ?? "",
      note: plan?.note ?? "",
    })
    setShowForm(true)
  }

  const submit = () => {
    save.mutate({ student_id: studentId, ...form }, { onSuccess: () => setOpen(false) })
  }

  const total = Number(plan?.total_amount ?? 0)
  // first_amount = paid so far; second_amount = counted as paid once second_paid_at is set.
  const paid = Number(plan?.first_amount ?? 0) + (plan?.second_paid_at ? Number(plan?.second_amount ?? 0) : 0)
  const remaining = total - paid

  return (
    <>
      <Button variant="outline" size="sm" onClick={start} className="gap-1.5">
        <CreditCard className="size-3.5" /> Payment plan
      </Button>

      <Dialog open={open} onOpenChange={(o) => !o && setOpen(false)}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2"><CreditCard className="size-4 text-teal-600" /> Payment plan</DialogTitle>
            <DialogDescription>Plan summary & payments for this trainee.</DialogDescription>
          </DialogHeader>

          <div className="space-y-4">
            {plan ? (
              <>
                <div className="flex items-end justify-between">
                  <div>
                    <div className="text-2xl font-semibold tabular-nums tracking-tight">{Number(plan.total_amount).toLocaleString()} MMK</div>
                    <div className="text-xs text-muted-foreground">
                      {paid.toLocaleString()} paid · {remaining.toLocaleString()} remaining
                    </div>
                  </div>
                  <Progress value={total > 0 ? (paid / total) * 100 : 0} className="w-28" />
                </div>
                <div className="grid grid-cols-2 gap-3 text-sm">
                  <Field label="Amount paid" value={plan.first_paid_at ? `${Number(plan.first_amount).toLocaleString()} MMK · ${formatDate(plan.first_paid_at)}` : `${Number(plan.first_amount).toLocaleString()} MMK`} />
                  <Field label="Remaining" value={plan.second_paid_at ? `Paid · ${formatDate(plan.second_paid_at)}` : `${Number(plan.second_amount).toLocaleString()} MMK`} />
                  <Field label="Reminder" value={plan.reminder_date} />
                  <Field label="Updated" value={plan.updated_at} />
                </div>
                {plan.note && <p className="text-xs text-muted-foreground">{plan.note}</p>}
              </>
            ) : (
              <p className="text-sm text-muted-foreground">No payment plan yet.</p>
            )}

            {showForm && (
              <div className="space-y-3 rounded-lg border p-4">
                <div className="grid gap-3 sm:grid-cols-3">
                  {(
                    [
                      ["total_amount", "Total (MMK)"],
                      ["first_amount", "Paid (MMK)"],
                      ["second_amount", "Remaining (MMK)"],
                    ] as const
                  ).map(([key, label]) => (
                    <div key={key} className="space-y-1.5">
                      <Label htmlFor={`pay-${key}`}>{label}</Label>
                      <Input id={`pay-${key}`} type="number" min={0} value={form[key]} onChange={(e) => setForm({ ...form, [key]: e.target.value })} />
                    </div>
                  ))}
                </div>
                <div className="grid gap-3 sm:grid-cols-3">
                  {(
                    [
                      ["first_paid_at", "Paid date"],
                      ["second_paid_at", "Remaining paid date"],
                      ["reminder_date", "Reminder date"],
                    ] as const
                  ).map(([key, label]) => (
                    <div key={key} className="space-y-1.5">
                      <Label htmlFor={`pay-${key}`}>{label}</Label>
                      <Input id={`pay-${key}`} type="date" value={form[key]} onChange={(e) => setForm({ ...form, [key]: e.target.value })} />
                    </div>
                  ))}
                </div>
                <div className="space-y-1.5">
                  <Label htmlFor="pay-note">Note</Label>
                  <Input id="pay-note" value={form.note} onChange={(e) => setForm({ ...form, note: e.target.value })} />
                </div>
                {save.isError && <Alert variant="destructive"><AlertDescription>{save.error.message}</AlertDescription></Alert>}
              </div>
            )}
          </div>

          <DialogFooter className="gap-2">
            <Button variant="outline" size="sm" onClick={() => setOpen(false)}>Close</Button>
            {!showForm ? (
              <Button size="sm" onClick={startEdit}>{plan ? "Edit plan" : "Add plan"}</Button>
            ) : (
              <Button size="sm" onClick={submit} disabled={save.isPending}>
                <Save /> {save.isPending ? "Saving…" : "Save plan"}
              </Button>
            )}
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </>
  )
}

function RepairsCard({ studentId }: { studentId: number }) {
  const { data: repairs, isLoading } = useRepairs(studentId)
  const save = useSaveRepair()
  const [open, setOpen] = useState(false)
  const [form, setForm] = useState({ repair_title: "", comment: "" })

  const submit = () => {
    save.mutate(
      { student_id: studentId, repair_title: form.repair_title, comment: form.comment },
      { onSuccess: () => { setOpen(false); setForm({ repair_title: "", comment: "" }) } },
    )
  }

  return (
    <Card>
      <CardHeader className="flex-row items-center justify-between px-5">
        <CardTitle className="flex items-center gap-2 text-base">
          <IconBadge icon={Stethoscope} color="orange" size="sm" /> Real-world repairs
        </CardTitle>
        <Button size="sm" variant="outline" onClick={() => setOpen(true)}>Add repair</Button>
      </CardHeader>
      <CardContent className="px-5 pt-0">
        {isLoading ? (
          <Skeleton className="h-16 w-full" />
        ) : repairs && repairs.length > 0 ? (
          <ul className="divide-y">
            {repairs.map((r) => (
              <li key={r.id} className="py-2.5">
                <div className="flex items-center justify-between gap-2">
                  <span className="text-sm font-medium">{r.repair_title}</span>
                  <span className="shrink-0 text-xs text-muted-foreground">{formatDate(r.created_at)}</span>
                </div>
                {r.comment && <p className="mt-0.5 text-xs text-muted-foreground">{r.comment}</p>}
                {r.trainer_name && <p className="mt-0.5 text-[11px] text-muted-foreground">by {r.trainer_name}</p>}
              </li>
            ))}
          </ul>
        ) : (
          <p className="py-2 text-sm text-muted-foreground">No repairs recorded yet.</p>
        )}

        {open && (
          <div className="mt-4 space-y-3 rounded-lg border p-4">
            <div className="space-y-1.5">
              <Label htmlFor="repair-title">Repair title</Label>
              <Input id="repair-title" placeholder="e.g. iPhone 11 battery replacement" value={form.repair_title} onChange={(e) => setForm({ ...form, repair_title: e.target.value })} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="repair-comment">Comment</Label>
              <Input id="repair-comment" placeholder="What the trainee did" value={form.comment} onChange={(e) => setForm({ ...form, comment: e.target.value })} />
            </div>
            {save.isError && <Alert variant="destructive"><AlertDescription>{save.error.message}</AlertDescription></Alert>}
            <div className="flex justify-end gap-2">
              <Button variant="outline" size="sm" onClick={() => setOpen(false)}>Cancel</Button>
              <Button size="sm" onClick={submit} disabled={save.isPending || !form.repair_title || !form.comment}>
                <Save /> {save.isPending ? "Saving…" : "Save repair"}
              </Button>
            </div>
          </div>
        )}
      </CardContent>
    </Card>
  )
}

function AttendanceCard({ studentId }: { studentId: number }) {
  const { data: records, isLoading } = useAttendance(studentId, 14)
  const counts = (records ?? []).reduce(
    (acc, r) => {
      acc[r.status] = (acc[r.status] ?? 0) + 1
      return acc
    },
    {} as Record<string, number>,
  )

  return (
    <Card>
      <CardHeader className="px-5">
        <CardTitle className="flex items-center gap-2 text-base">
          <IconBadge icon={CalendarCheck} color="blue" size="sm" /> Attendance
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-3 px-5 pt-0">
        {isLoading ? (
          <Skeleton className="h-16 w-full" />
        ) : records && records.length > 0 ? (
          <>
            <div className="flex gap-2">
              <Badge variant="success">Present {counts["Present"] ?? 0}</Badge>
              <Badge variant="warning">Late {counts["Late"] ?? 0}</Badge>
              <Badge variant="destructive">Absent {counts["Absent"] ?? 0}</Badge>
            </div>
            <ul className="max-h-44 divide-y overflow-y-auto text-sm">
              {records.slice(0, 8).map((r) => (
                <li key={r.id} className="flex items-center justify-between py-1.5">
                  <span className="text-muted-foreground">{new Date(r.created_at).toLocaleDateString("en-GB", { day: "numeric", month: "short" })}</span>
                  <Badge variant={r.status === "Present" ? "success" : r.status === "Late" ? "warning" : "destructive"}>{r.status}</Badge>
                </li>
              ))}
            </ul>
          </>
        ) : (
          <p className="py-2 text-sm text-muted-foreground">No attendance records yet.</p>
        )}
      </CardContent>
    </Card>
  )
}

function CurriculumHalf({ type, items, completed }: { type: "Course" | "Practical"; items: Array<{ id: number; title: string; category: string }>; completed: Set<number> }) {
  const done = items.filter((i) => completed.has(i.id)).length
  return (
    <Card>
      <CardHeader className="px-5">
        <CardTitle className="flex items-center gap-2 text-base">
          {type === "Course" ? <BookOpen className="size-4 text-emerald-600" /> : <Wrench className="size-4 text-amber-600" />}
          {type === "Course" ? "Courses" : "Practical"}
        </CardTitle>
      </CardHeader>
      <CardContent className="px-0 pb-0">
        <div className="flex items-center gap-3 px-5 pb-3">
          <Progress value={pct(done, items.length)} className="h-1.5 flex-1" />
          <span className="text-xs tabular-nums text-muted-foreground">{done}/{items.length}</span>
        </div>
        <div className="max-h-96 divide-y overflow-y-auto border-t">
          {items.map((item) => {
            const isDone = completed.has(item.id)
            return (
              <div key={item.id} className="flex items-center gap-3 px-5 py-2.5">
                <span
                  className={cn(
                    "flex size-5 shrink-0 items-center justify-center rounded-full border text-[10px] font-semibold",
                    isDone
                      ? "border-emerald-500 bg-emerald-500 text-white"
                      : "border-border text-muted-foreground",
                  )}
                >
                  {isDone ? "✓" : ""}
                </span>
                <div className="min-w-0 flex-1">
                  <div className={cn("truncate text-sm", isDone ? "font-medium" : "text-muted-foreground")}>{item.title}</div>
                  <div className="text-[11px] text-muted-foreground">{item.category}</div>
                </div>
              </div>
            )
          })}
        </div>
      </CardContent>
    </Card>
  )
}

export function StudentDetailPage() {
  const { id } = useParams()
  const student = useStudentById(id)
  const { data: progress } = useStudentProgress(id ? Number(id) : null)
  const { byId } = useCurriculumByIds()
  const { data: history, isLoading: historyLoading } = useHistory(id ? Number(id) : null)
  const [analyticsOpen, setAnalyticsOpen] = useState(false)

  if (student.isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-8 w-48" />
        <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)]">
          <Skeleton className="h-72 w-full" />
          <Skeleton className="h-72 w-full" />
        </div>
      </div>
    )
  }

  if (!student.data) {
    return (
      <div className="flex flex-col items-center gap-3 py-20 text-center">
        <p className="text-sm font-medium">Trainee not found</p>
        <Button variant="outline" asChild><Link to="/students"><ArrowLeft /> Back to trainees</Link></Button>
      </div>
    )
  }

  const s = student.data
  const sid = s.id
  const completed = new Set((progress ?? []).filter((p) => p.detail_idx == null).map((p) => p.item_id))

  return (
    <div className="space-y-6">
      <div>
        <Button variant="ghost" size="sm" asChild className="-ml-2 mb-2 text-muted-foreground">
          <Link to="/students"><ArrowLeft /> All trainees</Link>
        </Button>
        <div className="flex flex-wrap items-center gap-4">
          <StudentAvatar name={s.name} photoPath={s.photo_path} className="size-14" />
          <div className="min-w-0 flex-1">
            <h1 className="text-2xl font-semibold tracking-tight">{s.name}</h1>
            <div className="mt-1 flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
              <Badge variant={s.rollcall_group === "Weekend" ? "warning" : "secondary"}>{s.rollcall_group}</Badge>
              <Badge variant={s.is_active === 1 ? "success" : "muted"}>{s.is_active === 1 ? "Active" : "Inactive"}</Badge>
              {s.enrollment_date && <span>Joined {formatDate(s.enrollment_date)}</span>}
            </div>
          </div>
          <div className="flex items-center gap-2">
            <Button variant="outline" size="sm" className="h-9 gap-1.5" onClick={() => setAnalyticsOpen(true)}>
              <BarChart3 className="size-4 text-primary" /> Analytics
            </Button>
            <PaymentCard studentId={sid} />
          </div>
        </div>
      </div>

      <AnalyticsDialog studentId={sid} studentName={s.name} open={analyticsOpen} onOpenChange={setAnalyticsOpen} />

      {/* Compact profile strip */}
      <Card>
        <CardContent className="flex flex-wrap items-center gap-x-6 gap-y-1 px-5 py-3">
          <span className="text-xs"><span className="text-muted-foreground">Phone </span><span className="font-medium">{s.phone ?? "—"}</span></span>
          <span className="text-xs"><span className="text-muted-foreground">Email </span><span className="font-medium">{s.email ?? "—"}</span></span>
          <span className="text-xs"><span className="text-muted-foreground">Shop </span><span className="font-medium">{s.shop_name ?? "—"}</span></span>
          <span className="text-xs"><span className="text-muted-foreground">Address </span><span className="font-medium">{s.address ?? "—"}</span></span>
          <span className="text-xs"><span className="text-muted-foreground">Registered </span><span className="font-medium">{s.created_at}</span></span>
        </CardContent>
      </Card>

      {/* Top: Exam + Attendance — compact, side by side */}
      <div className="grid gap-4 md:grid-cols-2">
        <ExamCard studentId={sid} />
        <AttendanceCard studentId={sid} />
      </div>

      {/* Course | Practical split into two halves */}
      <div className="grid gap-6 lg:grid-cols-2">
        <CurriculumHalf type="Course" items={[...byId.values()].filter((i) => i.type === "Course")} completed={completed} />
        <CurriculumHalf type="Practical" items={[...byId.values()].filter((i) => i.type === "Practical")} completed={completed} />
      </div>

      {/* Rest: profile + payments + repairs + history */}
      <div className="grid gap-6 lg:grid-cols-2">
        <RepairsCard studentId={sid} />

        <Card>
          <CardHeader className="px-5">
            <CardTitle className="flex items-center gap-2 text-base">
              <ShoppingBag className="size-4 text-emerald-600" /> Completion history
            </CardTitle>
          </CardHeader>
            <CardContent className="px-0 pb-0">
              {historyLoading ? (
                <Skeleton className="mx-5 h-24" />
              ) : history && history.length > 0 ? (
                <ul className="max-h-80 divide-y overflow-y-auto">
                  {history.map((h) => (
                    <li key={h.id} className="flex items-center justify-between gap-3 px-5 py-2.5">
                      <div className="min-w-0">
                        <div className="truncate text-sm">{h.title}</div>
                        <div className="text-[11px] text-muted-foreground">
                          {h.type} · {h.trainer_name ?? "Instructor"}
                        </div>
                      </div>
                      <span className="shrink-0 text-xs tabular-nums text-muted-foreground">{formatDate(h.completion_date)}</span>
                    </li>
                  ))}
                </ul>
              ) : (
                <p className="px-5 py-3 text-sm text-muted-foreground">No completions yet.</p>
              )}
            </CardContent>
          </Card>
      </div>
    </div>
  )
}
