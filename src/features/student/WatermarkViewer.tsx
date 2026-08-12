import { useCallback, useEffect, useRef, useState } from "react"
import { ChevronLeft, ChevronRight, Loader2, Minus, Plus, X } from "lucide-react"
import * as pdfjsLib from "pdfjs-dist"
import type { CurriculumMaterial } from "@/features/students/types"
import type { PortalStudent } from "@/features/student/api"
import { Button } from "@/components/ui/button"
import { cn } from "@/lib/utils"

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL("pdfjs-dist/build/pdf.worker.min.mjs", import.meta.url).toString()

const WM_TEXT = (s: PortalStudent) => [s.name, s.email ?? "", s.phone ?? ""].filter(Boolean).join("  |  ")

/** Diagonal repeated watermark layer (pointer-events none, no selection). */
function WatermarkLayer({ student }: { student: PortalStudent }) {
  const text = WM_TEXT(student)
  const cols = 1
  const perCol = 3
  return (
    <div className="pointer-events-none absolute inset-0 z-10 select-none overflow-hidden">
      <div className="flex h-full w-[200%] -rotate-[24deg] items-center justify-around" style={{ marginLeft: "-50%" }}>
        {Array.from({ length: cols }).map((_, ci) => (
          <div key={ci} className="flex h-full flex-col justify-evenly gap-y-24">
            {Array.from({ length: perCol }).map((_, ri) => (
              <p key={ri} className="whitespace-nowrap text-2xl font-bold tracking-wider text-white/40">
                {text}
              </p>
            ))}
          </div>
        ))}
      </div>
    </div>
  )
}

