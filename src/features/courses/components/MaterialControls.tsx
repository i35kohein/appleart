import { useRef, useState } from "react"
import { Check, CircleAlert, Download, FileText, Loader2, Paperclip, Trash2, Upload } from "lucide-react"
import { useDeleteMaterial, useUploadMaterial } from "@/features/students/api"
import type { CurriculumMaterial } from "@/features/students/types"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog"
import { cn } from "@/lib/utils"

const fmtSize = (b: number) => (b > 1048576 ? `${(b / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(b / 1024))} KB`)

function MaterialViewer({ open, onOpenChange, itemTitle, materials }: { open: boolean; onOpenChange: (o: boolean) => void; itemTitle: string; materials: CurriculumMaterial[] }) {
  const [idx, setIdx] = useState(0)
  const m = materials[Math.min(idx, materials.length - 1)]
  const url = m ? `/api/serve_material.php?mid=${m.id}` : ""

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="flex max-h-[90vh] flex-col overflow-hidden sm:max-w-4xl">
        <DialogHeader>
          <DialogTitle className="truncate pr-8 text-base">{itemTitle}</DialogTitle>
        </DialogHeader>
        {materials.length > 1 && (
          <div className="flex flex-wrap gap-1.5">
            {materials.map((x, i) => (
              <button
                key={x.id}
                type="button"
                onClick={() => setIdx(i)}
                className={cn(
                  "max-w-48 cursor-pointer truncate rounded-full border px-2.5 py-1 text-xs transition-colors",
                  i === idx ? "border-primary bg-primary/10 font-medium text-primary" : "border-border text-muted-foreground hover:bg-accent",
                )}
              >
                {x.file_name}
              </button>
            ))}
          </div>
        )}
        <div className="min-h-0 flex-1 overflow-hidden rounded-lg border bg-muted/20">
          {m.file_type === "application/pdf" ? (
            <iframe src={url} title={m.file_name} className="h-[70vh] w-full" />
          ) : m.file_type.startsWith("video/") ? (
            <video src={url} controls className="h-[70vh] w-full bg-black object-contain" />
          ) : (
            <img src={url} alt={m.file_name} className="h-[70vh] w-full object-contain" />
          )}
        </div>
      </DialogContent>
    </Dialog>
  )
}

/** Per-lesson material buttons: 📎 view (if any) + ⬆ upload (admin only) — with in-browser viewer. */
export function MaterialControls({ itemId, itemTitle, materials, hideUpload, labeled }: { itemId: number; itemTitle: string; materials?: CurriculumMaterial[]; hideUpload?: boolean; labeled?: boolean }) {
  const upload = useUploadMaterial()
  const inputRef = useRef<HTMLInputElement>(null)
  const [viewerOpen, setViewerOpen] = useState(false)
  const [justUploaded, setJustUploaded] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const list = materials ?? []

  const onPick = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    e.target.value = ""
    if (!file) return
    setError(null)
    upload.mutate(
      { itemId, file },
      {
        onSuccess: () => {
          setJustUploaded(true)
          window.setTimeout(() => setJustUploaded(false), 1600)
        },
        onError: (err) => {
          setError(err instanceof Error ? err.message : "Upload failed")
          window.setTimeout(() => setError(null), 4000)
        },
      },
    )
  }

  return (
    <>
      <div className="flex shrink-0 items-center gap-0.5">
        {list.length > 0 && (
          <Button
            size="icon"
            variant="ghost"
            className="size-7 gap-1 text-muted-foreground hover:text-primary"
            title={`View material (${list.length})`}
            onClick={() => setViewerOpen(true)}
          >
            <Paperclip className="size-3.5" />
            <span className="text-[10px] font-semibold">{list.length}</span>
          </Button>
        )}
        {!hideUpload && (
          <Button
            size={labeled ? "sm" : "icon"}
            variant={labeled ? "outline" : "ghost"}
            className={cn(
              labeled ? "h-7 gap-1.5 text-xs" : "size-7",
              "transition-colors",
              justUploaded ? "text-emerald-500" : error ? "text-destructive" : "text-muted-foreground hover:text-primary",
            )}
            title={error ?? (upload.isPending ? "Uploading…" : "Upload course material (PDF / image / MP4)")}
            onClick={() => inputRef.current?.click()}
            disabled={upload.isPending}
          >
            {upload.isPending ? (
              <Loader2 className={cn("animate-spin", labeled ? "size-3.5" : "size-3.5")} />
            ) : justUploaded ? (
              <Check className={labeled ? "size-3.5" : "size-3.5"} />
            ) : error ? (
              <CircleAlert className={labeled ? "size-3.5" : "size-3.5"} />
            ) : (
              <Upload className={labeled ? "size-3.5" : "size-3.5"} />
            )}
            {labeled ? (justUploaded ? " Added" : " Add material") : null}
          </Button>
        )}
        {error && !hideUpload && <span className="sr-only">{error}</span>}
        <input ref={inputRef} type="file" accept="application/pdf,image/jpeg,image/png,image/webp,image/gif,video/mp4" className="hidden" onChange={onPick} />
      </div>

      {viewerOpen && list.length > 0 && (
        <MaterialViewer
          open={viewerOpen}
          onOpenChange={setViewerOpen}
          itemTitle={itemTitle}
          materials={list}
        />
      )}
    </>
  )
}

/** Inline material chips (admin) — name + size + view + delete. */
export function MaterialChips({ materials }: { materials?: CurriculumMaterial[] }) {
  const del = useDeleteMaterial()
  const [viewerOpen, setViewerOpen] = useState(false)
  const [viewer, setViewer] = useState<CurriculumMaterial | null>(null)
  const list = materials ?? []
  if (list.length === 0) return null

  return (
    <>
      <div className="mt-1.5 flex flex-wrap items-center gap-1.5">
        {list.map((m) => (
          <span key={m.id} className="flex items-center gap-1 rounded-full border bg-background px-2 py-0.5 text-[11px]">
            <FileText className="size-3 text-muted-foreground" />
            <button
              type="button"
              className="max-w-40 cursor-pointer truncate text-muted-foreground hover:text-primary"
              title="View"
              onClick={() => {
                setViewer(m)
                setViewerOpen(true)
              }}
            >
              {m.file_name}
            </button>
            <span className="text-muted-foreground/60">{fmtSize(m.file_size)}</span>
            <a
              href={`/api/serve_material.php?mid=${m.id}&dl=1`}
              download
              className="text-muted-foreground/60 transition-colors hover:text-primary"
              title="Download (original)"
            >
              <Download className="size-3" />
            </a>
            <button
              type="button"
              className="cursor-pointer text-muted-foreground/60 transition-colors hover:text-destructive"
              title="Delete material"
              onClick={() => { if (window.confirm("Delete this material?")) del.mutate(m.id) }}
            >
              <Trash2 className="size-3" />
            </button>
          </span>
        ))}
      </div>
      {viewer && (
        <MaterialViewer open={viewerOpen} onOpenChange={setViewerOpen} itemTitle={viewer.file_name} materials={[viewer]} />
      )}
    </>
  )
}
