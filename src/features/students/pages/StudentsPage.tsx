import { useEffect, useMemo, useState } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { Link } from "react-router-dom"
import { Eye, Pencil, Plus, Search, Trash2, Users } from "lucide-react"
import { useDeleteStudent, useSaveStudent, useStudents } from "@/features/students/api"
import type { Student, StudentInput } from "@/features/students/types"
import { useTimeMachine } from "@/lib/timemachine"
import { StudentAvatar } from "@/components/ui/avatar"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Progress } from "@/components/ui/progress"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Skeleton } from "@/components/ui/skeleton"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { pct } from "@/lib/utils"
import { EmptyState } from "@/components/common/feedback"

const studentSchema = z.object({
  name: z.string().min(1, "Name is required"),
  phone: z.string().optional(),
  email: z.string().optional(),
  address: z.string().optional(),
  shop_name: z.string().optional(),
  rollcall_group: z.enum(["Weekday", "Weekend"]),
  is_active: z.boolean(),
  role: z.enum(["student", "online"]),
})

type StudentFormValues = z.infer<typeof studentSchema>

function emptyForm(student?: Student): StudentFormValues {
  return {
    name: student?.name ?? "",
    phone: student?.phone ?? "",
    email: student?.email ?? "",
    address: student?.address ?? "",
    shop_name: student?.shop_name ?? "",
    rollcall_group: student?.rollcall_group ?? "Weekday",
    is_active: student ? student.is_active === 1 : true,
    role: student?.role ?? "student",
  }
}

