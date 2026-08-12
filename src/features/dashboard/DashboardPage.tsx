import { Link } from "react-router-dom"
import { ArrowRight, BookOpen, CalendarDays, GraduationCap, Users, Wrench } from "lucide-react"
import { useCurriculum, useStudents, useToday } from "@/features/students/api"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Progress } from "@/components/ui/progress"
import { Skeleton } from "@/components/ui/skeleton"
import { StudentAvatar } from "@/components/ui/avatar"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { pct } from "@/lib/utils"
import { IconBadge, type BrandColor } from "@/components/common/icon-badge"

function StatCard({ icon: Icon, label, value, hint, color = "primary" }: { icon: typeof Users; label: string; value: string | number; hint?: string; color?: BrandColor }) {
  return (
    <Card className="gap-3 py-5">
      <CardHeader className="px-5">
        <div className="flex items-center justify-between">
          <span className="text-sm text-muted-foreground">{label}</span>
          <IconBadge icon={Icon} color={color} />
        </div>
      </CardHeader>
      <CardContent className="px-5 pt-0">
        <div className="text-3xl font-semibold tabular-nums tracking-tight">{value}</div>
        {hint && <p className="mt-1 text-xs text-muted-foreground">{hint}</p>}
      </CardContent>
    </Card>
  )
}

