import { useMemo, useState } from "react"
import { Link } from "react-router-dom"
import { CalendarDays, CalendarCheck, Check, Clock, ClipboardList, X } from "lucide-react"
import { useMarkRollcall, useToday } from "@/features/rollcall/api"
import { useSettings } from "@/features/admin/api"
import { RollCallCalendar } from "@/features/rollcall/components/RollCallCalendar"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Progress } from "@/components/ui/progress"
import { Skeleton } from "@/components/ui/skeleton"
import { Tabs, TabsContent, UnderlineTabsList as TabsList, UnderlineTabsTrigger as TabsTrigger } from "@/components/ui/tabs"
import { StudentAvatar } from "@/components/ui/avatar"
import { cn, pct } from "@/lib/utils"
import { useTimeMachine } from "@/lib/timemachine"

type MarkStatus = "Present" | "Absent" | "Late"

const MARK_STYLES: Record<MarkStatus, { icon: typeof Check; active: string; inactive: string }> = {
  Present: {
    icon: Check,
    active: "bg-brand-teal text-white border-brand-teal shadow-sm",
    inactive: "border-brand-teal/40 text-brand-teal hover:bg-brand-teal-soft dark:border-brand-teal/50 dark:text-brand-teal dark:hover:bg-brand-teal/15",
  },
  Late: {
    icon: Clock,
    active: "bg-brand-orange text-white border-brand-orange shadow-sm",
    inactive: "border-brand-orange/40 text-brand-orange hover:bg-brand-orange-soft dark:border-brand-orange/50 dark:text-brand-orange dark:hover:bg-brand-orange/15",
  },
  Absent: {
    icon: X,
    active: "bg-brand-red text-white border-brand-red shadow-sm",
    inactive: "border-brand-red/40 text-brand-red hover:bg-brand-red-soft dark:border-brand-red/50 dark:text-brand-red dark:hover:bg-brand-red/15",
  },
}

