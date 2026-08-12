import { useMemo, useState } from "react"
import { useSearchParams } from "react-router-dom"
import { BookOpen, Check, ChevronLeft, ChevronRight, ChevronDown, Clock, FileText, KeyRound, Loader2, LogOut, Pencil, PlayCircle, RotateCcw, Settings, User, Wrench } from "lucide-react"
import { useStudentChangePassword, useStudentLogin, useStudentLogout, useStudentMe, useStudentPortalData, useStudentSignup, useStudentUpdateProfile, useStudentUpdateProgress, type PortalProgressRow } from "@/features/student/api"
import { WatermarkViewer } from "@/features/student/WatermarkViewer"
import type { CurriculumItem, CurriculumMaterial } from "@/features/students/types"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Progress } from "@/components/ui/progress"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { StudentAvatar } from "@/components/ui/avatar"
import { cn, pct } from "@/lib/utils"

/* ============================== LOGIN ============================== */

function LoginView({ onSignup }: { onSignup: () => void }) {
  const login = useStudentLogin()
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [remember, setRemember] = useState(true)

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    if (!email.trim() || !password) return
    login.mutate({ email: email.trim(), password, remember })
  }

  return (
    <div className="flex min-h-svh items-center justify-center bg-muted/30 px-4">
      <div className="w-full max-w-sm rounded-2xl border bg-background p-6 shadow-sm">
        <div className="mb-5 flex flex-col items-center gap-2 text-center">
          <img src="/logo.png" alt="Apple Art" className="size-14 rounded-2xl object-cover" />
          <div>
            <h1 className="text-xl font-semibold tracking-tight">Student Portal</h1>
            <p className="text-xs text-muted-foreground">Apple Art Online Learning</p>
          </div>
        </div>
        <form onSubmit={submit} className="space-y-3">
          <div className="space-y-1.5">
            <Label htmlFor="sp-email">Email</Label>
            <Input id="sp-email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="you@example.com" autoComplete="username" />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="sp-pass">Password</Label>
            <Input id="sp-pass" type="password" value={password} onChange={(e) => setPassword(e.target.value)} placeholder="••••••" autoComplete="current-password" />
          </div>
          {login.isError && <p className="text-xs font-medium text-destructive">{login.error.message}</p>}
          <label className="flex cursor-pointer items-center gap-2 text-sm text-muted-foreground">
            <input type="checkbox" checked={remember} onChange={(e) => setRemember(e.target.checked)} className="size-4 accent-black" />
            Remember me
          </label>
          <Button type="submit" className="w-full rounded-full bg-black text-white hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200" disabled={login.isPending || !email.trim() || !password}>
            {login.isPending ? <Loader2 className="size-4 animate-spin" /> : "Sign in"}
          </Button>
        </form>
        <button type="button" onClick={onSignup} className="mt-3 w-full cursor-pointer text-center text-xs font-medium text-primary transition-colors hover:underline">
          အသစ် စာရင်းသွင်းမယ် — Create account →
        </button>
      </div>
    </div>
  )
}

/* ============================== SIGNUP ============================== */

