import * as React from "react"
import { cn } from "@/lib/utils"

function Avatar({ className, ...props }: React.ComponentProps<"div">) {
  return <div data-slot="avatar" className={cn("relative flex size-9 shrink-0 overflow-hidden rounded-full", className)} {...props} />
}

function AvatarImage({ className, ...props }: React.ComponentProps<"img">) {
  return <img data-slot="avatar-image" className={cn("aspect-square size-full object-cover", className)} {...props} />
}

function AvatarFallback({ className, ...props }: React.ComponentProps<"div">) {
  return (
    <div
      data-slot="avatar-fallback"
      className={cn("bg-muted flex size-full items-center justify-center rounded-full text-xs font-medium", className)}
      {...props}
    />
  )
}

function StudentAvatar({ name, photoPath, className }: { name: string; photoPath?: string | null; className?: string }) {
  const photoUrl = photoPath ? `/uploads/${photoPath.replace(/^.*[\\/]/, "")}` : null
  const [imgError, setImgError] = React.useState(false)
  // No photo (or photo failed to load) → show the shop logo as placeholder.
  const src = photoUrl && !imgError ? photoUrl : "/logo.png"
  return (
    <Avatar className={className}>
      <AvatarImage src={src} alt={name} onError={() => setImgError(true)} />
    </Avatar>
  )
}

export { Avatar, AvatarImage, AvatarFallback, StudentAvatar }
