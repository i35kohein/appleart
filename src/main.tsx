import { StrictMode } from "react"
import { createRoot } from "react-dom/client"
import "./index.css"
import { App } from "./App"
import { applyTheme, getStoredTheme } from "@/lib/themes"

// Restore theme before first paint
const saved = localStorage.getItem("appleart_theme")
if (saved === "dark" || (!saved && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
  document.documentElement.classList.add("dark")
}

// Restore color theme (from color plate presets)
applyTheme(getStoredTheme())

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