function SignupView({ onLogin }: { onLogin: () => void }) {
  const signup = useStudentSignup()
  const [name, setName] = useState("")
  const [email, setEmail] = useState("")
  const [phone, setPhone] = useState("")
  const [password, setPassword] = useState("")

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    if (!name.trim() || !email.trim() || password.length < 6) return
    signup.mutate({ name: name.trim(), email: email.trim(), phone: phone.trim(), password })
  }

  return (
    <div className="flex min-h-svh items-center justify-center bg-muted/30 px-4">
      <div className="w-full max-w-sm rounded-2xl border bg-background p-6 shadow-sm">
        <div className="mb-5 flex flex-col items-center gap-2 text-center">
          <img src="/logo.png" alt="Apple Art" className="size-14 rounded-2xl object-cover" />
          <div>
            <h1 className="text-xl font-semibold tracking-tight">Create account</h1>
            <p className="text-xs text-muted-foreground">Apple Art Online Learning — အခမဲ့ စာရင်းသွင်းပါ</p>
          </div>
        </div>
        <form onSubmit={submit} className="space-y-3">
          <div className="space-y-1.5">
            <Label htmlFor="su-name">Name</Label>
            <Input id="su-name" value={name} onChange={(e) => setName(e.target.value)} placeholder="သင့်နာမည်" />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="su-email">Email</Label>
            <Input id="su-email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="you@example.com" />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="su-phone">Phone</Label>
            <Input id="su-phone" value={phone} onChange={(e) => setPhone(e.target.value)} placeholder="09xxxxxxxxx" />
          </div>
          <div className="space-y-1.5">
            <Label htmlFor="su-pass">Password (min 6)</Label>
            <Input id="su-pass" type="password" value={password} onChange={(e) => setPassword(e.target.value)} placeholder="••••••" />
          </div>
          {signup.isError && <p className="text-xs font-medium text-destructive">{signup.error.message}</p>}
          <Button type="submit" className="w-full rounded-full bg-black text-white hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200" disabled={signup.isPending || !name.trim() || !email.trim() || password.length < 6}>
            {signup.isPending ? <Loader2 className="size-4 animate-spin" /> : "Create account"}
          </Button>
        </form>
        <button type="button" onClick={onLogin} className="mt-3 w-full cursor-pointer text-center text-xs font-medium text-muted-foreground transition-colors hover:text-primary">
          ← အကောင့်ရှိပြီးသား — Sign in
        </button>
      </div>
    </div>
  )
}

/* ============================== PROFILE ============================== */

function ProfileView({ student }: { student: PortalStudentLike }) {
  const save = useStudentUpdateProfile()
  const [editing, setEditing] = useState(false)
  const [name, setName] = useState(student.name)
  const [email, setEmail] = useState(student.email ?? "")
  const [phone, setPhone] = useState(student.phone ?? "")
  const [done, setDone] = useState(false)

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    setDone(false)
    save.mutate(
      { name: name.trim(), email: email.trim(), phone: phone.trim() },
      {
        onSuccess: () => {
          setDone(true)
          setEditing(false)
          window.setTimeout(() => setDone(false), 2500)
        },
      },
    )
  }

  return (
    <div className="mx-auto w-full max-w-md px-4 py-6">
      <div className="rounded-2xl border bg-background p-6 shadow-sm">
        <div className="mb-4 flex items-center justify-between gap-3">
          <div className="flex items-center gap-3">
            <span className="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
              <User className="size-5" />
            </span>
            <div>
              <h1 className="text-lg font-semibold tracking-tight">Profile</h1>
              <p className="text-xs text-muted-foreground">သင့်အကောင့်အချက်အလက်</p>
            </div>
          </div>
          {!editing && (
            <Button size="sm" variant="outline" className="gap-1.5" onClick={() => setEditing(true)}>
              <Pencil className="size-3.5" /> Edit
            </Button>
          )}
        </div>

        {!editing ? (
          <div className="space-y-3">
            <Row label="Name" value={student.name} />
            <Row label="Email" value={student.email ?? "—"} />
            <Row label="Phone" value={student.phone ?? "—"} />
            <Row label="Role" value="Online (e-learning)" />
          </div>
        ) : (
          <form onSubmit={submit} className="space-y-3">
            <div className="space-y-1.5">
              <Label htmlFor="pf-name">Name</Label>
              <Input id="pf-name" value={name} onChange={(e) => setName(e.target.value)} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="pf-email">Email</Label>
              <Input id="pf-email" type="email" value={email} onChange={(e) => setEmail(e.target.value)} />
            </div>
            <div className="space-y-1.5">
              <Label htmlFor="pf-phone">Phone</Label>
              <Input id="pf-phone" value={phone} onChange={(e) => setPhone(e.target.value)} />
            </div>
            {save.isError && <p className="text-xs font-medium text-destructive">{save.error.message}</p>}
            {done && <p className="text-xs font-semibold text-emerald-600">Saved ✓</p>}
            <div className="flex gap-2">
              <Button type="submit" className="flex-1" disabled={save.isPending || !name.trim() || !email.trim()}>
                {save.isPending ? <Loader2 className="size-4 animate-spin" /> : "Save profile"}
              </Button>
              <Button type="button" variant="outline" onClick={() => setEditing(false)}>
                Cancel
              </Button>
            </div>
          </form>
        )}
      </div>
    </div>
  )
}

