import { createContext, useCallback, useContext, useRef, useState } from "react"
import { AlertCircle, CheckCircle2, Info, X } from "lucide-react"
import { cn } from "@/lib/utils"

type ToastVariant = "success" | "error" | "info"
interface Toast {
  id: number
  title: string
  description?: string
  variant: ToastVariant
}
interface ToastCtx {
  toast: (t: { title: string; description?: string; variant?: ToastVariant }) => void
}

const Ctx = createContext<ToastCtx | null>(null)

export function useToast() {
  const c = useContext(Ctx)
  if (!c) throw new Error("useToast must be used within ToastProvider")
  return c
}

const ICONS: Record<ToastVariant, React.ReactNode> = {
  success: <CheckCircle2 className="size-4 shrink-0 text-emerald-500" />,
  error: <AlertCircle className="size-4 shrink-0 text-destructive" />,
  info: <Info className="size-4 shrink-0 text-primary" />,
}

export function ToastProvider({ children }: { children: React.ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([])
  const idRef = useRef(0)

  const dismiss = useCallback((id: number) => {
    setToasts((t) => t.filter((x) => x.id !== id))
  }, [])

  const toast = useCallback<ToastCtx["toast"]>(({ title, description, variant = "info" }) => {
    const id = ++idRef.current
    setToasts((t) => [...t, { id, title, description, variant }])
    window.setTimeout(() => dismiss(id), 4000)
  }, [dismiss])

  return (
    <Ctx.Provider value={{ toast }}>
      {children}
      <div className="pointer-events-none fixed bottom-4 right-4 z-[100] flex w-full max-w-sm flex-col gap-2 px-4">
        {toasts.map((t) => (
          <div
            key={t.id}
            role="status"
            className={cn(
              "toast-in pointer-events-auto flex items-start gap-2.5 rounded-xl border bg-background/95 p-3 shadow-lg backdrop-blur",
              t.variant === "success" && "border-emerald-300/60 dark:border-emerald-500/40",
              t.variant === "error" && "border-destructive/40",
              t.variant === "info" && "border-border",
            )}
          >
            {ICONS[t.variant]}
            <div className="min-w-0 flex-1">
              <p className="text-sm font-medium leading-tight">{t.title}</p>
              {t.description ? <p className="mt-0.5 break-words text-xs text-muted-foreground">{t.description}</p> : null}
            </div>
            <button
              type="button"
              onClick={() => dismiss(t.id)}
              aria-label="Dismiss"
              className="shrink-0 cursor-pointer text-muted-foreground/60 transition-colors hover:text-foreground"
            >
              <X className="size-3.5" />
            </button>
          </div>
        ))}
      </div>
    </Ctx.Provider>
  )
}
