import { useMemo, useState } from "react"
import { Contact, Mail, MapPin, Phone, Search, ShoppingBag, Store } from "lucide-react"
import { useStudents } from "@/features/students/api"
import { useStudentById } from "@/features/students/detail-api"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Skeleton } from "@/components/ui/skeleton"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { StudentAvatar } from "@/components/ui/avatar"
import { Progress } from "@/components/ui/progress"
import { cn, pct } from "@/lib/utils"
import { EmptyState } from "@/components/common/feedback"
import { IconBadge } from "@/components/common/icon-badge"

function ContactField({ icon: Icon, label, value }: { icon: typeof Phone; label: string; value: string | null | undefined }) {
  return (
    <div className="flex items-start gap-3 rounded-lg border bg-muted/20 p-3">
      <span className="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary">
        <Icon className="size-4" />
      </span>
      <div className="min-w-0">
        <div className="text-xs text-muted-foreground">{label}</div>
        <div className="break-words text-sm font-medium">{value || "—"}</div>
      </div>
    </div>
  )
}

export function ContactsPage() {
  const students = useStudents()
  const [search, setSearch] = useState("")
  const [filter, setFilter] = useState<"all" | "active" | "inactive" | "finished">("all")
  const [selectedId, setSelectedId] = useState<number | null>(null)

  const selected = useStudentById(selectedId != null ? String(selectedId) : undefined)

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return (students.data ?? []).filter((s) => {
      if (filter === "active" && s.is_active !== 1) return false
      if (filter === "inactive" && s.is_active !== 0) return false
      if (filter === "finished" && !(s.course_completed >= s.total_course && s.total_course > 0)) return false
      if (!q) return true
      return [s.name, s.phone, s.email, s.shop_name].some((v) => (v ?? "").toLowerCase().includes(q))
    })
  }, [students.data, search, filter])

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div className="relative flex-1 sm:max-w-[176px]">
          <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Search name, phone, shop…"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="pl-9"
            aria-label="Search contacts"
          />
        </div>
        <Select value={filter} onValueChange={(v) => setFilter(v as typeof filter)}>
          <SelectTrigger className="w-full sm:w-44">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All trainees</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="inactive">Inactive</SelectItem>
            <SelectItem value="finished">Finished</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div className="grid gap-5 xl:grid-cols-[minmax(320px,400px)_minmax(0,1fr)]">
        {/* List */}
        <Card>
          <CardContent className="p-0">
            <div className="max-h-[65vh] divide-y overflow-y-auto">
              {students.isLoading ? (
                Array.from({ length: 5 }).map((_, i) => (
                  <div key={i} className="flex items-center gap-3 p-3">
                    <Skeleton className="size-9 rounded-full" />
                    <Skeleton className="h-4 flex-1" />
                  </div>
                ))
              ) : filtered.length === 0 ? (
                <EmptyState icon={<Contact className="size-4" />} title="No trainees found" />
              ) : (
                filtered.map((s) => (
                  <button
                    key={s.id}
                    type="button"
                    onClick={() => setSelectedId(s.id)}
                    className={cn(
                      "flex w-full items-center gap-3 p-3 text-left transition-colors hover:bg-accent",
                      selectedId === s.id && "bg-primary/10 dark:bg-primary/15",
                    )}
                  >
                    <StudentAvatar name={s.name} photoPath={s.photo_path} />
                    <div className="min-w-0 flex-1">
                      <div className="truncate text-sm font-medium">{s.name}</div>
                      <div className="truncate text-xs text-muted-foreground">{s.phone ?? "—"}</div>
                    </div>
                    <Badge variant={s.is_active === 1 ? "success" : "muted"} className="shrink-0">
                      {s.is_active === 1 ? "Active" : "Inactive"}
                    </Badge>
                  </button>
                ))
              )}
            </div>
          </CardContent>
        </Card>

        {/* Detail */}
        <Card>
          <CardHeader className="px-5">
            <CardTitle className="flex items-center gap-2 text-base">
              <IconBadge icon={Contact} color="blue" size="sm" /> Contact details
            </CardTitle>
          </CardHeader>
          <CardContent className="px-5 pt-0">
            {selected.isLoading ? (
              <div className="space-y-3">
                {Array.from({ length: 4 }).map((_, i) => <Skeleton key={i} className="h-14 w-full" />)}
              </div>
            ) : selected.data ? (
              <div className="space-y-5">
                <div className="flex items-center gap-4">
                  <StudentAvatar name={selected.data.name} photoPath={selected.data.photo_path} className="size-14" />
                  <div className="min-w-0">
                    <div className="text-lg font-semibold tracking-tight">{selected.data.name}</div>
                    <div className="mt-1 flex flex-wrap gap-2">
                      <Badge variant={selected.data.rollcall_group === "Weekend" ? "warning" : "secondary"}>{selected.data.rollcall_group}</Badge>
                      <Badge variant={selected.data.is_active === 1 ? "success" : "muted"}>{selected.data.is_active === 1 ? "Active" : "Inactive"}</Badge>
                    </div>
                  </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-2">
                  <ContactField icon={Phone} label="Phone" value={selected.data.phone} />
                  <ContactField icon={Mail} label="Email" value={selected.data.email} />
                  <ContactField icon={Store} label="Shop" value={selected.data.shop_name} />
                  <ContactField icon={MapPin} label="Address" value={selected.data.address} />
                </div>

                <div className="rounded-lg border bg-muted/20 p-4">
                  <div className="mb-2 flex items-center gap-2 text-sm font-semibold">
                    <ShoppingBag className="size-4 text-primary" /> Progress
                  </div>
                  <div className="space-y-3">
                    <div>
                      <div className="mb-1 flex justify-between text-xs">
                        <span className="text-muted-foreground">Courses</span>
                        <span className="tabular-nums">{selected.data.course_completed}/{selected.data.total_course}</span>
                      </div>
                      <Progress value={pct(selected.data.course_completed, selected.data.total_course)} className="h-1.5" />
                    </div>
                    <div>
                      <div className="mb-1 flex justify-between text-xs">
                        <span className="text-muted-foreground">Practical</span>
                        <span className="tabular-nums">{selected.data.practical_completed}/{selected.data.total_practical}</span>
                      </div>
                      <Progress value={pct(selected.data.practical_completed, selected.data.total_practical)} className="h-1.5" />
                    </div>
                  </div>
                </div>
              </div>
            ) : (
              <EmptyState
                className="py-16"
                icon={<Contact className="size-4" />}
                title="Select a trainee"
                hint="Contact, status and progress show here."
              />
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