function Row({ label, value }: { label: string; value: string }) {
  return (
    <div className="flex items-center justify-between gap-3 rounded-lg border bg-muted/20 px-3 py-2.5">
      <span className="text-xs text-muted-foreground">{label}</span>
      <span className="min-w-0 truncate text-sm font-medium">{value}</span>
    </div>
  )
}

/* ============================== SETTINGS (PASSWORD) ============================== */

function SettingsView({ student }: { student: PortalStudentLike }) {
  const change = useStudentChangePassword()
  const [showForm, setShowForm] = useState(false)
  const [current, setCurrent] = useState("")
  const [next, setNext] = useState("")
  const [confirm, setConfirm] = useState("")
  const [done, setDone] = useState(false)

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    if (next !== confirm) return
    setDone(false)
    change.mutate(
      { current_password: current, new_password: next },
      {
        onSuccess: () => {
          setDone(true)
          setShowForm(false)
          setCurrent("")
          setNext("")
          setConfirm("")
          window.setTimeout(() => setDone(false), 2500)
        },
      },
    )
  }

  return (
    <div className="mx-auto w-full max-w-md px-4 py-6">
      <div className="rounded-2xl border bg-background p-6 shadow-sm">
        <div className="mb-4 flex items-center gap-3">
          <span className="flex size-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <Settings className="size-5" />
          </span>
          <div>
            <h1 className="text-lg font-semibold tracking-tight">Settings</h1>
            <p className="text-xs text-muted-foreground">အကောင့် လုံခြုံရေး</p>
          </div>
        </div>

        <div className="space-y-3">
          <Row label="Account" value={student.email ?? "—"} />
          <Row label="Password" value="••••••••" />
          {!showForm ? (
            <Button className="w-full gap-1.5" onClick={() => setShowForm(true)}>
              <KeyRound className="size-4" /> Change Password
            </Button>
          ) : (
            <form onSubmit={submit} className="space-y-3 rounded-xl border p-4">
              <div className="space-y-1.5">
                <Label htmlFor="st-current">Current password</Label>
                <Input id="st-current" type="password" value={current} onChange={(e) => setCurrent(e.target.value)} autoFocus />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="st-new">New password (min 6)</Label>
                <Input id="st-new" type="password" value={next} onChange={(e) => setNext(e.target.value)} />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="st-confirm">Confirm new password</Label>
                <Input id="st-confirm" type="password" value={confirm} onChange={(e) => setConfirm(e.target.value)} />
              </div>
              {next !== confirm && next !== "" && <p className="text-xs font-medium text-destructive">Passwords do not match</p>}
              {change.isError && <p className="text-xs font-medium text-destructive">{change.error.message}</p>}
              {done && <p className="text-xs font-semibold text-emerald-600">Password changed ✓</p>}
              <div className="flex gap-2">
                <Button type="submit" className="flex-1" disabled={change.isPending || !current || next.length < 6 || next !== confirm}>
                  {change.isPending ? <Loader2 className="size-4 animate-spin" /> : "Save new password"}
                </Button>
                <Button type="button" variant="outline" onClick={() => setShowForm(false)}>
                  Cancel
                </Button>
              </div>
            </form>
          )}
        </div>
      </div>
    </div>
  )
}

interface PortalStudentLike {
  name: string
  email: string | null
  phone: string | null
}

/* ============================== HELPERS ============================== */

const doneSet = (progress: PortalProgressRow[]) =>
  new Set(progress.filter((p) => p.detail_idx == null && p.status === "Completed").map((p) => p.item_id))

const inProgressSet = (progress: PortalProgressRow[]) =>
  new Set(progress.filter((p) => p.status !== "Pending" && !(p.detail_idx == null && p.status === "Completed")).map((p) => p.item_id))

const stepDoneMap = (progress: PortalProgressRow[]) => {
  const m = new Map<number, Set<number>>()
  for (const p of progress) {
    if (p.detail_idx == null || p.status !== "Completed") continue
    if (!m.has(p.item_id)) m.set(p.item_id, new Set())
    m.get(p.item_id)!.add(p.detail_idx)
  }
  return m
}

/* ============================== LESSON PLAYER ============================== */

