import { useMemo, useState } from "react"
import { GraduationCap, Pencil, Search } from "lucide-react"
import { useStudents } from "@/features/students/api"
import { useExam, useSaveExam } from "@/features/students/detail-api"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Skeleton } from "@/components/ui/skeleton"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { StudentAvatar } from "@/components/ui/avatar"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { formatDate } from "@/lib/utils"
import { EmptyState } from "@/components/common/feedback"
import { IconBadge } from "@/components/common/icon-badge"

function ExamDialog({
  studentId,
  studentName,
  open,
  onOpenChange,
}: {
  studentId: number | null
  studentName: string
  open: boolean
  onOpenChange: (open: boolean) => void
}) {
  const { data: exam, isLoading } = useExam(studentId)
  const save = useSaveExam()
  const [form, setForm] = useState({ exam_name: "Final Exam", score: "", max_score: "100", exam_date: "", note: "" })
  const [lastKey, setLastKey] = useState<string>("")

  const key = studentId != null ? String(studentId) : "none"
  if (open && key !== lastKey && !isLoading) {
    setLastKey(key)
    setForm({
      exam_name: exam?.exam_name ?? "Final Exam",
      score: exam?.score ?? "",
      max_score: exam?.max_score ?? "100",
      exam_date: exam?.exam_date ?? "",
      note: exam?.note ?? "",
    })
  }

  const submit = () => {
    if (studentId == null) return
    save.mutate(
      {
        student_id: studentId,
        exam_name: form.exam_name,
        score: form.score,
        max_score: form.max_score,
        exam_date: form.exam_date,
        note: form.note,
      },
      {
        onSuccess: () => {
          onOpenChange(false)
          setLastKey("")
        },
      },
    )
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>{studentName ? `Exam — ${studentName}` : "Exam"}</DialogTitle>
          <DialogDescription>Record or update the trainee's exam result.</DialogDescription>
        </DialogHeader>

        {isLoading ? (
          <Skeleton className="h-40 w-full" />
        ) : (
          <div className="space-y-4">
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

            {save.isError && (
              <Alert variant="destructive">
                <AlertDescription>{save.error.message}</AlertDescription>
              </Alert>
            )}
          </div>
        )}

        <DialogFooter>
          <Button variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
          <Button onClick={submit} disabled={save.isPending || studentId == null}>
            {save.isPending ? "Saving…" : "Save exam"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

export function ExamsPage() {
  const students = useStudents()
  const [search, setSearch] = useState("")
  const [dialog, setDialog] = useState<{ studentId: number; name: string } | null>(null)

  const active = useMemo(
    () => (students.data ?? []).filter((s) => s.is_active === 1),
    [students.data],
  )

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return active.filter((s) => !q || s.name.toLowerCase().includes(q) || (s.phone ?? "").includes(q))
  }, [active, search])

  return (
    <div className="space-y-6">
      <div className="flex items-center rounded-lg border bg-card px-3 py-2.5">
        <div className="relative w-full sm:w-72">
          <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
          <Input placeholder="Search trainee…" value={search} onChange={(e) => setSearch(e.target.value)} className="pl-9" aria-label="Search exams" />
        </div>
      </div>

      <Card>
        <CardHeader className="px-5">
          <CardTitle className="flex items-center gap-2 text-base">
            <IconBadge icon={GraduationCap} color="purple" size="sm" /> Active trainees
          </CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Trainee</TableHead>
                  <TableHead className="hidden sm:table-cell">Exam</TableHead>
                  <TableHead>Result</TableHead>
                  <TableHead className="hidden md:table-cell">Date</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {students.isLoading ? (
                  Array.from({ length: 6 }).map((_, i) => (
                    <TableRow key={i}><TableCell colSpan={5}><Skeleton className="h-10 w-full" /></TableCell></TableRow>
                  ))
                ) : filtered.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={5}>
                      <EmptyState icon={<GraduationCap className="size-4" />} title="No trainees found" />
                    </TableCell>
                  </TableRow>
                ) : (
                  filtered.map((s) => (
                    <ExamRow key={s.id} studentId={s.id} name={s.name} photoPath={s.photo_path} onEdit={() => setDialog({ studentId: s.id, name: s.name })} />
                  ))
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <ExamDialog
        studentId={dialog?.studentId ?? null}
        studentName={dialog?.name ?? ""}
        open={dialog != null}
        onOpenChange={(o) => !o && setDialog(null)}
      />
    </div>
  )
}

function ExamRow({
  studentId,
  name,
  photoPath,
  onEdit,
}: {
  studentId: number
  name: string
  photoPath: string | null
  onEdit: () => void
}) {
  const { data: exam, isLoading } = useExam(studentId)

  return (
    <TableRow>
      <TableCell>
        <div className="flex items-center gap-3">
          <StudentAvatar name={name} photoPath={photoPath} />
          <div className="min-w-0">
            <div className="truncate font-medium">{name}</div>
            <div className="text-xs text-muted-foreground md:hidden">{exam?.exam_name ?? "No exam"}</div>
          </div>
        </div>
      </TableCell>
      <TableCell className="hidden sm:table-cell">{isLoading ? "…" : (exam?.exam_name ?? "—")}</TableCell>
      <TableCell>
        {isLoading ? (
          "…"
        ) : exam ? (
          (() => {
            const score = Number(exam.score)
            const max = Number(exam.max_score)
            const grade = max > 0 ? Math.round((score / max) * 100) : 0
            return (
              <div className="flex items-center gap-2">
                <span className="tabular-nums font-medium">{score}/{max}</span>
                <Badge variant={grade >= 60 ? "success" : grade >= 40 ? "warning" : "destructive"}>{grade}%</Badge>
              </div>
            )
          })()
        ) : (
          <Badge variant="muted">Not taken</Badge>
        )}
      </TableCell>
      <TableCell className="hidden text-muted-foreground md:table-cell">{exam?.exam_date ? formatDate(exam.exam_date) : "—"}</TableCell>
      <TableCell className="text-right">
        <Button variant="ghost" size="icon" aria-label={`Edit exam for ${name}`} onClick={onEdit}>
          <Pencil />
        </Button>
      </TableCell>
    </TableRow>
  )
}