function StudentDialog({
  open,
  onOpenChange,
  student,
}: {
  open: boolean
  onOpenChange: (open: boolean) => void
  student: Student | null
}) {
  const save = useSaveStudent()
  const { date: tmDate } = useTimeMachine()
  const {
    register,
    handleSubmit,
    reset,
    watch,
    setValue,
    formState: { errors },
  } = useForm<StudentFormValues>({
    resolver: zodResolver(studentSchema),
    defaultValues: emptyForm(student ?? undefined),
  })

  const isActive = watch("is_active")

  // Re-apply form values whenever the edited student changes (dialog stays mounted).
  useEffect(() => {
    reset(emptyForm(student ?? undefined))
  }, [student, reset])

  const onSubmit = (values: StudentFormValues) => {
    const input: StudentInput = {
      name: values.name,
      phone: values.phone ?? "",
      email: values.email ?? "",
      address: values.address ?? "",
      shop_name: values.shop_name ?? "",
      rollcall_group: values.rollcall_group,
      is_active: values.is_active ? 1 : 0,
      role: values.role,
    }
    save.mutate(
      { id: student?.id, input: { ...input, enrollment_date: tmDate } },
      {
        onSuccess: () => {
          onOpenChange(false)
          reset()
        },
      },
    )
  }

  return (
    <Dialog
      open={open}
      onOpenChange={(o) => {
        if (!o) reset()
        onOpenChange(o)
      }}
    >
      <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{student ? "Edit trainee" : "Add trainee"}</DialogTitle>
          <DialogDescription>
            {student ? "Update the trainee's details." : "Register a new trainee in the academy."}
          </DialogDescription>
        </DialogHeader>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="student-name">Name *</Label>
            <Input id="student-name" placeholder="Full name" {...register("name")} />
            {errors.name && <p className="text-xs text-destructive">{errors.name.message}</p>}
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="student-phone">Phone</Label>
              <Input id="student-phone" placeholder="09…" {...register("phone")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="student-email">Email</Label>
              <Input id="student-email" type="email" placeholder="name@example.com" {...register("email")} />
            </div>
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="student-shop">Shop</Label>
              <Input id="student-shop" placeholder="i35 Apple Service" {...register("shop_name")} />
            </div>
            <div className="space-y-2">
              <Label>Group</Label>
              <Select
                value={watch("rollcall_group")}
                onValueChange={(v) => setValue("rollcall_group", v as "Weekday" | "Weekend")}
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select group" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Weekday">Weekday</SelectItem>
                  <SelectItem value="Weekend">Weekend</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="student-address">Address</Label>
            <Input id="student-address" placeholder="Address" {...register("address")} />
          </div>

          <div className="grid gap-3 sm:grid-cols-2">
            <div className="space-y-2">
              <Label>Role</Label>
              <Select value={watch("role")} onValueChange={(v) => setValue("role", v as "student" | "online")}>
                <SelectTrigger className="w-full"><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="student">Student (academy)</SelectItem>
                  <SelectItem value="online">Online (e-learning)</SelectItem>
                </SelectContent>
              </Select>
              <p className="text-[11px] text-muted-foreground">Online students က main platform မှာ မပေါ်ဘူး — portal မှာပဲ။</p>
            </div>
            <label className="flex cursor-pointer items-center gap-2.5 rounded-lg border p-3 text-sm">
              <input
                type="checkbox"
                className="size-4 accent-emerald-600"
                checked={isActive}
                onChange={(e) => setValue("is_active", e.target.checked)}
              />
              <span>
                Active trainee
                <span className="block text-xs text-muted-foreground">Inactive = platform မှာ မပြ။</span>
              </span>
            </label>
          </div>

          {save.isError && (
            <Alert variant="destructive">
              <AlertDescription>{save.error.message}</AlertDescription>
            </Alert>
          )}

          <DialogFooter>
            <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
              Cancel
            </Button>
            <Button type="submit" disabled={save.isPending}>
              {save.isPending ? "Saving…" : student ? "Save changes" : "Add trainee"}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  )
}

function DeleteDialog({
  student,
  onOpenChange,
}: {
  student: Student | null
  onOpenChange: (open: boolean) => void
}) {
  const del = useDeleteStudent()
  return (
    <Dialog open={student != null} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-md">
        <DialogHeader>
          <DialogTitle>Delete trainee?</DialogTitle>
          <DialogDescription>
            This permanently removes <span className="font-medium text-foreground">{student?.name}</span> and all their
            progress, attendance, exam and payment records. This cannot be undone.
          </DialogDescription>
        </DialogHeader>
        {del.isError && (
          <Alert variant="destructive">
            <AlertDescription>{del.error.message}</AlertDescription>
          </Alert>
        )}
        <DialogFooter>
          <Button variant="outline" onClick={() => onOpenChange(false)}>
            Cancel
          </Button>
          <Button
            variant="destructive"
            disabled={del.isPending}
            onClick={() =>
              student &&
              del.mutate(student.id, {
                onSuccess: () => onOpenChange(false),
              })
            }
          >
            {del.isPending ? "Deleting…" : "Delete trainee"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

export function StudentsPage() {
  const [includeAll, setIncludeAll] = useState(false)
  const students = useStudents(includeAll)
  const [search, setSearch] = useState("")
  const [group, setGroup] = useState<"all" | "Weekday" | "Weekend">("all")
  const [status, setStatus] = useState<"all" | "active" | "inactive">("all")
  const [dialogOpen, setDialogOpen] = useState(false)
  const [editing, setEditing] = useState<Student | null>(null)
  const [deleting, setDeleting] = useState<Student | null>(null)

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return (students.data ?? []).filter((s) => {
      if (group !== "all" && s.rollcall_group !== group) return false
      if (status === "active" && s.is_active !== 1) return false
      if (status === "inactive" && s.is_active !== 0) return false
      if (!q) return true
      return [s.name, s.phone, s.shop_name, s.email].some((v) => (v ?? "").toLowerCase().includes(q))
    })
  }, [students.data, search, group, status])

  return (
    <div className="space-y-6">
      {/* Toolbar: search + filters + add — one compact row, no dead space */}
      <div className="flex flex-col gap-3 rounded-lg border bg-card px-3 py-2.5 sm:flex-row sm:items-center">
        <div className="relative flex-1 sm:max-w-[176px]">
          <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Search name, phone, shop…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-9"
            aria-label="Search trainees"
          />
        </div>
        <div className="flex flex-1 flex-wrap items-center justify-between gap-2 sm:justify-end">
          <div className="flex gap-2">
            <Select value={group} onValueChange={(v) => setGroup(v as typeof group)}>
              <SelectTrigger className="w-44">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All groups</SelectItem>
                <SelectItem value="Weekday">Weekday</SelectItem>
                <SelectItem value="Weekend">Weekend</SelectItem>
              </SelectContent>
            </Select>
            <Select value={status} onValueChange={(v) => setStatus(v as typeof status)}>
              <SelectTrigger className="w-44">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="inactive">Inactive</SelectItem>
                <SelectItem value="all">All status</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <Button
            variant={includeAll ? "default" : "outline"}
            size="sm"
            className="h-8 gap-1.5 text-xs"
            onClick={() => setIncludeAll((v) => !v)}
          >
            <Eye className="size-3.5" /> {includeAll ? "Active only" : "Show all"}
          </Button>
          <Button
            onClick={() => {
              setEditing(null)
              setDialogOpen(true)
            }}
          >
            <Plus /> Add trainee
          </Button>
        </div>
      </div>

      {/* Table */}
      <Card>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Trainee</TableHead>
                  <TableHead className="hidden md:table-cell">Group</TableHead>
                  <TableHead className="hidden lg:table-cell">Shop</TableHead>
                  <TableHead className="hidden xl:table-cell">Course progress</TableHead>
                  <TableHead className="hidden xl:table-cell">Practical</TableHead>
                  <TableHead className="hidden sm:table-cell">Status</TableHead>
                  <TableHead className="hidden md:table-cell">Role</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {students.isLoading ? (
                  Array.from({ length: 6 }).map((_, i) => (
                    <TableRow key={i}>
                      <TableCell colSpan={7}>
                        <Skeleton className="h-10 w-full" />
                      </TableCell>
                    </TableRow>
                  ))
                ) : filtered.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={7}>
                      <EmptyState icon={<Users className="size-4" />} title="No trainees found" hint={students.data?.length ? "Try adjusting the filters." : "Add your first trainee to get started."} />
                    </TableCell>
                  </TableRow>
                ) : (
                  filtered.map((s) => (
                    <TableRow key={s.id}>
                      <TableCell>
                        <div className="flex items-center gap-2.5">
                          <StudentAvatar name={s.name} photoPath={s.photo_path} className="size-8" />
                          <div className="min-w-0">
                            <Link
                              to={`/students/${s.id}`}
                              className="block truncate font-medium hover:text-primary hover:underline"
                            >
                              {s.name}
                            </Link>
                            <div className="truncate text-xs text-muted-foreground md:hidden">
                              {s.rollcall_group} • {s.phone ?? "—"}
                            </div>
                            <div className="hidden truncate text-xs text-muted-foreground md:block">{s.phone ?? "—"}</div>
                          </div>
                        </div>
                      </TableCell>
                      <TableCell className="hidden md:table-cell">
                        <Badge variant={s.rollcall_group === "Weekend" ? "warning" : "secondary"}>{s.rollcall_group}</Badge>
                      </TableCell>
                      <TableCell className="hidden max-w-40 truncate text-muted-foreground lg:table-cell">
                        {s.shop_name ?? "—"}
                      </TableCell>
                      <TableCell className="hidden xl:table-cell">
                        <div className="flex items-center gap-2">
                          <Progress value={pct(s.course_completed, s.total_course)} className="h-1.5 w-24" />
                          <span className="text-xs tabular-nums text-muted-foreground">
                            {s.course_completed}/{s.total_course}
                          </span>
                        </div>
                      </TableCell>
                      <TableCell className="hidden xl:table-cell">
                        <div className="flex items-center gap-2">
                          <Progress value={pct(s.practical_completed, s.total_practical)} className="h-1.5 w-24" />
                          <span className="text-xs tabular-nums text-muted-foreground">
                            {s.practical_completed}/{s.total_practical}
                          </span>
                        </div>
                      </TableCell>
                      <TableCell className="hidden sm:table-cell">
                        <Badge variant={s.is_active === 1 ? "success" : "muted"}>
                          {s.is_active === 1 ? "Active" : "Inactive"}
                        </Badge>
                      </TableCell>
                      <TableCell className="hidden md:table-cell">
                        {s.role === "online" ? (
                          <Badge variant="warning">Online</Badge>
                        ) : (
                          <span className="text-xs text-muted-foreground">Student</span>
                        )}
                      </TableCell>
                      <TableCell className="text-right">
                        <div className="flex justify-end gap-1">
                          <Button variant="ghost" size="icon" asChild aria-label={`View ${s.name}`}>
                            <Link to={`/students/${s.id}`}>
                              <Eye />
                            </Link>
                          </Button>
                          <Button
                            variant="ghost"
                            size="icon"
                            aria-label={`Edit ${s.name}`}
                            onClick={() => {
                              setEditing(s)
                              setDialogOpen(true)
                            }}
                          >
                            <Pencil />
                          </Button>
                          <Button
                            variant="ghost"
                            size="icon"
                            aria-label={`Delete ${s.name}`}
                            className="text-destructive hover:bg-destructive/10 hover:text-destructive"
                            onClick={() => setDeleting(s)}
                          >
                            <Trash2 />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <StudentDialog open={dialogOpen} onOpenChange={setDialogOpen} student={editing} />
      <DeleteDialog student={deleting} onOpenChange={(o) => !o && setDeleting(null)} />
    </div>
  )
}