function LessonView({
  item,
  index,
  total,
  studentId,
  progress,
  onBack,
  onNext,
  onPrev,
  onOpenMaterial,
}: {
  item: CurriculumItem
  index: number
  total: number
  studentId: number
  progress: PortalProgressRow[]
  onBack: () => void
  onNext: () => void
  onPrev: () => void
  onOpenMaterial: (m: CurriculumMaterial) => void
}) {
  const update = useStudentUpdateProgress()
  const [busy, setBusy] = useState(false)

  const completed = progress.some((p) => p.item_id === item.id && p.detail_idx == null && p.status === "Completed")
  const steps = (item.details ?? "").split(/\r?\n/).filter(Boolean)
  const hasDetails = steps.length > 0
  const doneSteps = stepDoneMap(progress).get(item.id) ?? new Set<number>()
  const allStepsDone = hasDetails && steps.every((_, i) => doneSteps.has(i + 1))
  const canComplete = !hasDetails || completed || allStepsDone

  const toggleComplete = async () => {
    setBusy(true)
    try {
      await update.mutateAsync({ student_id: studentId, item_id: item.id, status: completed ? "Pending" : "Completed" })
    } finally {
      setBusy(false)
    }
  }
  const toggleStep = async (idx: number) => {
    setBusy(true)
    try {
      await update.mutateAsync({ student_id: studentId, item_id: item.id, status: doneSteps.has(idx) ? "Pending" : "Completed", detail_idx: idx })
    } finally {
      setBusy(false)
    }
  }

  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-5">
      <button type="button" onClick={onBack} className="mb-3 flex cursor-pointer items-center gap-1 text-xs font-medium text-muted-foreground transition-colors hover:text-primary">
        <ChevronLeft className="size-4" /> Back to course
      </button>

      <div className="rounded-2xl border bg-background shadow-sm">
        {/* Lesson header */}
        <div className="border-b p-5">
          <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
            Lesson {index} / {total} · {item.category}
          </p>
          <h1 className="mt-1 text-xl font-semibold tracking-tight">{item.title}</h1>
          {item.practice ? <p className="mt-1.5 text-sm text-muted-foreground">{item.practice}</p> : null}
        </div>

        {/* Content */}
        <div className="space-y-4 p-5">
          {/* Material CTA */}
          {(item.materials?.length ?? 0) > 0 && (
            <button
              type="button"
              onClick={() => onOpenMaterial(item.materials![0])}
              className="flex w-full cursor-pointer items-center gap-3 rounded-2xl bg-black p-4 text-left text-white transition-colors hover:bg-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800"
            >
              <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-white/10">
                <FileText className="size-5" />
              </span>
              <span className="min-w-0 flex-1">
                <span className="block text-sm font-semibold">
                  {item.materials!.length > 1 ? `${item.materials!.length} course materials` : "Course material"}
                </span>
                <span className="block truncate text-xs text-zinc-400">{item.materials![0].file_name}</span>
              </span>
              <span className="text-xs font-semibold">Open →</span>
            </button>
          )}

          {/* Detail steps */}
          {hasDetails && (
            <div className="rounded-xl border p-4">
              <p className="mb-2 text-xs font-semibold text-muted-foreground">Detail steps — တစ်ဆင့်ချင်း လုပ်ပြီးမှ Mark complete</p>
              <div className="space-y-1.5">
                {steps.map((step, i) => {
                  const idx = i + 1
                  const done = doneSteps.has(idx)
                  return (
                    <button
                      key={idx}
                      type="button"
                      onClick={() => toggleStep(idx)}
                      disabled={busy}
                      className={cn(
                        "flex w-full cursor-pointer items-start gap-2.5 rounded-lg px-2 py-2 text-left text-sm transition-colors disabled:opacity-60",
                        done ? "bg-emerald-50 dark:bg-emerald-500/10" : "hover:bg-accent",
                      )}
                    >
                      <span
                        className={cn(
                          "mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border-2 text-[10px] font-bold",
                          done ? "border-emerald-500 bg-emerald-500 text-white" : "border-muted-foreground/40 text-transparent",
                        )}
                      >
                        ✓
                      </span>
                      <span className={cn("min-w-0", done ? "text-foreground" : "text-muted-foreground")}>{step}</span>
                    </button>
                  )
                })}
              </div>
            </div>
          )}

          {/* No content placeholder */}
          {!hasDetails && (item.materials?.length ?? 0) === 0 && (
            <div className="flex flex-col items-center gap-2 rounded-xl border border-dashed p-10 text-center">
              <BookOpen className="size-8 text-muted-foreground/50" />
              <p className="text-sm text-muted-foreground">ဒီ lesson အတွက် လေ့လာစရာ ဆရာက မထည့်ရသေးပါ။</p>
            </div>
          )}
        </div>

        {/* Actions */}
        <div className="flex flex-wrap items-center justify-between gap-3 border-t p-5">
          <Button variant="outline" size="sm" onClick={onPrev} disabled={index <= 1} className="gap-1">
            <ChevronLeft className="size-4" /> Prev
          </Button>
          <Button
            size="lg"
            className={cn("min-w-44 gap-2", completed ? "bg-muted text-muted-foreground hover:bg-muted" : "bg-emerald-500 text-white hover:bg-emerald-600")}
            disabled={busy || (!completed && !canComplete)}
            title={!completed && !canComplete ? `Steps ${doneSteps.size}/${steps.length} ပြည့်မှ Mark complete ရမယ်` : undefined}
            onClick={() => void toggleComplete()}
          >
            {busy ? (
              <Loader2 className="size-4 animate-spin" />
            ) : completed ? (
              <>
                <RotateCcw className="size-4" /> Completed — undo
              </>
            ) : (
              <>
                <Check className="size-4" /> Mark as complete
              </>
            )}
          </Button>
          <Button size="sm" variant="outline" onClick={onNext} disabled={index >= total} className="gap-1">
            Next <ChevronRight className="size-4" />
          </Button>
        </div>
      </div>
    </div>
  )
}

