import { useMemo, useState } from "react"
import { Banknote, Briefcase, HandCoins, PiggyBank, Plus, TrendingDown, Trash2, Wallet } from "lucide-react"
import { useFinance, useSaveFinance, type FinanceRow } from "@/features/finance/api"
import { useTimeMachine } from "@/lib/timemachine"
import { Button } from "@/components/ui/button"
import { Card, CardContent } from "@/components/ui/card"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Skeleton } from "@/components/ui/skeleton"
import { Tabs, TabsContent, UnderlineTabsList as TabsList, UnderlineTabsTrigger as TabsTrigger } from "@/components/ui/tabs"
import { formatDate } from "@/lib/utils"

type FinanceType = "income" | "assets" | "expenses" | "shares" | "money_out"

const TABS: Array<{ id: FinanceType; label: string; icon: typeof Banknote }> = [
  { id: "income", label: "Income", icon: Wallet },
  { id: "assets", label: "Assets", icon: Briefcase },
  { id: "expenses", label: "Expenses", icon: TrendingDown },
  { id: "shares", label: "Share", icon: PiggyBank },
  { id: "money_out", label: "Money Out", icon: HandCoins },
]

const fmt = (n: number | string | null | undefined) => Number(n ?? 0).toLocaleString()

export function FinancePage() {
  const { data, isLoading } = useFinance()
  const save = useSaveFinance()
  const { date: tmDate } = useTimeMachine()
  const [tab, setTab] = useState<FinanceType>("assets")
  const [dialog, setDialog] = useState<FinanceType | null>(null)
  const [form, setForm] = useState<Record<string, string>>({})

  const assets = data?.assets ?? []
  const expenses = data?.expenses ?? []
  const shares = data?.shares ?? []
  const moneyOut = data?.money_out ?? []
  const income = data?.income ?? []

  const totals = useMemo(
    () => ({
      income: income.reduce((s, r) => s + Number(r.paid_amount ?? 0), 0),
      assets: assets.reduce((s, r) => s + Number(r.value_amount ?? 0), 0),
      expenses: expenses.reduce((s, r) => s + Number(r.amount ?? 0), 0),
      moneyOut: moneyOut.reduce((s, r) => s + Number(r.amount ?? 0), 0),
      shareTotal: shares.reduce((s, r) => s + Number(r.share_percent ?? 0), 0),
    }),
    [assets, expenses, income, moneyOut, shares],
  )

  const openAdd = (type: FinanceType) => {
    setForm({})
    setDialog(type)
  }

  const submit = () => {
    if (!dialog) return
    const base: Record<string, string | number> = {
      action: "add",
      type: dialog,
      entry_date: form.entry_date ?? tmDate,
      expense_date: form.expense_date ?? tmDate,
      out_date: form.out_date ?? tmDate,
      name: form.name ?? "",
      value_amount: form.value_amount ?? "0",
      title: form.title ?? "",
      amount: form.amount ?? "0",
      category: form.category ?? "",
      partner_name: form.partner_name ?? "",
      share_percent: form.share_percent ?? "0",
      reason: form.reason ?? "",
      note: form.note ?? "",
    }
    save.mutate(base as never, { onSuccess: () => setDialog(null) })
  }

  const del = (type: FinanceType, id: number) => {
    if (!window.confirm("Delete this record?")) return
    save.mutate({ action: "delete", type, id } as never)
  }

  const rowsFor = (type: FinanceType): FinanceRow[] =>
    type === "assets" ? assets : type === "expenses" ? expenses : type === "shares" ? shares : type === "money_out" ? moneyOut : []

  return (
    <div className="space-y-6">
      {/* Summary cards */}
      <div className="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <SummaryCard label="Total received" value={`${fmt(totals.income)} MMK`} icon={<Wallet className="size-4" />} tone="emerald" />
        <SummaryCard label="Total assets" value={`${fmt(totals.assets)} MMK`} icon={<Briefcase className="size-4" />} tone="teal" />
        <SummaryCard label="Total expenses" value={`${fmt(totals.expenses)} MMK`} icon={<TrendingDown className="size-4" />} tone="amber" />
        <SummaryCard label="Money out" value={`${fmt(totals.moneyOut)} MMK`} icon={<HandCoins className="size-4" />} tone="rose" />
        <SummaryCard label="Share total" value={`${totals.shareTotal}%`} icon={<PiggyBank className="size-4" />} tone="blue" />
      </div>

      <Card>
        <CardContent className="px-4 pt-4">
          <Tabs value={tab} onValueChange={(v) => setTab(v as FinanceType)}>
            <div className="flex flex-wrap items-center justify-between gap-2">
              <TabsList className="gap-1">
                {TABS.map((t) => (
                  <TabsTrigger key={t.id} value={t.id} className="gap-1.5">
                    <t.icon className="size-3.5" /> {t.label}
                  </TabsTrigger>
                ))}
              </TabsList>
              {tab !== "income" && (
                <Button size="sm" className="h-8 gap-1.5 text-xs" onClick={() => openAdd(tab)}>
                  <Plus className="size-3.5" /> Add {tab.replace("_", " ")}
                </Button>
              )}
            </div>

            {isLoading ? (
              <div className="mt-4 space-y-2">
                {Array.from({ length: 3 }).map((_, i) => <Skeleton key={i} className="h-12 w-full" />)}
              </div>
            ) : (
              <>
              <TabsContent value="income" className="mt-4 space-y-2">
                <p className="text-xs text-muted-foreground">
                  Trainee payments ကနေ အလိုအလျောက် လာတာပါ — Payment plan ထဲ update ရင် ဒီမှာ ပြောင်းပြီးသား။
                </p>
                {income.length === 0 ? (
                  <p className="py-10 text-center text-sm text-muted-foreground">No payments recorded yet.</p>
                ) : (
                  income.map((r) => (
                    <div key={r.student_id} className="rounded-lg border bg-muted/20 px-3 py-2">
                      <div className="flex items-start gap-3">
                        <div className="min-w-0 flex-1">
                          <div className="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                            <span className="min-w-0 text-sm font-medium">{r.student_name}</span>
                            <span className="text-[10px] font-semibold text-muted-foreground">{fmt(r.paid_amount)} MMK</span>
                            {Number(r.remaining_amount) > 0 && (
                              <span className="rounded-full bg-amber-500/15 px-2 py-0.5 text-[10px] font-semibold text-amber-600 dark:text-amber-400">
                                {fmt(r.remaining_amount)} MMK ကျန်
                              </span>
                            )}
                          </div>
                          <span className="block truncate text-xs text-muted-foreground/80">
                            Total {fmt(r.total_amount)} MMK
                            {r.first_paid_at ? ` · Paid ${formatDate(r.first_paid_at)}` : ""}
                            {r.second_paid_at ? ` · Remaining paid ${formatDate(r.second_paid_at)}` : ""}
                            {r.reminder_date ? ` · Reminder ${formatDate(r.reminder_date)}` : ""}
                          </span>
                          {r.note ? <span className="mt-0.5 block text-xs text-muted-foreground/80">{r.note}</span> : null}
                        </div>
                      </div>
                    </div>
                  ))
                )}
              </TabsContent>

              {TABS.filter((t) => t.id !== "income").map((t) => (
                <TabsContent key={t.id} value={t.id} className="mt-4 space-y-2">
                  {rowsFor(t.id).length === 0 ? (
                    <p className="py-10 text-center text-sm text-muted-foreground">No {t.label.toLowerCase()} yet.</p>
                  ) : (
                    rowsFor(t.id).map((r) => (
                      <div key={r.id} className="rounded-lg border bg-muted/20 px-3 py-2">
                        <div className="flex items-start gap-3">
                          <div className="min-w-0 flex-1">
                            <div className="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                              <span className="min-w-0 text-sm font-medium">{r.name ?? r.title ?? r.partner_name ?? r.reason ?? "—"}</span>
                              <span className="text-[10px] font-semibold text-muted-foreground">
                                {fmt(r.value_amount ?? r.amount ?? r.share_percent)}
                                {(r.value_amount != null || r.amount != null) ? " MMK" : "%"}
                              </span>
                            </div>
                            <span className="block truncate text-xs text-muted-foreground/80">
                              {formatDate(String(r.entry_date ?? r.expense_date ?? r.out_date ?? r.created_at ?? ""))}
                              {r.category ? ` · ${r.category}` : ""}
                            </span>
                            {r.note ? <span className="mt-0.5 block text-xs text-muted-foreground/80">{r.note}</span> : null}
                          </div>
                          <button
                            type="button"
                            onClick={() => del(t.id, Number(r.id))}
                            aria-label={`Delete ${t.id} ${r.id}`}
                            className="shrink-0 cursor-pointer text-muted-foreground transition-colors hover:text-destructive"
                          >
                            <Trash2 className="size-3.5" />
                          </button>
                        </div>
                      </div>
                    ))
                  )}
                </TabsContent>
              ))}
              </>
            )}
          </Tabs>
        </CardContent>
      </Card>

      {/* Add dialog */}
      <Dialog open={dialog != null} onOpenChange={(o) => !o && setDialog(null)}>
        <DialogContent className="sm:max-w-sm">
          <DialogHeader>
            <DialogTitle>Add {dialog?.replace("_", " ")}</DialogTitle>
            <DialogDescription>Academy finance record.</DialogDescription>
          </DialogHeader>
          <div className="space-y-3">
            {dialog === "assets" && (
              <>
                <Field label="Name" id="f-name" value={form.name ?? ""} onChange={(v) => setForm((f) => ({ ...f, name: v }))} />
                <Field label="Value (MMK)" id="f-value" type="number" value={form.value_amount ?? ""} onChange={(v) => setForm((f) => ({ ...f, value_amount: v }))} />
                <Field label="Date" id="f-date" type="date" value={form.entry_date ?? tmDate} onChange={(v) => setForm((f) => ({ ...f, entry_date: v }))} />
              </>
            )}
            {dialog === "expenses" && (
              <>
                <Field label="Title" id="f-title" value={form.title ?? ""} onChange={(v) => setForm((f) => ({ ...f, title: v }))} />
                <Field label="Amount (MMK)" id="f-amount" type="number" value={form.amount ?? ""} onChange={(v) => setForm((f) => ({ ...f, amount: v }))} />
                <Field label="Category" id="f-cat" value={form.category ?? ""} onChange={(v) => setForm((f) => ({ ...f, category: v }))} />
                <Field label="Date" id="f-edate" type="date" value={form.expense_date ?? tmDate} onChange={(v) => setForm((f) => ({ ...f, expense_date: v }))} />
              </>
            )}
            {dialog === "shares" && (
              <>
                <Field label="Partner name" id="f-partner" value={form.partner_name ?? ""} onChange={(v) => setForm((f) => ({ ...f, partner_name: v }))} />
                <Field label="Share (%)" id="f-pct" type="number" value={form.share_percent ?? ""} onChange={(v) => setForm((f) => ({ ...f, share_percent: v }))} />
              </>
            )}
            {dialog === "money_out" && (
              <>
                <Field label="Amount (MMK)" id="f-mo" type="number" value={form.amount ?? ""} onChange={(v) => setForm((f) => ({ ...f, amount: v }))} />
                <Field label="Reason" id="f-reason" value={form.reason ?? ""} onChange={(v) => setForm((f) => ({ ...f, reason: v }))} />
                <Field label="Date" id="f-odate" type="date" value={form.out_date ?? tmDate} onChange={(v) => setForm((f) => ({ ...f, out_date: v }))} />
              </>
            )}
            <div className="space-y-1.5">
              <Label htmlFor="f-note">Note</Label>
              <Input id="f-note" value={form.note ?? ""} onChange={(e) => setForm((f) => ({ ...f, note: e.target.value }))} />
            </div>
          </div>
          <DialogFooter className="gap-2">
            <Button variant="outline" onClick={() => setDialog(null)}>Cancel</Button>
            <Button onClick={submit} disabled={save.isPending}><Plus /> Add</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}

function Field({ label, id, value, onChange, type = "text" }: { label: string; id: string; value: string; onChange: (v: string) => void; type?: string }) {
  return (
    <div className="space-y-1.5">
      <Label htmlFor={id}>{label}</Label>
      <Input id={id} type={type} min={0} value={value} onChange={(e) => onChange(e.target.value)} />
    </div>
  )
}

function SummaryCard({ label, value, icon, tone }: { label: string; value: string; icon: React.ReactNode; tone: "emerald" | "teal" | "amber" | "rose" | "blue" }) {
  const tones = {
    emerald: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
    teal: "bg-teal-500/10 text-teal-600 dark:text-teal-400",
    amber: "bg-amber-500/10 text-amber-600 dark:text-amber-400",
    rose: "bg-rose-500/10 text-rose-600 dark:text-rose-400",
    blue: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  }
  return (
    <Card>
      <CardContent className="flex items-center gap-3 p-4">
        <span className={`flex size-9 shrink-0 items-center justify-center rounded-xl ${tones[tone]}`}>{icon}</span>
        <div className="min-w-0">
          <div className="truncate text-xs text-muted-foreground">{label}</div>
          <div className="truncate text-sm font-semibold tabular-nums">{value}</div>
        </div>
      </CardContent>
    </Card>
  )
}