/** Fullscreen in-browser viewer with student watermark; no download / no copy / no context menu. */
export function WatermarkViewer({ student, material, onClose }: { student: PortalStudent; material: CurriculumMaterial; onClose: () => void }) {
  const isPdf = material.file_type === "application/pdf"
  const isVideo = material.file_type.startsWith("video/")
  const url = `/api/serve_material.php?mid=${material.id}`

  // PDF state
  const [doc, setDoc] = useState<pdfjsLib.PDFDocumentProxy | null>(null)
  const [page, setPage] = useState(1)
  const [pages, setPages] = useState(0)
  const [scale, setScale] = useState(1.4)
  const [pdfError, setPdfError] = useState<string | null>(null)
  const [outline, setOutline] = useState<any[] | null>(null)
  const [sideView, setSideView] = useState<"chapters" | "pages">("chapters")
  const [pageTitles, setPageTitles] = useState<Record<number, string>>({})
  const [titlesLoading, setTitlesLoading] = useState(false)
  const canvasRef = useRef<HTMLCanvasElement>(null)
  const containerRef = useRef<HTMLDivElement>(null)
  const renderTaskRef = useRef<pdfjsLib.RenderTask | null>(null)

  useEffect(() => {
    if (!isPdf) return
    let cancelled = false
    setPdfError(null)
    fetch(url)
      .then((r) => r.arrayBuffer())
      .then((buf) => pdfjsLib.getDocument({ data: buf }).promise)
      .then((d) => {
        if (cancelled) return
        setDoc(d)
        setPages(d.numPages)
        return d.getOutline()
      })
      .then((out) => {
        if (cancelled) return
        const items = (out ?? []).filter(Boolean) as any[]
        setOutline(items)
        setSideView(items.length > 0 ? "chapters" : "pages")
      })
      .catch((e) => {
        if (!cancelled) setPdfError(e?.message ?? "Could not load PDF")
      })
    return () => {
      cancelled = true
    }
  }, [isPdf, url])

  // Extract first line of every page → page "names" for the sidebar (works for
  // PDFs without bookmarks, e.g. Canva exports).
  useEffect(() => {
    if (!doc || !isPdf) return
    let cancelled = false
    setTitlesLoading(true)
    const load = async () => {
      const titles: Record<number, string> = {}
      await Promise.all(
        Array.from({ length: doc.numPages }).map(async (_, i) => {
          try {
            const pg = await doc.getPage(i + 1)
            const tc = await pg.getTextContent()
            const line = tc.items.map((it: any) => (it?.str ?? "")).join(" ").replace(/\s+/g, " ").trim().slice(0, 52)
            if (line) titles[i + 1] = line
          } catch {
            /* skip unreadable page */
          }
        }),
      )
      if (!cancelled) {
        setPageTitles(titles)
        setTitlesLoading(false)
      }
    }
    void load()
    return () => {
      cancelled = true
    }
  }, [doc, isPdf])

  // Keyboard navigation: ← → for pages.
  useEffect(() => {
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "ArrowRight") setPage((p) => Math.min(pages, p + 1))
      else if (e.key === "ArrowLeft") setPage((p) => Math.max(1, p - 1))
      else if (e.key === "Escape") onClose()
    }
    window.addEventListener("keydown", onKey)
    return () => window.removeEventListener("keydown", onKey)
  }, [pages, onClose])

  // Render current page when doc/page/scale change — auto-fit width on mobile.
  useEffect(() => {
    if (!doc || !canvasRef.current) return
    let cancelled = false
    renderTaskRef.current?.cancel()
    doc.getPage(page).then((p) => {
      if (cancelled) return
      const base = p.getViewport({ scale: 1 })
      const maxW = (containerRef.current?.clientWidth ?? 720) - 24
      const effScale = Math.min(scale, Math.max(0.6, maxW / base.width))
      const vp = p.getViewport({ scale: effScale })
      const canvas = canvasRef.current!
      const ratio = Math.max(1, window.devicePixelRatio || 1)
      canvas.width = vp.width * ratio
      canvas.height = vp.height * ratio
      canvas.style.width = `${vp.width}px`
      canvas.style.height = `${vp.height}px`
      const ctx = canvas.getContext("2d")!
      ctx.setTransform(ratio, 0, 0, ratio, 0, 0)
      renderTaskRef.current = p.render({ canvas: canvasRef.current!, viewport: vp })
      renderTaskRef.current.promise.catch(() => {})
      void base
    })
    return () => {
      cancelled = true
    }
  }, [doc, page, scale])

  const next = useCallback(() => {
    if (pages > 0) setPage((p) => Math.min(pages, p + 1))
  }, [pages])

  const jumpToOutline = useCallback(
    async (dest: unknown) => {
      if (!doc) return
      try {
        let d = dest
        if (typeof d === "string") {
          const dests: any = await doc.getDestinations()
          d = dests[d]
        }
        if (Array.isArray(d) && d.length > 0) {
          const idx = await doc.getPageIndex(d[0])
          setPage(idx + 1)
        }
      } catch {
        /* ignore bad dest */
      }
    },
    [doc],
  )

  const renderOutline = (items: any[], depth: number): React.ReactNode =>
    items.map((it, i) => (
      <div key={i}>
        <button
          type="button"
          onClick={() => void jumpToOutline(it.dest)}
          className="block w-full cursor-pointer truncate rounded-md px-2 py-1 text-left text-xs text-white/70 transition-colors hover:bg-white/10 hover:text-white"
          style={{ paddingLeft: `${8 + depth * 12}px` }}
          title={it.title ?? "Untitled"}
        >
          {it.title ?? "Untitled"}
        </button>
        {Array.isArray(it.items) && it.items.length > 0 ? renderOutline(it.items, depth + 1) : null}
      </div>
    ))
  const prev = useCallback(() => {
    if (pages > 0) setPage((p) => Math.max(1, p - 1))
  }, [pages])

  return (
    <div
      role="dialog"
      aria-modal="true"
      aria-label={material.file_name}
      className="fixed inset-0 z-[110] flex flex-col bg-black/95 backdrop-blur-sm"
      onContextMenu={(e) => e.preventDefault()}
    >
      {/* Top bar */}
      <div className="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 border-b border-white/10 px-3 py-2 text-white sm:px-4">
        <div className="min-w-0">
          <p className="truncate text-sm font-semibold">{material.file_name}</p>
          <p className="truncate text-[11px] text-white/50">
            {student.name} · {student.email} · {student.phone} — watermark protected
          </p>
        </div>
        <div className="flex shrink-0 flex-wrap items-center gap-1">
          {isPdf && (
            <>
              <Button size="icon" variant="ghost" className="size-8 text-white" onClick={() => setScale((s) => Math.max(0.6, s - 0.2))} aria-label="Zoom out">
                <Minus className="size-4" />
              </Button>
              <span className="hidden text-xs tabular-nums text-white/70 sm:inline">{page}/{pages}</span>
              <input
                type="number"
                min={1}
                max={pages}
                value={page}
                onChange={(e) => {
                  const v = Number(e.target.value)
                  if (v >= 1 && v <= pages) setPage(v)
                }}
                onBlur={() => setPage((p) => Math.max(1, Math.min(pages, p)))}
                onKeyDown={(e) => {
                  if (e.key === "Enter") (e.target as HTMLInputElement).blur()
                  e.stopPropagation()
                }}
                className="hidden h-8 w-14 rounded-md border border-white/20 bg-white/10 px-1 text-center text-xs tabular-nums text-white outline-none focus:border-white/50 sm:block"
                aria-label="Go to page"
              />
              <Button size="icon" variant="ghost" className="size-8 text-white" onClick={() => setScale((s) => Math.min(3, s + 0.2))} aria-label="Zoom in">
                <Plus className="size-4" />
              </Button>
              <Button size="icon" variant="ghost" className="size-8 text-white disabled:opacity-30" onClick={prev} disabled={page <= 1} aria-label="Previous page">
                <ChevronLeft className="size-4" />
              </Button>
              <Button size="icon" variant="ghost" className="size-8 text-white disabled:opacity-30" onClick={next} disabled={page >= pages} aria-label="Next page">
                <ChevronRight className="size-4" />
              </Button>
              <span className="px-1 text-xs tabular-nums text-white/70 sm:hidden">{page}/{pages}</span>
            </>
          )}
          <Button size="icon" variant="ghost" className="size-8 text-white" onClick={onClose} aria-label="Close viewer">
            <X className="size-4" />
          </Button>
        </div>
      </div>
      {/* Reading progress */}
      {isPdf && doc && (
        <div className="h-0.5 w-full bg-white/10">
          <div className="h-full bg-emerald-400 transition-all" style={{ width: `${(page / pages) * 100}%` }} />
        </div>
      )}

      {/* Content */}
      <div className="flex min-h-0 flex-1">
        {/* Sidebar (desktop): Chapters + Pages */}
        {isPdf && doc && (
          <aside className="hidden w-52 shrink-0 flex-col border-r border-white/10 md:flex lg:w-60">
            <div className="flex border-b border-white/10 p-1">
              {(
                [
                  ["chapters", `Chapters${outline?.length ? ` (${outline.length})` : ""}`],
                  ["pages", "Pages"],
                ] as const
              ).map(([v, label]) => (
                <button
                  key={v}
                  type="button"
                  onClick={() => setSideView(v)}
                  className={cn(
                    "flex-1 cursor-pointer rounded-md py-1 text-[11px] font-semibold transition-colors",
                    sideView === v ? "bg-white/15 text-white" : "text-white/50 hover:text-white",
                  )}
                >
                  {label}
                </button>
              ))}
            </div>
            <div className="flex-1 overflow-y-auto p-2">
              {sideView === "chapters" ? (
                outline && outline.length > 0 ? (
                  renderOutline(outline, 0)
                ) : outline === null ? (
                  <p className="px-2 py-3 text-xs text-white/40">Loading chapters…</p>
                ) : (
                  <p className="px-2 py-3 text-xs leading-relaxed text-white/40">
                    ဒီ PDF မှာ chapter/bookmark မရှိဘူး။
                    <br />
                    Pages tab မှာ page တစ်ခုချင်းရဲ့ နာမည်တွေ ပြထားတယ်။
                  </p>
                )
              ) : (
                <div className="space-y-0.5">
                  {titlesLoading && Object.keys(pageTitles).length === 0 ? (
                    <p className="px-2 py-3 text-xs text-white/40">Loading page names…</p>
                  ) : (
                    Array.from({ length: pages }).map((_, i) => {
                      const n = i + 1
                      const active = n === page
                      const title = pageTitles[n]
                      return (
                        <button
                          key={n}
                          type="button"
                          onClick={() => setPage(n)}
                          className={cn(
                            "flex w-full cursor-pointer items-center gap-2 rounded-md px-2 py-1 text-left text-xs transition-colors",
                            active ? "bg-emerald-500 font-semibold text-white" : "text-white/60 hover:bg-white/10 hover:text-white",
                          )}
                        >
                          <span className="shrink-0 tabular-nums text-white/40">{n}</span>
                          <span className="min-w-0 truncate">{title || "—"}</span>
                        </button>
                      )
                    })
                  )}
                </div>
              )}
            </div>
          </aside>
        )}

        <div ref={containerRef} className="relative flex min-h-0 flex-1 select-none justify-center overflow-auto p-4" onContextMenu={(e) => e.preventDefault()}>
          <WatermarkLayer student={student} />
          <div className="relative z-[5] m-auto">
          {isPdf ? (
            pdfError ? (
              <p className="max-w-md rounded-lg bg-white/10 px-4 py-3 text-center text-sm text-white/80">{pdfError}</p>
            ) : !doc ? (
              <div className="flex items-center gap-2 text-white/70">
                <Loader2 className="size-5 animate-spin" /> Loading PDF…
              </div>
            ) : (
              <canvas ref={canvasRef} className="shadow-2xl" />
            )
          ) : isVideo ? (
            <video src={url} controls className="max-h-[75vh] max-w-full rounded-lg bg-black object-contain" draggable={false} />
          ) : (
            <img src={url} alt={material.file_name} className="max-h-[78vh] max-w-full rounded-lg object-contain" draggable={false} />
          )}
          </div>
        </div>
      </div>
    </div>
  )
}