/* ============================== COURSE VIEW ============================== */

function CourseView({
  type,
  items,
  progress,
  onBack,
  onOpenLesson,
}: {
  type: "Course" | "Practical"
  items: CurriculumItem[]
  progress: PortalProgressRow[]
  onBack: () => void
  onOpenLesson: (item: CurriculumItem) => void
}) {
  const [openCats, setOpenCats] = useState<Set<string>>(() => new Set())
  const done = doneSet(progress)
  const inProg = inProgressSet(progress)

  const categories = useMemo(() => {
    const map = new Map<string, CurriculumItem[]>()
    for (const i of items) {
      if (!map.has(i.category)) map.set(i.category, [])
      map.get(i.category)!.push(i)
    }
    return [...map.entries()]
  }, [items])

  const doneCount = items.filter((i) => done.has(i.id)).length
  const Icon = type === "Course" ? BookOpen : Wrench

  const toggleCat = (c: string) =>
    setOpenCats((prev) => {
      const n = new Set(prev)
      if (n.has(c)) n.delete(c)
      else n.add(c)
      return n
    })

  return (
    <div className="mx-auto w-full max-w-3xl px-4 py-5">
      <button type="button" onClick={onBack} className="mb-3 flex cursor-pointer items-center gap-1 text-xs font-medium text-muted-foreground transition-colors hover:text-primary">
        <ChevronLeft className="size-4" /> All courses
      </button>

      {/* Course banner */}
      <div className="mb-5 overflow-hidden rounded-3xl border border-zinc-200/70 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
        <div className="flex items-center gap-3">
          <span className="flex size-11 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white">
            <Icon className="size-5" />
          </span>
          <div className="min-w-0 flex-1">
            <h1 className="text-xl font-semibold tracking-tight">{type} Course</h1>
            <p className="text-xs text-zinc-500">
              {doneCount}/{items.length} lessons completed · {categories.length} sections
            </p>
          </div>
        </div>
        <Progress value={pct(doneCount, items.length)} className="mt-3 h-2" />
      </div>

      {/* Curriculum accordion */}
      <div className="space-y-3">
        {categories.map(([c, cItems]) => {
          const cDone = cItems.filter((i) => done.has(i.id)).length
          const open = openCats.size === 0 || openCats.has(c)
          return (
            <div key={c} className="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
              <button type="button" onClick={() => toggleCat(c)} className="flex w-full cursor-pointer items-center gap-2.5 px-4 py-3 text-left">
                <ChevronDown className={cn("size-4 shrink-0 text-zinc-400 transition-transform", !open && "-rotate-90")} />
                <span className="min-w-0 flex-1 truncate text-sm font-semibold tracking-tight">{c}</span>
                <span className="text-xs tabular-nums text-zinc-500">
                  {cDone}/{cItems.length}
                </span>
                <Progress value={pct(cDone, cItems.length)} className="h-1 w-20 shrink-0" />
              </button>
              {open && (
                <ul className="border-t">
                  {cItems.map((item, idx) => {
                    const isDone = done.has(item.id)
                    const isProg = !isDone && inProg.has(item.id)
                    const hasMat = (item.materials?.length ?? 0) > 0
                    return (
                      <li key={item.id}>
                        <button
                          type="button"
                          onClick={() => onOpenLesson(item)}
                          className="flex w-full cursor-pointer items-center gap-3 border-b px-4 py-3 text-left transition-colors last:border-b-0 hover:bg-accent/50"
                        >
                          <span className="w-6 shrink-0 text-center text-xs tabular-nums text-muted-foreground/60">{idx + 1}</span>
                          <span
                            className={cn(
                              "flex size-6 shrink-0 items-center justify-center rounded-full",
                              isDone ? "bg-emerald-500 text-white" : isProg ? "bg-amber-500 text-white" : "bg-muted text-muted-foreground",
                            )}
                          >
                            {isDone ? <Check className="size-3.5" /> : isProg ? <Clock className="size-3.5" /> : <PlayCircle className="size-3.5" />}
                          </span>
                          <span className={cn("min-w-0 flex-1 truncate text-sm", isDone ? "font-medium text-foreground" : "text-muted-foreground")}>
                            {item.title}
                          </span>
                          {hasMat && <FileText className="size-4 shrink-0 text-rose-500" />}
                        </button>
                      </li>
                    )
                  })}
                </ul>
              )}
            </div>
          )
        })}
      </div>
    </div>
  )
}