export function DashboardPage() {
  const students = useStudents()
  const curriculum = useCurriculum()
  const today = useToday()

  const active = (students.data ?? []).filter((s) => s.is_active === 1)
  const courseTotal = (curriculum.data ?? []).filter((c) => c.type === "Course").length
  const practicalTotal = (curriculum.data ?? []).filter((c) => c.type === "Practical").length
  const todayLessons = today.data?.students ?? []
  const trainingCount = todayLessons.filter((s) => s.is_training).length

  return (
    <div className="space-y-6">

      {/* Stats — each stat gets its own brand color */}
      <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <StatCard icon={Users} label="Active trainees" value={active.length} hint="enrolled &amp; active" color="blue" />
        <StatCard icon={CalendarDays} label="Class today" value={trainingCount} hint={`of ${active.length} trainees`} color="teal" />
        <StatCard icon={BookOpen} label="Theory modules" value={courseTotal} color="purple" />
        <StatCard icon={Wrench} label="Practical modules" value={practicalTotal} color="orange" />
      </div>

      <div className="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        {/* Today's classes */}
        <Card>
          <CardHeader className="flex-row items-center justify-between px-5">
            <CardTitle className="flex items-center gap-2.5 text-base">
              <IconBadge icon={CalendarDays} color="teal" size="sm" /> Today&apos;s classes
            </CardTitle>
            <Link to="/calendar" className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline">
              Calendar <ArrowRight className="size-3" />
            </Link>
          </CardHeader>
          <CardContent className="px-0 pb-2">
            {today.isLoading ? (
              <div className="space-y-3 px-5">
                {Array.from({ length: 4 }).map((_, i) => (
                  <Skeleton key={i} className="h-14 w-full" />
                ))}
              </div>
            ) : today.data?.students.length ? (
              <div className="divide-y">
                {today.data.students.map((s) => (
                  <div key={s.student_id} className="flex flex-col gap-3 px-5 py-3 sm:flex-row sm:items-center">
                    <div className="flex min-w-0 flex-1 items-center gap-3">
                      <StudentAvatar name={s.student_name} />
                      <div className="min-w-0">
                        <div className="truncate text-sm font-medium">{s.student_name}</div>
                        <div className="text-xs text-muted-foreground">{s.group} • {s.phone ?? "—"}</div>
                      </div>
                    </div>
                    {s.is_training ? (
                      <div className="grid flex-1 grid-cols-2 gap-2">
                        <div className="rounded-lg border bg-muted/40 px-3 py-2">
                          <div className="text-[11px] font-medium uppercase tracking-wide text-emerald-600 dark:text-emerald-400">Course</div>
                          <div className="truncate text-sm font-medium" title={s.course?.title}>{s.course?.title ?? "🎓 complete"}</div>
                        </div>
                        <div className="rounded-lg border bg-muted/40 px-3 py-2">
                          <div className="text-[11px] font-medium uppercase tracking-wide text-amber-600 dark:text-amber-400">Practical</div>
                          <div className="truncate text-sm font-medium" title={s.practical?.title}>{s.practical?.title ?? "🎓 complete"}</div>
                        </div>
                      </div>
                    ) : (
                      <Badge variant="secondary" className="flex-1 justify-center py-1.5">Rest day</Badge>
                    )}
                  </div>
                ))}
              </div>
            ) : (
              <p className="px-5 text-sm text-muted-foreground">No students found.</p>
            )}
          </CardContent>
        </Card>

        {/* Progress overview */}
        <Card>
          <CardHeader className="px-5">
            <CardTitle className="flex items-center gap-2.5 text-base">
              <IconBadge icon={GraduationCap} color="blue" size="sm" /> Trainee progress
            </CardTitle>
          </CardHeader>
          <CardContent className="px-5 pt-0">
            {students.isLoading ? (
              <div className="space-y-4">
                {Array.from({ length: 4 }).map((_, i) => (
                  <Skeleton key={i} className="h-12 w-full" />
                ))}
              </div>
            ) : today.data?.students.length ? (
              <div className="space-y-4">
                {today.data.students.map((s) => (
                  <div key={s.student_id}>
                    <div className="mb-1 flex items-center justify-between text-sm">
                      <span className="truncate font-medium">{s.student_name}</span>
                      <span className="tabular-nums text-xs text-muted-foreground">
                        {s.course_done}/{s.course_total} · {pct(s.course_done, s.course_total)}%
                      </span>
                    </div>
                    <Progress value={pct(s.course_done, s.course_total)} />
                    <div className="mt-1 flex items-center justify-between text-xs text-muted-foreground">
                      <span>Practical</span>
                      <span className="tabular-nums">{s.practical_done}/{s.practical_total}</span>
                    </div>
                  </div>
                ))}
              </div>
            ) : null}
          </CardContent>
        </Card>
      </div>

      {/* Students table */}
      <Card>
        <CardHeader className="flex-row items-center justify-between px-5">
          <CardTitle className="text-base">Trainees</CardTitle>
          <Link to="/students" className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline">
            View all <ArrowRight className="size-3" />
          </Link>
        </CardHeader>
        <CardContent className="px-0 pb-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead className="hidden md:table-cell">Phone</TableHead>
                <TableHead className="hidden lg:table-cell">Group</TableHead>
                <TableHead className="hidden sm:table-cell">Joined</TableHead>
                <TableHead className="text-right">Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(students.data ?? []).slice(0, 6).map((s) => (
                <TableRow key={s.id}>
                  <TableCell>
                    <div className="flex items-center gap-3">
                      <StudentAvatar name={s.name} photoPath={s.photo_path} />
                      <div>
                        <div className="font-medium">{s.name}</div>
                        <div className="text-xs text-muted-foreground md:hidden">{s.phone ?? "—"}</div>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell className="hidden tabular-nums text-muted-foreground md:table-cell">{s.phone ?? "—"}</TableCell>
                  <TableCell className="hidden lg:table-cell"><Badge variant={s.rollcall_group === "Weekend" ? "warning" : "secondary"}>{s.rollcall_group}</Badge></TableCell>
                  <TableCell className="hidden text-muted-foreground sm:table-cell">{s.enrollment_date ?? "—"}</TableCell>
                  <TableCell className="text-right">
                    <Badge variant={s.is_active === 1 ? "success" : "muted"}>{s.is_active === 1 ? "Active" : "Inactive"}</Badge>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
          {students.data && students.data.length === 0 && (
            <p className="px-5 py-8 text-center text-sm text-muted-foreground">
              No trainees yet. <Link to="/students" className="text-primary hover:underline">Add your first trainee</Link>.
            </p>
          )}
        </CardContent>
      </Card>

      <div className="flex items-center gap-2 text-xs text-muted-foreground">
        <GraduationCap className="size-4 text-emerald-600" />
        Apple Art Academy — training from basic to advanced
      </div>
    </div>
  )
}
