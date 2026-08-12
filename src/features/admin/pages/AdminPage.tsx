import { useMemo, useState } from "react"
import { Link } from "react-router-dom"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import {
  BookOpen,
  CalendarClock,
  GraduationCap,
  MoreHorizontal,
  Pencil,
  Plus,
  Save,
  Sparkles,
  Trash2,
  UserPlus,
  Users,
  Wrench,
} from "lucide-react"
import {
  useDeleteCurriculumItem,
  useDeleteTrainer,
  useRegisterUser,
  useSaveAiSettings,
  useSaveCurriculumItem,
  useSaveRollcallSettings,
  useSaveTrainer,
  useSettings,
  useTrainers,
  useUsers,
} from "@/features/admin/api"
import { useCurriculum, useStudents } from "@/features/students/api"
import { MaterialControls, MaterialChips } from "@/features/courses/components/MaterialControls"
import { useMe } from "@/features/auth/api"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Switch } from "@/components/ui/switch"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { Skeleton } from "@/components/ui/skeleton"
import { Tabs, TabsContent, UnderlineTabsList, UnderlineTabsTrigger } from "@/components/ui/tabs"
import { StudentAvatar } from "@/components/ui/avatar"
import { cn } from "@/lib/utils"
import { EmptyState } from "@/components/common/feedback"
import { THEMES, useTheme } from "@/lib/themes"

const DAYS = [
  { v: 1, label: "Mon" },
  { v: 2, label: "Tue" },
  { v: 3, label: "Wed" },
  { v: 4, label: "Thu" },
  { v: 5, label: "Fri" },
  { v: 6, label: "Sat" },
  { v: 0, label: "Sun" },
]

function GroupLabel({ days }: { days: number[] }) {
  const sorted = [...days].sort((a, b) => (a === 0 ? 7 : a) - (b === 0 ? 7 : b))
  const labels = sorted.map((d) => DAYS.find((x) => x.v === d)?.label ?? "?")
  return <span className="text-xs text-muted-foreground">({labels.join("-")})</span>
}

