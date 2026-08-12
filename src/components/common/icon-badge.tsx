import type { ComponentType } from "react"
import { cn } from "@/lib/utils"

export type BrandColor = "blue" | "teal" | "orange" | "red" | "purple" | "indigo" | "primary"

const STYLES: Record<BrandColor, string> = {
  blue: "bg-brand-blue-soft text-brand-blue dark:bg-brand-blue/20 dark:text-brand-blue",
  teal: "bg-brand-teal-soft text-brand-teal dark:bg-brand-teal/20 dark:text-brand-teal",
  orange: "bg-brand-orange-soft text-brand-orange dark:bg-brand-orange/20 dark:text-brand-orange",
  red: "bg-brand-red-soft text-brand-red dark:bg-brand-red/20 dark:text-brand-red",
  purple: "bg-brand-purple-soft text-brand-purple dark:bg-brand-purple/20 dark:text-brand-purple",
  indigo: "bg-brand-indigo-soft text-brand-indigo dark:bg-brand-indigo/20 dark:text-brand-indigo",
  primary: "bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary",
}

/**
 * Reusable colored icon chip — circle/rounded square with brand color tint.
 * Use everywhere an icon needs a color accent (stats, card headers, empty states).
 */
export function IconBadge({
  icon: Icon,
  color = "primary",
  className,
  size = "md",
}: {
  icon: ComponentType<{ className?: string }>
  color?: BrandColor
  className?: string
  size?: "sm" | "md" | "lg"
}) {
  const sizes = {
    sm: "size-6 rounded-md [&_svg]:size-3.5",
    md: "size-8 rounded-lg [&_svg]:size-4",
    lg: "size-10 rounded-xl [&_svg]:size-5",
  }
  return (
    <span className={cn("flex shrink-0 items-center justify-center", sizes[size], STYLES[color], className)}>
      <Icon />
    </span>
  )
}
