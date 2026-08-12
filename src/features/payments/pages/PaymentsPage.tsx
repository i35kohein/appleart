import { useMemo, useState } from "react"
import { CreditCard, Pencil, Search } from "lucide-react"
import { usePayments, useSavePayment } from "@/features/students/detail-api"
import type { PaymentInput } from "@/features/students/detail-api"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Progress } from "@/components/ui/progress"
import { Skeleton } from "@/components/ui/skeleton"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { StudentAvatar } from "@/components/ui/avatar"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { formatDate } from "@/lib/utils"
import { EmptyState } from "@/components/common/feedback"

function PaymentDialog({
  plan,
  open,
  onOpenChange,
}: {
  plan: NonNullable<ReturnType<typeof usePayments>["data"]>[number] | null
  open: boolean
  onOpenChange: (open: boolean) => void
}) {
  const save = useSavePayment()
  const [form, setForm] = useState<PaymentInput>({
    student_id: 0,
    total_amount: "",
    first_amount: "",
    second_amount: "",
    first_paid_at: "",
    second_paid_at: "",
    reminder_date: "",
    note: "",
  })

  // Reset form whenever the dialog opens for a different plan
  const [lastKey, setLastKey] = useState<string>("")
  const key = plan ? `${plan.student_id}` : "new"
  if (open && key !== lastKey) {
    setLastKey(key)
    setForm({
      student_id: plan?.student_id ?? 0,
      total_amount: plan?.total_amount ?? "",
      first_amount: plan?.first_amount ?? "",
      second_amount: plan?.second_amount ?? "",
      first_paid_at: plan?.first_paid_at ?? "",
      second_paid_at: plan?.second_paid_at ?? "",
      reminder_date: plan?.reminder_date ?? "",
      note: plan?.note ?? "",
    })
  }

  const submit = () => {
    save.mutate(form, {
      onSuccess: () => {
        onOpenChange(false)
        setLastKey("")
      },
    })
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{plan ? `Payment plan — ${plan.name}` : "New payment plan"}</DialogTitle>
          <DialogDescription>Set amounts, paid dates and reminders.</DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          <div className="grid gap-3 sm:grid-cols-3">
            {(
              [
                ["total_amount", "Total (MMK)"],
                ["first_amount", "First (MMK)"],
                ["second_amount", "Second (MMK)"],
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
                ["first_paid_at", "First paid date"],
                ["second_paid_at", "Second paid date"],
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

          {save.isError && (
            <Alert variant="destructive">
              <AlertDescription>{save.error.message}</AlertDescription>
            </Alert>
          )}
        </div>

        <DialogFooter>
          <Button variant="outline" onClick={() => onOpenChange(false)}>Cancel</Button>
          <Button onClick={submit} disabled={save.isPending || !form.student_id}>
            {save.isPending ? "Saving…" : "Save plan"}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}

export function PaymentsPage() {
  const { data: plans, isLoading } = usePayments()
  const [search, setSearch] = useState("")
  const [editing, setEditing] = useState<NonNullable<typeof plans>[number] | null>(null)
  const [dialogOpen, setDialogOpen] = useState(false)

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase()
    return (plans ?? []).filter((p) => !q || p.name.toLowerCase().includes(q) || (p.phone ?? "").includes(q))
  }, [plans, search])

  const withPlan = filtered.filter((p) => Number(p.total_amount) > 0)
  const withoutPlan = filtered.filter((p) => Number(p.total_amount) === 0)

  const rows = [...withPlan, ...withoutPlan]

  return (
    <div className="space-y-6">
      <div className="flex items-center rounded-lg border bg-card px-3 py-2.5">
        <div className="relative w-full sm:w-72">
          <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
          <Input placeholder="Search trainee…" value={search} onChange={(e) => setSearch(e.target.value)} className="pl-9" aria-label="Search payments" />
        </div>
      </div>

      <Card>
        <CardContent className="p-0">
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Trainee</TableHead>
                  <TableHead className="hidden sm:table-cell">Total</TableHead>
                  <TableHead className="hidden md:table-cell">First</TableHead>
                  <TableHead className="hidden md:table-cell">Second</TableHead>
                  <TableHead className="hidden lg:table-cell">Reminder</TableHead>
                  <TableHead>Paid</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {isLoading ? (
                  Array.from({ length: 6 }).map((_, i) => (
                    <TableRow key={i}><TableCell colSpan={7}><Skeleton className="h-10 w-full" /></TableCell></TableRow>
                  ))
                ) : rows.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={7}>
                      <EmptyState icon={<CreditCard className="size-4" />} title="No trainees found" />
                    </TableCell>
                  </TableRow>
                ) : (
                  rows.map((p) => {
                    const total = Number(p.total_amount)
                    // second installment counts as paid only when actually paid
                    const paid = Number(p.first_amount ?? 0) + (p.second_paid_at ? Number(p.second_amount ?? 0) : 0)
                    const remaining = total - paid
                    const progress = total > 0 ? Math.round((paid / total) * 100) : 0
                    return (
                      <TableRow key={p.student_id}>
                        <TableCell>
                          <div className="flex items-center gap-3">
                            <StudentAvatar name={p.name} photoPath={p.photo_path} />
                            <div className="min-w-0">
                              <div className="truncate font-medium">{p.name}</div>
                              <div className="text-xs text-muted-foreground md:hidden">{total > 0 ? `${total.toLocaleString()} MMK` : "No plan"}</div>
                            </div>
                          </div>
                        </TableCell>
                        <TableCell className="hidden tabular-nums sm:table-cell">
                          {total > 0 ? `${total.toLocaleString()} MMK` : <Badge variant="muted">No plan</Badge>}
                        </TableCell>
                        <TableCell className="hidden tabular-nums text-muted-foreground md:table-cell">
                          {Number(p.first_amount) > 0 ? `${Number(p.first_amount).toLocaleString()} MMK` : "—"}
                        </TableCell>
                        <TableCell className="hidden tabular-nums text-muted-foreground md:table-cell">
                          {Number(p.second_amount) > 0 ? `${Number(p.second_amount).toLocaleString()} MMK` : "—"}
                        </TableCell>
                        <TableCell className="hidden lg:table-cell">{p.reminder_date ? formatDate(p.reminder_date) : "—"}</TableCell>
                        <TableCell>
                          <div className="flex items-center gap-2">
                            <Progress value={progress} className="h-1.5 w-16" />
                            <span className="text-xs tabular-nums text-muted-foreground">
                              {paid.toLocaleString()}
                              {total > 0 && remaining > 0 && <span className="hidden sm:inline"> / {remaining.toLocaleString()} left</span>}
                            </span>
                          </div>
                        </TableCell>
                        <TableCell className="text-right">
                          <Button variant="ghost" size="icon" aria-label={`Edit payment for ${p.name}`} onClick={() => { setEditing(p); setDialogOpen(true) }}>
                            <Pencil />
                          </Button>
                        </TableCell>
                      </TableRow>
                    )
                  })
                )}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <PaymentDialog plan={editing} open={dialogOpen} onOpenChange={setDialogOpen} />
    </div>
  )
}
