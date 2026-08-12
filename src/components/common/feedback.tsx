import type { ReactNode } from "react"
import { cn } from "@/lib/utils"

/**
 * Consistent page header: title + subtitle on the left, actions on the right.
 * Wraps on small screens. Use on every page for a uniform look.
 */
export function PageHeader({
  title,
  subtitle,
  children,
  className,
}: {
  title: string
  subtitle?: ReactNode
  children?: ReactNode
  className?: string
}) {
  return (
    <div className={cn("flex flex-wrap items-end justify-between gap-3", className)}>
      <div className="min-w-0">
        <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
        {subtitle && <p className="mt-0.5 text-sm text-muted-foreground">{subtitle}</p>}
      </div>
      {children && <div className="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">{children}</div>}
    </div>
  )
}

/**
 * Consistent empty state: icon in a tinted circle, title, hint, optional action.
 * Centered, same padding everywhere.
 */
export function EmptyState({
  icon,
  title,
  hint,
  action,
  className,
}: {
  icon?: ReactNode
  title: string
  hint?: string
  action?: ReactNode
  className?: string
}) {
  return (
    <div className={cn("flex flex-col items-center gap-2 py-12 text-center", className)}>
      {icon && (
        <div className="flex size-12 items-center justify-center rounded-full bg-gradient-to-br from-brand-blue/15 via-brand-purple/15 to-brand-teal/15 text-brand-purple">
          {icon}
        </div>
      )}
      <p className="text-sm font-medium text-foreground">{title}</p>
      {hint && <p className="max-w-sm text-xs text-muted-foreground">{hint}</p>}
      {action && <div className="mt-2">{action}</div>}
    </div>
  )
}
