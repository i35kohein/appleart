import { useEffect, useState } from "react"

/**
 * Theme presets built from Ko Hein's color plate (2026-08-12):
 * Blue, Teal, Orange, Red, Purple, Indigo — each with a soft tint.
 */

export interface AppTheme {
  id: string
  name: string
  /** Main brand color (from the color plate). */
  primary: string
  /** Soft tint for accents / sidebar. */
  soft: string
  /** Slightly deeper shade for hovers. */
  deep: string
  /** Chart palette (derived). */
  charts: string[]
}

export const THEMES: AppTheme[] = [
  {
    id: "blue",
    name: "Ocean Blue",
    primary: "#2f91ff",
    soft: "#e6f0ff",
    deep: "#1f6fd6",
    charts: ["#2f91ff", "#009688", "#ffa41d", "#694bd9", "#ff555f"],
  },
  {
    id: "teal",
    name: "Teal",
    primary: "#009688",
    soft: "#e0f2f1",
    deep: "#00796b",
    charts: ["#009688", "#2f91ff", "#ffa41d", "#694bd9", "#ff555f"],
  },
  {
    id: "orange",
    name: "Sunset Orange",
    primary: "#ffa41d",
    soft: "#fff3e0",
    deep: "#e08a00",
    charts: ["#ffa41d", "#2f91ff", "#009688", "#694bd9", "#ff555f"],
  },
  {
    id: "red",
    name: "Coral Red",
    primary: "#ff555f",
    soft: "#ffe4e6",
    deep: "#e03a44",
    charts: ["#ff555f", "#2f91ff", "#009688", "#ffa41d", "#694bd9"],
  },
  {
    id: "purple",
    name: "Royal Purple",
    primary: "#694bd9",
    soft: "#eee9fb",
    deep: "#5438b8",
    charts: ["#694bd9", "#2f91ff", "#009688", "#ffa41d", "#ff555f"],
  },
  {
    id: "indigo",
    name: "Indigo",
    primary: "#4453b6",
    soft: "#e6e9f7",
    deep: "#35408f",
    charts: ["#4453b6", "#2f91ff", "#009688", "#ffa41d", "#ff555f"],
  },
]

const STORAGE_KEY = "appleart_color_theme"
const DEFAULT_THEME = "teal" // current site look

/** hex -> {r,g,b} */
function hexRgb(hex: string) {
  const h = hex.replace("#", "")
  return {
    r: parseInt(h.slice(0, 2), 16),
    g: parseInt(h.slice(2, 4), 16),
    b: parseInt(h.slice(4, 6), 16),
  }
}

/** mix two colors, amount 0..1 toward target */
function mix(hex: string, targetHex: string, amount: number) {
  const a = hexRgb(hex)
  const b = hexRgb(targetHex)
  const c = (x: number, y: number) => Math.round(x + (y - x) * amount)
  return `#${[c(a.r, b.r), c(a.g, b.g), c(a.b, b.b)].map((v) => v.toString(16).padStart(2, "0")).join("")}`
}

/** Apply a theme's CSS variables to :root (light) and .dark (dark). */
export function applyTheme(id: string) {
  const theme = THEMES.find((t) => t.id === id) ?? THEMES[0]
  const root = document.documentElement
  const isDark = root.classList.contains("dark")

  // Light values
  root.style.setProperty("--primary", theme.primary)
  root.style.setProperty("--primary-foreground", "#ffffff")
  root.style.setProperty("--ring", theme.primary)
  root.style.setProperty("--accent", theme.soft)
  root.style.setProperty("--accent-foreground", theme.deep)
  root.style.setProperty("--sidebar-accent", theme.soft)
  root.style.setProperty("--sidebar-accent-foreground", theme.deep)
  root.style.setProperty("--chart-1", theme.charts[0])
  root.style.setProperty("--chart-2", theme.charts[1])
  root.style.setProperty("--chart-3", theme.charts[2])
  root.style.setProperty("--chart-4", theme.charts[3])
  root.style.setProperty("--chart-5", theme.charts[4])

  // Dark values — only when dark mode is active, and lighter primary so it reads well
  if (isDark) {
    const darkPrimary = mix(theme.primary, "#ffffff", 0.25)
    root.style.setProperty("--primary", darkPrimary)
    root.style.setProperty("--ring", darkPrimary)
    root.style.setProperty("--primary-foreground", "#121212")
    root.style.setProperty("--accent", mix(theme.primary, "#000000", 0.75))
    root.style.setProperty("--accent-foreground", mix(theme.primary, "#ffffff", 0.8))
    root.style.setProperty("--sidebar-accent", mix(theme.primary, "#000000", 0.72))
    root.style.setProperty("--sidebar-accent-foreground", mix(theme.primary, "#ffffff", 0.85))
  }
}

export function getStoredTheme(): string {
  try {
    return localStorage.getItem(STORAGE_KEY) ?? DEFAULT_THEME
  } catch {
    return DEFAULT_THEME
  }
}

export function storeTheme(id: string) {
  try {
    localStorage.setItem(STORAGE_KEY, id)
  } catch {
    /* ignore */
  }
}

/** Hook: returns current theme id + setter (applies + persists). */
export function useTheme() {
  const [themeId, setThemeId] = useState<string>(getStoredTheme)

  useEffect(() => {
    applyTheme(themeId)
    storeTheme(themeId)
  }, [themeId])

  // Re-apply when dark mode toggles
  useEffect(() => {
    const observer = new MutationObserver(() => applyTheme(themeId))
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ["class"] })
    return () => observer.disconnect()
  }, [themeId])

  return { themeId, setThemeId }
}