function TrainersTab() {
  const { data: trainers, isLoading } = useTrainers()
  const save = useSaveTrainer()
  const del = useDeleteTrainer()
  const [dialog, setDialog] = useState<{ id?: number; name: string; role: string } | null>(null)
  const [confirmDelete, setConfirmDelete] = useState<{ id: number; name: string } | null>(null)

  const submit = (fields: { id?: number; name: string; role: string }) => {
    save.mutate(fields, {
      onSuccess: () => {
        setDialog(null)
        if (fields.id == null) setConfirmDelete(null)
      },
    })
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <p className="text-sm text-muted-foreground">Authorized instructors and trainers</p>
        <Button onClick={() => setDialog({ name: "", role: "Instructor" })}>
          <UserPlus /> Add instructor
        </Button>
      </div>

      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        {isLoading ? (
          Array.from({ length: 4 }).map((_, i) => <Skeleton key={i} className="h-20" />)
        ) : (trainers ?? []).length === 0 ? (
          <p className="text-sm text-muted-foreground">No instructors yet.</p>
        ) : (
          (trainers ?? []).map((t) => (
            <Card key={t.id} className="p-4">
              <div className="flex items-center gap-3">
                <StudentAvatar name={t.name} photoPath={t.photo_path} />
                <div className="min-w-0 flex-1">
                  <div className="truncate font-medium">{t.name}</div>
                  <Badge variant="secondary" className="mt-0.5">{t.role}</Badge>
                </div>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline" size="icon" aria-label={`Actions for ${t.name}`}>
                      <MoreHorizontal />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem onClick={() => setDialog({ id: t.id, name: t.name, role: t.role })}>
                      <Pencil /> Edit
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem variant="destructive" onClick={() => setConfirmDelete({ id: t.id, name: t.name })}>
                      <Trash2 /> Delete
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </Card>
          ))
        )}
      </div>

      <Dialog open={dialog != null} onOpenChange={(o) => !o && setDialog(null)}>
        <DialogContent className="sm:max-w-sm">
          <DialogHeader>
            <DialogTitle>{dialog?.id ? "Edit instructor" : "Add instructor"}</DialogTitle>
            <DialogDescription>Name and role for the trainer.</DialogDescription>
          </DialogHeader>
          <div className="space-y-3">
            <div className="space-y-1.5">
              <Label htmlFor="trainer-name">Name *</Label>
              <Input id="trainer-name" value={dialog?.name ?? ""} onChange={(e) => setDialog((d) => d && { ...d, name: e.target.value })} />
            </div>
            <div className="space-y-1.5">
              <Label>Role</Label>
              <Select value={dialog?.role ?? "Instructor"} onValueChange={(v) => setDialog((d) => d && { ...d, role: v })}>
                <SelectTrigger className="w-full">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="Instructor">Instructor</SelectItem>
                  <SelectItem value="Trainer">Trainer</SelectItem>
                  <SelectItem value="Master Admin">Master Admin</SelectItem>
                </SelectContent>
              </Select>
            </div>
            {save.isError && <Alert variant="destructive"><AlertDescription>{save.error.message}</AlertDescription></Alert>}
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDialog(null)}>Cancel</Button>
            <Button disabled={save.isPending || !dialog?.name.trim()} onClick={() => dialog && submit(dialog)}>
              {save.isPending ? "Saving…" : "Save"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog open={confirmDelete != null} onOpenChange={(o) => !o && setConfirmDelete(null)}>
        <DialogContent className="sm:max-w-sm">
          <DialogHeader>
            <DialogTitle>Delete instructor?</DialogTitle>
            <DialogDescription>Remove <span className="font-medium text-foreground">{confirmDelete?.name}</span> from the trainer list.</DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" onClick={() => setConfirmDelete(null)}>Cancel</Button>
            <Button
              variant="destructive"
              disabled={del.isPending}
              onClick={() => confirmDelete && del.mutate(confirmDelete.id, { onSuccess: () => setConfirmDelete(null) })}
            >
              {del.isPending ? "Deleting…" : "Delete"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}

function RollcallSettingsTab() {
  const { data: settings, isLoading } = useSettings()
  const save = useSaveRollcallSettings()
  const [weekday, setWeekday] = useState<number[] | null>(null)
  const [weekend, setWeekend] = useState<number[] | null>(null)
  const [weekdayTime, setWeekdayTime] = useState<{ start: string; end: string } | null>(null)
  const [weekendTime, setWeekendTime] = useState<{ start: string; end: string } | null>(null)
  const [wdEnabled, setWdEnabled] = useState<boolean | null>(null)
  const [weEnabled, setWeEnabled] = useState<boolean | null>(null)
  const [showSchedule, setShowSchedule] = useState<boolean | null>(null)
  const [showLessons, setShowLessons] = useState<boolean | null>(null)

  const sched = settings?.rollcall_schedules
  const rcShowSchedule = showSchedule ?? settings?.show_today_schedule ?? false
  const rcShowLessons = showLessons ?? settings?.show_rollcall_lessons ?? false
  const wdDays = weekday ?? sched?.Weekday.days ?? []
  const weDays = weekend ?? sched?.Weekend.days ?? []
  const wdT = weekdayTime ?? { start: sched?.Weekday.start_time ?? "10:00", end: sched?.Weekday.end_time ?? "15:00" }
  const weT = weekendTime ?? { start: sched?.Weekend.start_time ?? "10:00", end: sched?.Weekend.end_time ?? "15:00" }
  const wdOn = wdEnabled ?? sched?.Weekday.enabled ?? true
  const weOn = weEnabled ?? sched?.Weekend.enabled ?? true

  const toggleDay = (arr: number[], setArr: (v: number[]) => void, d: number) => {
    setArr(arr.includes(d) ? arr.filter((x) => x !== d) : [...arr, d])
  }

  const submit = () => {
    save.mutate(
      {
        show_today_schedule: rcShowSchedule ? 1 : 0,
        show_rollcall_lessons: rcShowLessons ? 1 : 0,
        Weekday: { days: weekday ?? sched?.Weekday.days ?? [], start_time: wdT.start, end_time: wdT.end, enabled: wdOn },
        Weekend: { days: weekend ?? sched?.Weekend.days ?? [], start_time: weT.start, end_time: weT.end, enabled: weOn },
      },
      { onSuccess: () => { setWeekday(null); setWeekend(null); setWeekdayTime(null); setWeekendTime(null); setWdEnabled(null); setWeEnabled(null); setShowSchedule(null); setShowLessons(null) } },
    )
  }

  if (isLoading) return <Skeleton className="h-64 w-full" />

  return (
    <div className="space-y-4">
      <Card>
        <CardHeader className="px-5">
          <CardTitle className="flex items-center gap-2 text-sm">
            <CalendarClock className="size-4 text-primary" /> Roll Call lesson info
          </CardTitle>
        </CardHeader>
        <CardContent className="px-5 pt-0">
          <div className="flex items-center justify-between gap-3 rounded-md border bg-muted/40 px-3 py-2">
            <span className="text-xs font-medium">{rcShowLessons ? "Showing" : "Hidden"}</span>
            <Switch checked={rcShowLessons} onCheckedChange={setShowLessons} aria-label="Show roll call lesson info" />
          </div>
          <p className="mt-2 text-xs text-muted-foreground">
            Roll Call ထဲက student card မှာ "Course: / Practical:" (ဘာသင်မလဲ) ကို ပြဖို့/ဖျောက်ဖို့ — ON ဆိုရင် ပြမယ်၊ OFF ဆိုရင် ဖျောက်မယ်။
          </p>
        </CardContent>
      </Card>
      <div className="grid gap-4 lg:grid-cols-2">
      {(
        [
          ["Weekday students", wdDays, setWeekday, wdT, setWeekdayTime, wdOn, setWdEnabled],
          ["Weekend students", weDays, setWeekend, weT, setWeekendTime, weOn, setWeEnabled],
        ] as const
      ).map(([title, days, setDays, time, setTime, on, setOn]) => (
        <Card key={title} className={cn("transition-opacity", !on && "opacity-60")}>
          <CardHeader className="px-5">
            <CardTitle className="flex items-center gap-2 text-sm">
              <CalendarClock className="size-4 text-primary" /> {title} <GroupLabel days={days} />
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-3 px-5 pt-0">
            <div className="flex items-center justify-between gap-3 rounded-md border bg-muted/40 px-3 py-2">
              <span className="text-xs font-medium">{on ? "Enabled" : "Disabled"}</span>
              <Switch checked={on} onCheckedChange={(v) => setOn(v)} aria-label={`${title} schedule`} />
            </div>
            <div className="flex flex-wrap gap-1.5">
              {DAYS.map((d) => (
                <button
                  key={d.v}
                  type="button"
                  onClick={() => toggleDay(days, setDays as (v: number[]) => void, d.v)}
                  className={cn(
                    "rounded-md border px-2.5 py-1 text-xs font-medium transition-colors",
                    days.includes(d.v)
                      ? "border-primary bg-primary text-white"
                      : "border-border text-muted-foreground hover:bg-accent",
                  )}
                >
                  {d.label}
                </button>
              ))}
            </div>
            <div className="flex gap-2">
              <div className="flex-1 space-y-1.5">
                <Label>Start</Label>
                <Input type="time" value={time.start} onChange={(e) => setTime({ ...time, start: e.target.value })} />
              </div>
              <div className="flex-1 space-y-1.5">
                <Label>End</Label>
                <Input type="time" value={time.end} onChange={(e) => setTime({ ...time, end: e.target.value })} />
              </div>
            </div>
          </CardContent>
        </Card>
      ))}
      {save.isError && <Alert variant="destructive" className="lg:col-span-2"><AlertDescription>{save.error.message}</AlertDescription></Alert>}
      <div className="lg:col-span-2">
        <Button onClick={submit} disabled={save.isPending}>
          <Save /> {save.isPending ? "Saving…" : "Save schedule"}
        </Button>
      </div>
      </div>
    </div>
  )
}

const curriculumSchema = z.object({
  type: z.enum(["Course", "Practical"]),
  category: z.string().min(1, "Category is required"),
  title: z.string().min(1, "Title is required"),
  tags: z.string().optional(),
  practice: z.string().optional(),
  details: z.string().optional(),
})

function CurriculumTab() {
  const { data: items, isLoading } = useCurriculum()
  const save = useSaveCurriculumItem()
  const del = useDeleteCurriculumItem()
  const [dialog, setDialog] = useState<{ id?: number; type: "Course" | "Practical"; category: string; title: string; tags: string; practice: string; details: string } | null>(null)
  const [confirmDelete, setConfirmDelete] = useState<{ id: number; title: string } | null>(null)
  const [tagDraft, setTagDraft] = useState("")
  const [newCatMode, setNewCatMode] = useState(false)
  const {
    register,
    handleSubmit,
    watch,
    setValue,
    reset,
    formState: { errors },
  } = useForm<z.infer<typeof curriculumSchema>>({
    resolver: zodResolver(curriculumSchema),
    defaultValues: { type: "Course", category: "", title: "", tags: "", practice: "", details: "" },
  })

  const tagsValue = (watch("tags") ?? "").trim()
  const tagsList = tagsValue ? tagsValue.split(/\s+/).filter(Boolean) : []

  const watchType = watch("type")

  // Existing categories for the current type (Course / Practical) — dropdown options.
  const catOptions = useMemo(() => {
    const set = new Set<string>()
    for (const item of items ?? []) {
      if (item.type === watchType && item.category) set.add(item.category)
    }
    return [...set].sort((a, b) => a.localeCompare(b, "my"))
  }, [items, watchType])

  // All tags already used anywhere — for autocomplete suggestions.
  const existingTags = useMemo(() => {
    const s = new Set<string>()
    for (const item of items ?? []) {
      for (const t of (item.tags ?? "").split(/\s+/).filter(Boolean)) s.add(t)
    }
    return [...s].sort()
  }, [items])

  const tagSuggestions = useMemo(() => {
    const q = tagDraft.trim().toLowerCase()
    if (!q) return []
    return existingTags.filter((t) => t.includes(q) && !tagsList.includes(t)).slice(0, 8)
  }, [tagDraft, existingTags, tagsList])

  const addTag = (raw: string) => {
    const t = raw.trim().toLowerCase().replace(/\s+/g, "-")
    if (!t) return
    setTagDraft("")
    if (!tagsList.includes(t)) setValue("tags", [...tagsList, t].join(" "), { shouldDirty: true })
  }

  const removeTag = (t: string) => setValue("tags", tagsList.filter((x) => x !== t).join(" "), { shouldDirty: true })

  const openDialog = (item?: { id: number; type: "Course" | "Practical"; category: string; title: string; tags: string; practice: string | null; details: string | null }) => {
    setNewCatMode(false)
    setTagDraft("")
    reset(item
      ? { type: item.type, category: item.category, title: item.title, tags: item.tags ?? "", practice: item.practice ?? "", details: item.details ?? "" }
      : { type: "Course", category: "", title: "", tags: "", practice: "", details: "" })
    setDialog(item ? { id: item.id, type: item.type, category: item.category, title: item.title, tags: item.tags ?? "", practice: item.practice ?? "", details: item.details ?? "" } : { type: "Course", category: "", title: "", tags: "", practice: "", details: "" })
  }

  const onSubmit = (values: z.infer<typeof curriculumSchema>) => {
    // Normalize tags: lowercase, dedupe, max 8.
    const tags = Array.from(new Set((values.tags ?? "").trim().toLowerCase().split(/\s+/).filter(Boolean))).slice(0, 8).join(" ")
    save.mutate(
      { id: dialog?.id, type: values.type, category: values.category, title: values.title, tags, practice: values.practice ?? "", details: values.details ?? "" },
      { onSuccess: () => setDialog(null) },
    )
  }

  const grouped = new Map<string, NonNullable<typeof items>>()
  for (const item of items ?? []) {
    if (!grouped.has(item.category)) grouped.set(item.category, [])
    grouped.get(item.category)!.push(item)
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <p className="text-sm text-muted-foreground">Theory & practical modules</p>
        <Button onClick={() => openDialog()}>
          <Plus /> Add module
        </Button>
      </div>

      {isLoading ? (
        <Skeleton className="h-64 w-full" />
      ) : items && items.length > 0 ? (
        <div className="space-y-4">
          {[...grouped.entries()].map(([category, catItems]) => (
            <Card key={category}>
              <CardHeader className="px-5 py-3">
                <CardTitle className="text-sm">{category} <Badge variant="secondary" className="ml-1">{catItems.length}</Badge></CardTitle>
              </CardHeader>
              <CardContent className="px-3 pb-3">
                <ul className="divide-y rounded-lg border">
                  {catItems.map((item) => (
                    <li key={item.id} className="px-3 py-2">
                      <div className="flex items-start gap-2">
                        {item.type === "Course" ? <BookOpen className="mt-0.5 size-4 shrink-0 text-emerald-600" /> : <Wrench className="mt-0.5 size-4 shrink-0 text-amber-600" />}
                        <div className="min-w-0 flex-1">
                          <div className="flex items-center gap-2">
                            <span className="min-w-0 truncate text-sm font-medium">{item.title}</span>
                            <Badge variant="secondary" className="shrink-0">{item.type}</Badge>
                          </div>
                          {item.practice ? (
                            <p className="mt-0.5 truncate text-xs text-muted-foreground">{item.practice}</p>
                          ) : null}
                          {item.tags ? (
                            <span className="mt-1 flex flex-wrap gap-x-2.5 gap-y-0.5">
                              {item.tags.split(/\s+/).filter(Boolean).map((t) => (
                                <span key={t} className="text-[10px] font-medium leading-none text-muted-foreground/60">
                                  #{t}
                                </span>
                              ))}
                            </span>
                          ) : null}
                          {item.details ? (
                            <ol className="mt-1 space-y-0.5">
                              {item.details.split(/\r?\n/).filter(Boolean).map((step, i) => (
                                <li key={i} className="flex items-start gap-1.5 text-xs text-muted-foreground">
                                  <span className="mt-px shrink-0 font-semibold text-muted-foreground/60">({i + 1})</span>
                                  <span>{step}</span>
                                </li>
                              ))}
                            </ol>
                          ) : null}
                          <MaterialChips materials={item.materials} />
                        </div>
                        <div className="flex shrink-0 items-center gap-1">
                          <MaterialControls itemId={item.id} itemTitle={item.title} materials={item.materials} labeled />
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="outline" size="icon" aria-label={`Actions for ${item.title}`}>
                              <MoreHorizontal />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem onClick={() => openDialog(item)}>
                              <Pencil /> Edit
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem variant="destructive" onClick={() => setConfirmDelete({ id: item.id, title: item.title })}>
                              <Trash2 /> Delete
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                          </DropdownMenu>
                        </div>
                      </div>
                    </li>
                  ))}
                </ul>
              </CardContent>
            </Card>
          ))}
        </div>
      ) : (
        <p className="text-sm text-muted-foreground">No modules yet — add the first one.</p>
      )}

      <Dialog open={dialog != null} onOpenChange={(o) => !o && setDialog(null)}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>{dialog?.id ? "Edit module" : "Add module"}</DialogTitle>
            <DialogDescription>Curriculum modules appear in calendar and courses.</DialogDescription>
          </DialogHeader>
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-3">
            <div className="space-y-1.5">
              <Label>Type</Label>
              <Select
                value={watch("type")}
                onValueChange={(v) => {
                  setValue("type", v as "Course" | "Practical")
                  setValue("category", "")
                  setNewCatMode(false)
                }}
              >
                <SelectTrigger className="w-full"><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="Course">Course</SelectItem>
                  <SelectItem value="Practical">Practical</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="cur-category">Category *</Label>
              {newCatMode ? (
                <div className="flex gap-2">
                  <Input
                    id="cur-category"
                    placeholder="New category name"
                    autoFocus
                    value={watch("category")}
                    onChange={(e) => setValue("category", e.target.value, { shouldValidate: true })}
                  />
                  <Button type="button" variant="outline" size="sm" className="h-9 shrink-0" onClick={() => setNewCatMode(false)}>
                    Cancel
                  </Button>
                </div>
              ) : (
                <Select value={watch("category") || undefined} onValueChange={(v) => (v === "__add__" ? setNewCatMode(true) : setValue("category", v, { shouldValidate: true }))}>
                  <SelectTrigger id="cur-category" className="w-full">
                    <SelectValue placeholder="Select category" />
                  </SelectTrigger>
                  <SelectContent>
                    {catOptions.length === 0 && (
                      <p className="px-2 py-1.5 text-xs text-muted-foreground">No categories yet for {watchType}.</p>
                    )}
                    {catOptions.map((c) => (
                      <SelectItem key={c} value={c}>{c}</SelectItem>
                    ))}
                    <SelectItem value="__add__">＋ Add new category…</SelectItem>
                  </SelectContent>
                </Select>
              )}
              {errors.category && <p className="text-xs text-destructive">{errors.category.message}</p>}
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="cur-title">Title *</Label>
              <Input id="cur-title" placeholder="e.g. iPhone X All Spare Parts" {...register("title")} />
              {errors.title && <p className="text-xs text-destructive">{errors.title.message}</p>}
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="cur-tags">Tags</Label>
              {tagsList.length > 0 && (
                <div className="flex flex-wrap gap-1.5">
                  {tagsList.map((t) => (
                    <span key={t} className="inline-flex items-center gap-1 rounded-full border bg-muted/40 px-2 py-0.5 text-xs font-medium">
                      #{t}
                      <button type="button" onClick={() => removeTag(t)} aria-label={`Remove tag ${t}`} className="cursor-pointer text-muted-foreground transition-colors hover:text-destructive">×</button>
                    </span>
                  ))}
                </div>
              )}
              <div className="relative">
                <Input
                  id="cur-tags"
                  value={tagDraft}
                  onChange={(e) => setTagDraft(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === "Enter" || e.key === ",") { e.preventDefault(); addTag(tagDraft) }
                    if (e.key === "Backspace" && !tagDraft.trim() && tagsList.length) removeTag(tagsList[tagsList.length - 1])
                  }}
                  onBlur={() => { if (tagDraft.trim()) addTag(tagDraft) }}
                  placeholder="Type a tag and press Enter — e.g. battery, schematic"
                />
                {tagSuggestions.length > 0 && (
                  <div className="bg-popover text-popover-foreground absolute z-10 mt-1 w-full rounded-md border p-1 shadow-md">
                    {tagSuggestions.map((t) => (
                      <button
                        key={t}
                        type="button"
                        onMouseDown={(e) => { e.preventDefault(); addTag(t) }}
                        className="flex w-full cursor-pointer items-center gap-1 rounded px-2 py-1 text-left text-xs transition-colors hover:bg-accent"
                      >
                        <span className="text-muted-foreground">#</span>{t}
                      </button>
                    ))}
                  </div>
                )}
              </div>
              <p className="text-xs text-muted-foreground">Enter နှိပ်ပြီး ထည့်ပါ — ရှိပြီးသား tag တွေက suggestion လေးတွေ ပေါ်ပေးမယ်။</p>
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="cur-practice">Practice</Label>
              <Input id="cur-practice" placeholder="e.g. Multi Meter လက်တွေ့တိုင်းတာရန်" {...register("practice")} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="cur-details">Detail steps</Label>
              <textarea
                id="cur-details"
                rows={5}
                placeholder={"Line တစ်ကြောင်း = အဆင့်တစ်ခု\n(၁) စတဲ့ နံပါတ်တွေ မထည့်ပါနဲ့ — auto-number လုပ်ပေးပါမယ်"}
                className="border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                {...register("details")}
              />
              <p className="text-xs text-muted-foreground">Lesson ရဲ့ အသေးစိတ် အဆင့်တွေ — တစ်ကြောင်း တစ်ဆင့်နှုန်း ရေးပါ။</p>
            </div>
            {save.isError && <Alert variant="destructive"><AlertDescription>{save.error.message}</AlertDescription></Alert>}
            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => setDialog(null)}>Cancel</Button>
              <Button type="submit" disabled={save.isPending}>{save.isPending ? "Saving…" : "Save"}</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>

      <Dialog open={confirmDelete != null} onOpenChange={(o) => !o && setConfirmDelete(null)}>
        <DialogContent className="sm:max-w-sm">
          <DialogHeader>
            <DialogTitle>Delete module?</DialogTitle>
            <DialogDescription>
              Remove <span className="font-medium text-foreground">{confirmDelete?.title}</span> and all its progress records. This cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" onClick={() => setConfirmDelete(null)}>Cancel</Button>
            <Button
              variant="destructive"
              disabled={del.isPending}
              onClick={() => confirmDelete && del.mutate(confirmDelete.id, { onSuccess: () => setConfirmDelete(null) })}
            >
              {del.isPending ? "Deleting…" : "Delete"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}

function UsersTab() {
  const { data: users, isLoading } = useUsers()
  const { data: me } = useMe()
  const register = useRegisterUser()
  const [dialog, setDialog] = useState(false)
  const [form, setForm] = useState({ name: "", email: "", password: "", role: "user" })

  const submit = () => {
    register.mutate(form, {
      onSuccess: () => {
        setDialog(false)
        setForm({ name: "", email: "", password: "", role: "user" })
      },
    })
  }

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <p className="text-sm text-muted-foreground">Accounts that can sign in to the system</p>
        <Button onClick={() => setDialog(true)}>
          <UserPlus /> Add user
        </Button>
      </div>

      {isLoading ? (
        <Skeleton className="h-40 w-full" />
      ) : (users ?? []).length === 0 ? (
        <p className="text-sm text-muted-foreground">No users yet.</p>
      ) : (
        <Card>
          <CardContent className="p-0">
            <ul className="divide-y">
              {(users ?? []).map((u) => (
                <li key={u.id} className="flex items-center gap-3 px-4 py-3">
                  <span className="flex size-9 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary dark:bg-primary/20 dark:text-primary">
                    {u.name.slice(0, 2).toUpperCase()}
                  </span>
                  <div className="min-w-0 flex-1">
                    <div className="truncate text-sm font-medium">
                      {u.name}
                      {me?.id === u.id && <span className="ml-1.5 text-xs text-muted-foreground">(you)</span>}
                    </div>
                    <div className="truncate text-xs text-muted-foreground">{u.email}</div>
                  </div>
                  <Badge variant={u.role === "master_admin" ? "default" : u.role === "admin" ? "warning" : "secondary"}>{u.role}</Badge>
                </li>
              ))}
            </ul>
          </CardContent>
        </Card>
      )}

      <Dialog open={dialog} onOpenChange={setDialog}>
        <DialogContent className="sm:max-w-sm">
          <DialogHeader>
            <DialogTitle>Add user</DialogTitle>
            <DialogDescription>Create a login for a new staff member.</DialogDescription>
          </DialogHeader>
          <div className="space-y-3">
            <div className="space-y-1.5">
              <Label htmlFor="user-name">Name *</Label>
              <Input id="user-name" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="user-email">Email *</Label>
              <Input id="user-email" type="email" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="user-password">Password *</Label>
              <Input id="user-password" type="password" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
            </div>
            <div className="space-y-1.5">
              <Label>Role</Label>
              <Select value={form.role} onValueChange={(v) => setForm({ ...form, role: v })}>
                <SelectTrigger className="w-full"><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="user">User</SelectItem>
                  <SelectItem value="admin">Admin</SelectItem>
                  <SelectItem value="master_admin">Master Admin</SelectItem>
                </SelectContent>
              </Select>
            </div>
            {register.isError && <Alert variant="destructive"><AlertDescription>{register.error.message}</AlertDescription></Alert>}
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDialog(false)}>Cancel</Button>
            <Button onClick={submit} disabled={register.isPending || !form.name || !form.email || !form.password}>
              {register.isPending ? "Creating…" : "Create user"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}

function ThemeTab() {
  const { themeId, setThemeId } = useTheme()
  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <p className="text-sm text-muted-foreground">Pick the site accent color — applies across the whole app</p>
      </div>

      <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
        {THEMES.map((t) => {
          const active = t.id === themeId
          return (
            <button
              key={t.id}
              type="button"
              onClick={() => setThemeId(t.id)}
              aria-pressed={active}
              className={cn(
                "group flex flex-col items-center gap-2.5 rounded-xl border bg-card p-4 text-center transition-all",
                active ? "border-primary ring-2 ring-primary/30 shadow-sm" : "border-border hover:border-primary/40 hover:bg-accent/40",
              )}
            >
              <span className="flex items-center gap-1.5">
                <span className="size-7 rounded-full border border-black/10 shadow-sm" style={{ backgroundColor: t.primary }} />
                <span className="size-7 rounded-full border border-black/10 shadow-sm" style={{ backgroundColor: t.soft }} />
              </span>
              <span className="text-xs font-medium">{t.name}</span>
              {active && <span className="text-[10px] font-semibold text-primary">Active</span>}
            </button>
          )
        })}
      </div>

      <div className="rounded-lg border bg-muted/40 p-4 text-xs text-muted-foreground">
        Color plate: Ocean Blue · Teal · Sunset Orange · Coral Red · Royal Purple · Indigo
      </div>
    </div>
  )
}

function AiSettingsTab() {
  const { data: settings, isLoading } = useSettings()
  const save = useSaveAiSettings()
  const [key, setKey] = useState("")
  const [model, setModel] = useState("gpt-4o-mini")
  const [baseUrl, setBaseUrl] = useState("https://api.openai.com/v1")
  const [testResult, setTestResult] = useState<string | null>(null)
  const [testPending, setTestPending] = useState(false)

  if (isLoading) return <Skeleton className="h-64 w-full" />

  const submit = () => {
    save.mutate(
      { ai_api_key: key || (settings?.ai_api_key as string) || "", ai_model: model || "gpt-4o-mini", ai_base_url: baseUrl || "https://api.openai.com/v1" },
      { onSuccess: () => setTestResult("Saved ✓") },
    )
  }

  const test = async () => {
    setTestPending(true)
    setTestResult(null)
    const fd = new FormData()
    fd.append("message", "Reply with exactly: AI OK")
    const res = await fetch("/api/ai_chat.php", { method: "POST", body: fd })
    const data = await res.json().catch(() => null)
    setTestPending(false)
    setTestResult(data?.status === "success" ? "Connection OK — " + (data.reply ?? "") : "Error: " + (data?.message ?? res.status))
  }

  return (
    <div className="space-y-4">
      <Card>
        <CardHeader className="px-5">
          <CardTitle className="flex items-center gap-2 text-base">
            <Sparkles className="size-4 text-primary" /> AI Assistant & API
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-3 px-5 pt-0">
          <div className="space-y-1.5">
            <Label htmlFor="ai-key">API key</Label>
            <Input id="ai-key" type="password" placeholder={settings?.ai_api_key ? "•••••••• (saved — type to change)" : "sk-..."} value={key} onChange={(e) => setKey(e.target.value)} />
          </div>
          <div className="grid gap-3 sm:grid-cols-2">
            <div className="space-y-1.5">
              <Label htmlFor="ai-model">Model</Label>
              <Input id="ai-model" value={model || (settings?.ai_model as string) || "gpt-4o-mini"} onChange={(e) => setModel(e.target.value)} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="ai-base">Base URL</Label>
              <Input id="ai-base" value={baseUrl || (settings?.ai_base_url as string) || "https://api.openai.com/v1"} onChange={(e) => setBaseUrl(e.target.value)} />
            </div>
          </div>
          <p className="text-xs text-muted-foreground">
            OpenAI-compatible API — key ထည့်ပြီးရင် sidebar က Sparkles (✨) ခလုတ်နဲ့ AI assistant ကို သုံးလို့ရမယ်။
          </p>
          {testResult && <p className="text-xs font-medium text-muted-foreground">{testResult}</p>}
          <div className="flex gap-2">
            <Button onClick={submit}><Save /> Save settings</Button>
            <Button variant="outline" onClick={() => void test()} disabled={testPending}>
              {testPending ? "Testing…" : "Test connection"}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}

export function AdminPage() {
  const { data: me } = useMe()
  const students = useStudents()

  if (!me || (me.role !== "admin" && me.role !== "master_admin")) {
    return (
      <EmptyState icon={<Users className="size-4" />} title="Admin access required" hint="Only admin or master admin accounts can manage the system." className="py-20" />
    )
  }

  return (
    <div className="space-y-6">
      <Tabs defaultValue="trainers">
        <UnderlineTabsList className="w-full">
          <UnderlineTabsTrigger value="trainers">Instructors</UnderlineTabsTrigger>
          <UnderlineTabsTrigger value="trainees">Trainee controls</UnderlineTabsTrigger>
          <UnderlineTabsTrigger value="rollcall">Roll call settings</UnderlineTabsTrigger>
          <UnderlineTabsTrigger value="curriculum">Curriculum</UnderlineTabsTrigger>
          <UnderlineTabsTrigger value="users">Users</UnderlineTabsTrigger>
          <UnderlineTabsTrigger value="theme">Theme</UnderlineTabsTrigger>
          <UnderlineTabsTrigger value="ai">AI Assistant & API</UnderlineTabsTrigger>
        </UnderlineTabsList>

        <TabsContent value="trainers" className="mt-4">
          <TrainersTab />
        </TabsContent>
        <TabsContent value="theme" className="mt-4">
          <ThemeTab />
        </TabsContent>
        <TabsContent value="ai" className="mt-4">
          <AiSettingsTab />
        </TabsContent>
        <TabsContent value="trainees" className="mt-4">
          <Card>
            <CardHeader className="px-5">
              <CardTitle className="flex items-center gap-2 text-base">
                <GraduationCap className="size-4 text-primary" /> Global trainee management
              </CardTitle>
            </CardHeader>
            <CardContent className="px-5 pt-0">
              <p className="mb-3 text-sm text-muted-foreground">
                Add, edit and delete trainees from the <Link to="/students" className="text-primary hover:underline">Trainees page</Link>. Here&apos;s the current roster:
              </p>
              {students.isLoading ? (
                <Skeleton className="h-24 w-full" />
              ) : (
                <ul className="divide-y rounded-lg border">
                  {(students.data ?? []).map((s) => (
                    <li key={s.id} className="flex items-center gap-3 px-3 py-2">
                      <StudentAvatar name={s.name} photoPath={s.photo_path} className="size-7" />
                      <span className="min-w-0 flex-1 truncate text-sm">{s.name}</span>
                      <Badge variant={s.is_active === 1 ? "success" : "muted"}>{s.is_active === 1 ? "Active" : "Inactive"}</Badge>
                      <span className="text-xs tabular-nums text-muted-foreground">{s.course_completed}/{s.total_course}</span>
                    </li>
                  ))}
                </ul>
              )}
            </CardContent>
          </Card>
        </TabsContent>
        <TabsContent value="rollcall" className="mt-4">
          <RollcallSettingsTab />
        </TabsContent>
        <TabsContent value="curriculum" className="mt-4">
          <CurriculumTab />
        </TabsContent>
        <TabsContent value="users" className="mt-4">
          <UsersTab />
        </TabsContent>
      </Tabs>
    </div>
  )
}