/* ============================== HOME / DASHBOARD ============================== */

function HomeView({
  studentName,
  items,
  progress,
  onOpenCourse,
  onOpenLesson,
}: {
  studentName: string
  items: CurriculumItem[]
  progress: PortalProgressRow[]
  onOpenCourse: (t: "Course" | "Practical") => void
  onOpenLesson: (item: CurriculumItem) => void
}) {
  const done = doneSet(progress)
  const inProg = inProgressSet(progress)
  const doneCount = done.size
  const inProgCount = inProg.size
  const total = items.length

  // Continue learning = first in-progress lesson, else first uncompleted.
  const ordered = items
  const continueItem =
    ordered.find((i) => inProg.has(i.id) && !done.has(i.id)) ?? ordered.find((i) => !done.has(i.id))

  const courses = (["Course", "Practical"] as const).map((t) => {
    const list = items.filter((i) => i.type === t)
    const d = list.filter((i) => done.has(i.id)).length
    return { type: t, total: list.length, done: d, pct: pct(d, list.length) }
  })

  return (
    <div className="mx-auto w-full max-w-4xl px-4 py-6">
      {/* Greeting */}
      <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 className="text-3xl font-semibold tracking-tight">Welcome back, {studentName} 👋</h1>
          <p className="text-sm text-zinc-500">Apple Art Online Learning — ဆက်လေ့လာလိုက်ရအောင်</p>
        </div>
        <div className="flex items-center gap-3 rounded-2xl border border-zinc-200/70 bg-white px-4 py-2.5 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
          <div className="relative size-12">
            <svg viewBox="0 0 40 40" className="size-12 -rotate-90">
              <circle cx="20" cy="20" r="16" fill="none" strokeWidth="5" className="stroke-muted" />
              <circle cx="20" cy="20" r="16" fill="none" strokeWidth="5" strokeLinecap="round" className="stroke-emerald-500" strokeDasharray={`${(doneCount / Math.max(1, total)) * 100.53} 100.53`} />
            </svg>
            <span className="absolute inset-0 flex items-center justify-center text-[11px] font-bold tabular-nums">{pct(doneCount, total)}%</span>
          </div>
          <div className="text-xs leading-tight text-muted-foreground">
            <span className="block text-sm font-semibold text-foreground">{doneCount}/{total}</span>
            lessons done
            {inProgCount > 0 && <span className="block text-amber-600">· {inProgCount} in progress</span>}
          </div>
        </div>
      </div>

      {/* Continue learning */}
      {continueItem && (
        <button
          type="button"
          onClick={() => onOpenLesson(continueItem)}
          className="mb-6 block w-full cursor-pointer overflow-hidden rounded-2xl border border-zinc-200/70 bg-white p-5 text-left shadow-sm transition-all hover:shadow-md dark:border-zinc-800 dark:bg-zinc-950"
        >
          <p className="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Continue learning</p>
          <div className="mt-1.5 flex items-center justify-between gap-3">
            <div className="min-w-0">
              <p className="truncate text-base font-semibold tracking-tight">{continueItem.title}</p>
              <p className="text-xs text-zinc-500">{continueItem.category} · {continueItem.type}</p>
            </div>
            <span className="flex shrink-0 items-center gap-1 rounded-full bg-black px-3.5 py-1.5 text-xs font-semibold text-white dark:bg-white dark:text-black">
              Continue <ChevronRight className="size-3.5" />
            </span>
          </div>
        </button>
      )}

      {/* My courses */}
      <h2 className="mb-3 text-sm font-semibold text-muted-foreground">My courses</h2>
      <div className="grid gap-4 sm:grid-cols-2">
        {courses.map((c) => {
          const Icon = c.type === "Course" ? BookOpen : Wrench
          return (
            <button
              key={c.type}
              type="button"
              onClick={() => onOpenCourse(c.type)}
              className="group cursor-pointer overflow-hidden rounded-3xl border border-zinc-200/70 bg-white text-left shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-950"
            >
              <div className="flex items-center gap-3 p-5">
                <span className="flex size-11 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white">
                  <Icon className="size-5" />
                </span>
                <div className="min-w-0 flex-1">
                  <h3 className="font-semibold tracking-tight">{c.type} Course</h3>
                  <p className="text-xs text-zinc-500">{c.done}/{c.total} lessons completed</p>
                </div>
                <ChevronRight className="size-5 text-zinc-400 transition-transform group-hover:translate-x-0.5" />
              </div>
              <div className="px-5 pb-4">
                <Progress value={c.pct} className="h-1.5" />
                <p className="mt-1.5 text-right text-xs font-semibold tabular-nums text-zinc-500">{c.pct}%</p>
              </div>
            </button>
          )
        })}
      </div>
    </div>
  )
}