export function RollCallPage() {
  const today = useToday()
  const mark = useMarkRollcall()
  const { date: tmDate } = useTimeMachine()
  const { data: settings } = useSettings()
  const showLessons = Boolean(settings?.show_rollcall_lessons)
  const [marks, setMarks] = useState<Record<number, MarkStatus>>({})

  const students = today.data?.students ?? []


  const markedCount = Object.keys(marks).length
  const presentCount = Object.values(marks).filter((m) => m === "Present").length
  const progress = pct(markedCount, students.length)

  const pending = useMemo(() => students.filter((s) => !marks[s.student_id]), [students, marks])

  const setMark = (studentId: number, status: MarkStatus) => {
    if (mark.isPending) return // no double-submit
    setMarks((m) => ({ ...m, [studentId]: status }))
    mark.mutate({ student_id: studentId, status, date: tmDate })
  }

  if (today.isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-8 w-56" />
        <div className="grid gap-4 md:grid-cols-3">
          {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-24" />)}
        </div>
        {Array.from({ length: 5 }).map((_, i) => <Skeleton key={i} className="h-16" />)}
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <Tabs defaultValue="today">
        <TabsList>
          <TabsTrigger value="today" className="gap-1.5"><ClipboardList className="size-3.5" /> Today</TabsTrigger>
          <TabsTrigger value="calendar" className="gap-1.5"><CalendarDays className="size-3.5" /> Calendar</TabsTrigger>
        </TabsList>

        <TabsContent value="today" className="mt-4 space-y-6">
      {/* Summary */}
      <div className="grid grid-cols-3 gap-3">
        <Card className="gap-2 p-4">
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <ClipboardList className="size-4" /> Marked
          </div>
          <div className="text-2xl font-semibold tabular-nums tracking-tight">
            {markedCount}<span className="text-sm font-normal text-muted-foreground">/{students.length}</span>
          </div>
        </Card>
        <Card className="gap-2 p-4">
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <Check className="size-4 text-emerald-600" /> Present
          </div>
          <div className="text-2xl font-semibold tabular-nums tracking-tight">{presentCount}</div>
        </Card>
        <Card className="gap-2 p-4">
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            <CalendarCheck className="size-4" /> Progress
          </div>
          <div className="flex items-center gap-2">
            <Progress value={progress} className="h-1.5 flex-1" />
            <span className="text-sm tabular-nums text-muted-foreground">{progress}%</span>
          </div>
        </Card>
      </div>

      {/* Pending quick bar */}
      {pending.length > 0 && (
        <Card>
          <CardHeader className="px-5 py-3">
            <CardTitle className="text-sm">Unmarked ({pending.length})</CardTitle>
          </CardHeader>
          <CardContent className="flex flex-wrap gap-2 px-5 pb-4">
            {pending.map((s) => (
              <div key={s.student_id} className="flex items-center gap-2 rounded-full border bg-muted/40 py-1 pr-1 pl-2 text-sm">
                <span className="font-medium">{s.student_name}</span>
                <Button size="sm" className="h-6 rounded-full px-2.5 text-xs" disabled={mark.isPending} onClick={() => setMark(s.student_id, "Present")}>
                  <Check className="size-3" /> Present
                </Button>
              </div>
            ))}
          </CardContent>
        </Card>
      )}

      {/* Roster */}
      <Tabs defaultValue="Weekday">
        <TabsList>
          <TabsTrigger value="Weekday">Weekday</TabsTrigger>
          <TabsTrigger value="Weekend">Weekend</TabsTrigger>
        </TabsList>
        {(["Weekday", "Weekend"] as const).map((group) => {
          const groupStudents = students.filter((s) => s.group === group)
          if (groupStudents.length === 0) {
            return (
              <TabsContent key={group} value={group}>
                <p className="py-10 text-center text-sm text-muted-foreground">No {group} trainees today.</p>
              </TabsContent>
            )
          }
          return (
            <TabsContent key={group} value={group} className="mt-4">
              <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                {groupStudents.map((s) => {
                  const current = marks[s.student_id]
                  const training = s.is_training
                  return (
                    <Card key={s.student_id} className={cn("p-4 transition-colors", current && "border-emerald-300 dark:border-emerald-700")}>
                      <div className="flex items-start gap-3">
                        <StudentAvatar name={s.student_name} />
                        <div className="min-w-0 flex-1">
                          <div className="flex items-center gap-2">
                            <Link to={`/students/${s.student_id}`} className="truncate font-medium hover:text-primary hover:underline">
                              {s.student_name}
                            </Link>
                            {!training && <Badge variant="secondary">Rest day</Badge>}
                          </div>
                          <div className="truncate text-xs text-muted-foreground">{s.phone ?? "—"}</div>
                          {training && showLessons && (
                            <div className="mt-2 flex flex-col gap-1 text-xs">
                              <div className="truncate text-muted-foreground">
                                <span className="text-emerald-600 dark:text-emerald-400">Course:</span> {s.course?.title ?? "complete"}
                              </div>
                              <div className="truncate text-muted-foreground">
                                <span className="text-amber-600 dark:text-amber-400">Practical:</span> {s.practical?.title ?? "complete"}
                              </div>
                            </div>
                          )}
                        </div>
                        {current && (
                          <Badge variant={current === "Present" ? "success" : current === "Late" ? "warning" : "destructive"}>
                            {current}
                          </Badge>
                        )}
                      </div>

                      <div className="mt-3 grid grid-cols-3 gap-1.5">
                        {(Object.keys(MARK_STYLES) as MarkStatus[]).map((status) => {
                          const style = MARK_STYLES[status]
                          const Icon = style.icon
                          const isActive = current === status
                          return (
                            <button
                              key={status}
                              type="button"
                              disabled={mark.isPending}
                              onClick={() => setMark(s.student_id, status)}
                              className={cn(
                                "flex items-center justify-center gap-1.5 rounded-md border py-2 text-xs font-medium transition-colors",
                                isActive ? style.active : style.inactive,
                              )}
                            >
                              <Icon className="size-3.5" /> {status}
                            </button>
                          )
                        })}
                      </div>
                    </Card>
                  )
                })}
              </div>
            </TabsContent>
          )
        })}
      </Tabs>
      </TabsContent>

      <TabsContent value="calendar" className="mt-4">
        <RollCallCalendar />
      </TabsContent>
      </Tabs>
    </div>
  )
}
