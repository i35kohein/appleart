import { useState } from "react"
import { Bot, Send, Sparkles } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { cn } from "@/lib/utils"

interface Msg {
  role: "user" | "assistant"
  text: string
}

export function AiChatDialog({ open, onOpenChange }: { open: boolean; onOpenChange: (o: boolean) => void }) {
  const [messages, setMessages] = useState<Msg[]>([])
  const [draft, setDraft] = useState("")
  const [pending, setPending] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const send = async () => {
    const text = draft.trim()
    if (!text || pending) return
    setDraft("")
    setError(null)
    setMessages((m) => [...m, { role: "user", text }])
    setPending(true)
    try {
      const fd = new FormData()
      fd.append("message", text)
      const res = await fetch("/api/ai_chat.php", { method: "POST", body: fd })
      const data = await res.json().catch(() => null)
      if (data?.status === "success") {
        setMessages((m) => [...m, { role: "assistant", text: data.reply }])
      } else {
        setError(data?.message ?? `Request failed (${res.status})`)
      }
    } catch (e) {
      setError("Network error — try again.")
    } finally {
      setPending(false)
    }
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="flex max-h-[80vh] flex-col sm:max-w-md">
        <DialogHeader>
          <DialogTitle className="flex items-center gap-2">
            <span className="flex size-7 items-center justify-center rounded-lg bg-primary/10 text-primary">
              <Bot className="size-4" />
            </span>
            AI Assistant
          </DialogTitle>
        </DialogHeader>

        <div className="flex-1 space-y-3 overflow-y-auto pr-1">
          {messages.length === 0 && !error && (
            <div className="rounded-lg border bg-muted/30 px-3 py-4 text-center text-xs text-muted-foreground">
              <Sparkles className="mx-auto mb-1.5 size-4 text-primary" />
              မေးချင်တာကို ရိုက်ထည့်ပါ — ပြုပြင်ရေး၊ သင်တန်းအကြောင်း စတာတွေ မေးလို့ရတယ်။
            </div>
          )}
          {messages.map((m, i) => (
            <div key={i} className={cn("flex", m.role === "user" ? "justify-end" : "justify-start")}>
              <div
                className={cn(
                  "max-w-[85%] whitespace-pre-wrap rounded-2xl px-3.5 py-2 text-sm",
                  m.role === "user"
                    ? "bg-primary text-primary-foreground"
                    : "border bg-muted/40 text-foreground",
                )}
              >
                {m.text}
              </div>
            </div>
          ))}
          {pending && (
            <div className="flex justify-start">
              <div className="rounded-2xl border bg-muted/40 px-3.5 py-2 text-sm text-muted-foreground">Thinking…</div>
            </div>
          )}
          {error && <p className="text-xs font-medium text-destructive">{error}</p>}
        </div>

        <div className="flex items-center gap-2 border-t pt-3">
          <Input
            value={draft}
            onChange={(e) => setDraft(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === "Enter") void send()
            }}
            placeholder="Ask anything…"
            className="h-9"
          />
          <Button size="icon" className="h-9 w-9 shrink-0" onClick={() => void send()} disabled={pending || !draft.trim()} aria-label="Send">
            <Send className="size-4" />
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  )
}