/* ============================== PORTAL PAGE ============================== */

export function StudentPortalPage() {
  const { data: student, isLoading: meLoading } = useStudentMe()
  const [searchParams] = useSearchParams()
  const { data: portal, isLoading: portalLoading } = useStudentPortalData()
  const logout = useStudentLogout()
  const [authMode, setAuthMode] = useState<"login" | "signup">(searchParams.get("signup") === "1" ? "signup" : "login")
  const [confirmLogout, setConfirmLogout] = useState(false)
  const [route, setRoute] = useState<{ name: "home" } | { name: "course"; type: "Course" | "Practical" } | { name: "lesson"; itemId: number } | { name: "profile" } | { name: "settings" }>({ name: "home" })
  const [viewer, setViewer] = useState<CurriculumMaterial | null>(null)

  const items = portal?.curriculum ?? []
  const progress = portal?.progress ?? []

  const currentItem = route.name === "lesson" ? items.find((i) => i.id === route.itemId) : undefined

  const openLesson = (item: CurriculumItem) => setRoute({ name: "lesson", itemId: item.id })
  const openCourse = (t: "Course" | "Practical") => setRoute({ name: "course", type: t })

  // Lesson ordering within the same type for prev/next.
  const lessonNav = useMemo(() => {
    if (route.name !== "lesson" || !currentItem) return null
    const list = items.filter((i) => i.type === currentItem.type)
    const idx = list.findIndex((i) => i.id === currentItem.id)
    return { list, index: idx + 1, total: list.length, prev: idx > 0 ? list[idx - 1] : null, next: idx < list.length - 1 ? list[idx + 1] : null }
  }, [route, currentItem, items])

  if (meLoading) {
    return (
      <div className="flex min-h-svh items-center justify-center text-muted-foreground">
        <Loader2 className="size-5 animate-spin" />
      </div>
    )
  }
  if (!student) return authMode === "signup" ? <SignupView onLogin={() => setAuthMode("login")} /> : <LoginView onSignup={() => setAuthMode("signup")} />

  return (
    <div className="flex min-h-svh flex-col bg-muted/30">
      {/* Navbar */}
      <header className="sticky top-0 z-20 border-b border-zinc-200/70 bg-white/80 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/80">
        <div className="mx-auto flex h-14 max-w-4xl items-center justify-between gap-3 px-4">
          <button
            type="button"
            onClick={() => setRoute({ name: "home" })}
            className="flex min-w-0 cursor-pointer items-center gap-2.5"
          >
            <img src="/logo.png" alt="Apple Art" className="size-8 shrink-0 rounded-lg object-cover" />
            <span className="truncate text-sm font-semibold tracking-tight">Apple Art</span>
          </button>
          <div className="flex shrink-0 items-center gap-2">
            {route.name !== "home" && (
              <button type="button" onClick={() => setRoute({ name: "home" })} className="hidden cursor-pointer text-xs font-medium text-muted-foreground transition-colors hover:text-primary sm:block">
                Home
              </button>
            )}
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="ghost" className="h-9 gap-2 px-2">
                  <StudentAvatar name={student.name} className="size-7" />
                  <span className="hidden max-w-28 truncate text-sm font-medium sm:inline">{student.name}</span>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-48">
                <DropdownMenuLabel>
                  <div className="truncate text-sm font-medium">{student.name}</div>
                  <div className="truncate text-xs font-normal text-muted-foreground">{student.email}</div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem onClick={() => setRoute({ name: "profile" })}>
                  <User className="size-4" /> Profile
                </DropdownMenuItem>
                <DropdownMenuItem onClick={() => setRoute({ name: "settings" })}>
                  <Settings className="size-4" /> Settings
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem variant="destructive" onClick={() => setConfirmLogout(true)}>
                  <LogOut className="size-4" /> Sign out
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
        </div>
      </header>

      <main className="flex-1">
        {portalLoading && route.name !== "home" ? (
          <div className="mx-auto max-w-3xl space-y-2 px-4 py-6">
            {Array.from({ length: 5 }).map((_, i) => <div key={i} className="h-14 animate-pulse rounded-xl bg-muted" />)}
          </div>
        ) : route.name === "home" ? (
          <HomeView
            studentName={student.name}
            items={items}
            progress={progress}
            onOpenCourse={openCourse}
            onOpenLesson={openLesson}
          />
        ) : route.name === "course" ? (
          <CourseView
            type={route.type}
            items={items.filter((i) => i.type === route.type)}
            progress={progress}
            onBack={() => setRoute({ name: "home" })}
            onOpenLesson={openLesson}
          />
        ) : route.name === "profile" ? (
          <ProfileView student={student} />
        ) : route.name === "settings" ? (
          <SettingsView student={student} />
        ) : currentItem && lessonNav ? (
          <LessonView
            item={currentItem}
            index={lessonNav.index}
            total={lessonNav.total}
            studentId={student.id}
            progress={progress}
            onBack={() => setRoute({ name: "course", type: currentItem.type })}
            onPrev={() => lessonNav.prev && openLesson(lessonNav.prev)}
            onNext={() => lessonNav.next && openLesson(lessonNav.next)}
            onOpenMaterial={setViewer}
          />
        ) : (
          <HomeView
            studentName={student.name}
            items={items}
            progress={progress}
            onOpenCourse={openCourse}
            onOpenLesson={openLesson}
          />
        )}
      </main>

      <footer className="border-t py-4 text-center text-[11px] text-zinc-500">
        © 2026 Apple Art — Online Learning · i35 Apple Service
      </footer>

      {/* Logout confirm */}
      <Dialog open={confirmLogout} onOpenChange={setConfirmLogout}>
        <DialogContent className="sm:max-w-xs">
          <DialogHeader>
            <DialogTitle>Sign out?</DialogTitle>
            <DialogDescription>အကောင့်ကနေ ထွက်ချင်တာ သေချာပါသလား။</DialogDescription>
          </DialogHeader>
          <DialogFooter className="gap-2">
            <Button variant="outline" onClick={() => setConfirmLogout(false)}>Cancel</Button>
            <Button variant="destructive" onClick={() => logout.mutate()} disabled={logout.isPending}>
              {logout.isPending ? <Loader2 className="size-4 animate-spin" /> : <LogOut className="size-4" />} Sign out
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {viewer && <WatermarkViewer student={student} material={viewer} onClose={() => setViewer(null)} />}
    </div>
  )
}
