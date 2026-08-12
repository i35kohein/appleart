import { useState } from "react"
import { NavLink, Outlet, useLocation, useNavigate } from "react-router-dom"
import { Banknote, CalendarClock, CalendarDays, ClipboardList, Contact, CreditCard, FileText, GraduationCap, History, LayoutDashboard, LogOut, Menu, MonitorPlay, Moon, PanelLeftClose, PanelLeftOpen, Settings, Sun, Sparkles, Users } from "lucide-react"
import { useMe, useLogout } from "@/features/auth/api"
import { Avatar, AvatarFallback } from "@/components/ui/avatar"
import { Button } from "@/components/ui/button"
import { Sheet, SheetContent, SheetTitle, SheetTrigger } from "@/components/ui/sheet"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { Input } from "@/components/ui/input"
import { cn, initials } from "@/lib/utils"
import { useTimeMachine, todayStr } from "@/lib/timemachine"
import { AiChatDialog } from "@/components/ai/AiChatDialog"
import { applyTheme, getStoredTheme } from "@/lib/themes"

const NAV = [
  { to: "/", label: "Dashboard", icon: LayoutDashboard, end: true },
  { to: "/students", label: "Trainees", icon: Users },
  { to: "/rollcall", label: "Roll Call", icon: ClipboardList },
  { to: "/courses", label: "Courses", icon: FileText },
  { to: "/calendar", label: "Calendar", icon: CalendarDays },
  { to: "/today", label: "Today Screen", icon: MonitorPlay },
  { to: "/exams", label: "Exams", icon: GraduationCap },
  { to: "/payments", label: "Payments", icon: CreditCard },
  { to: "/finance", label: "Finance", icon: Banknote },
  { to: "/contacts", label: "Contacts", icon: Contact },
]

function TimeMachinePicker() {
  const { date, setDate, isTimeTraveling, reset } = useTimeMachine()
  const [open, setOpen] = useState(false)
  return (
    <DropdownMenu open={open} onOpenChange={setOpen}>
      <DropdownMenuTrigger asChild>
        <Button
          variant={isTimeTraveling ? "outline" : "ghost"}
          size="sm"
          className={cn("h-9 gap-1.5 px-2.5 text-xs font-medium", isTimeTraveling && "border-amber-400 bg-amber-50 text-amber-700 dark:border-amber-600 dark:bg-amber-950 dark:text-amber-300")}
          aria-label="Time Machine — working date"
        >
          {isTimeTraveling ? <History className="size-4" /> : <CalendarClock className="size-4" />}
          <span className="tabular-nums">{date}</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-72 p-3">
        <DropdownMenuLabel className="px-1 pb-2">
          <div className="flex items-center gap-2 text-sm font-semibold">
            <History className="size-4" /> Time Machine
          </div>
          <div className="text-xs font-normal text-muted-foreground">
            {isTimeTraveling
              ? `Working as ${date} — new entries use this date`
              : "Pick a date — new entries will use it"}
          </div>
        </DropdownMenuLabel>
        <div className="space-y-2">
          <Input
            type="date"
            value={date}
            onChange={(e) => e.target.value && setDate(e.target.value)}
            aria-label="Working date"
          />
          <div className="flex items-center justify-between gap-2">
            <span className="text-[11px] text-muted-foreground">Today: {todayStr()}</span>
            <Button
              variant="outline"
              size="sm"
              className="h-7 text-xs"
              disabled={!isTimeTraveling}
              onClick={() => {
                reset()
                setOpen(false)
              }}
            >
              Back to today
            </Button>
          </div>
        </div>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}

function ThemeToggle() {
  const [dark, setDark] = useState(() => document.documentElement.classList.contains("dark"))
  return (
    <Button
      variant="ghost"
      size="icon"
      aria-label="Toggle theme"
      onClick={() => {
        const next = !dark
        setDark(next)
        document.documentElement.classList.toggle("dark", next)
        localStorage.setItem("appleart_theme", next ? "dark" : "light")
        applyTheme(getStoredTheme())
      }}
    >
      {dark ? <Sun className="size-4" /> : <Moon className="size-4" />}
    </Button>
  )
}

function UserMenu() {
  const { data: me } = useMe()
  const logout = useLogout()
  const navigate = useNavigate()
  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="ghost" className="h-9 gap-2 px-2">
          <Avatar className="size-7">
            <AvatarFallback className="bg-primary/10 text-primary text-[10px] dark:bg-primary/20 dark:text-primary">{initials(me?.name ?? "?")}</AvatarFallback>
          </Avatar>
          <span className="hidden text-sm font-medium sm:inline">{me?.name ?? "…"}</span>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-52">
        <DropdownMenuLabel>
          <div className="text-sm font-medium">{me?.name}</div>
          <div className="text-xs font-normal text-muted-foreground">{me?.email}</div>
        </DropdownMenuLabel>
        <DropdownMenuSeparator />
        {(me?.role === "admin" || me?.role === "master_admin") && (
          <DropdownMenuItem onClick={() => navigate("/admin")}>
            <Settings className="size-4" /> Settings
          </DropdownMenuItem>
        )}
        <DropdownMenuItem onClick={() => window.open("/student", "_blank")}>
          <GraduationCap className="size-4" /> Student Portal
        </DropdownMenuItem>
        <DropdownMenuItem
          variant="destructive"
          onClick={() => logout.mutate(undefined, { onSuccess: () => navigate("/login") })}
        >
          <LogOut className="size-4" /> Sign out
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}

function SidebarContent({ onNavigate, collapsed = false }: { onNavigate?: () => void; collapsed?: boolean }) {
  return (
    <div className="flex h-full flex-col">
      <div className={cn("flex h-14 items-center gap-2.5", collapsed ? "justify-center px-0" : "px-5")}>
        <span className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-brand-blue via-brand-teal to-brand-purple text-white shadow-sm">
          <GraduationCap className="size-4.5" />
        </span>
        {!collapsed && (
          <div className="leading-tight">
            <div className="text-sm font-semibold tracking-tight">Apple Art</div>
            <div className="text-[11px] text-muted-foreground">Academy</div>
          </div>
        )}
      </div>
      <nav className="flex-1 space-y-0.5 overflow-y-auto px-3 py-2">
        {NAV.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            end={item.end}
            onClick={onNavigate}
            title={collapsed ? item.label : undefined}
            className={({ isActive }) =>
              cn(
                "flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors",
                collapsed && "justify-center px-0",
                isActive
                  ? "bg-primary/10 text-primary dark:bg-primary/15 dark:text-primary"
                  : "text-muted-foreground hover:bg-accent hover:text-foreground",
              )
            }
          >
            <item.icon className="size-4 shrink-0" />
            {!collapsed && item.label}
          </NavLink>
        ))}
      </nav>
      {!collapsed && (
        <div className="border-t p-3">
          <div className="rounded-lg bg-muted/60 px-3 py-2.5 text-xs text-muted-foreground">
            <div className="font-medium text-foreground">Repair training</div>
            basic → advanced, daily
          </div>
        </div>
      )}
    </div>
  )
}

export function AppShell() {
  const location = useLocation()
  const [aiOpen, setAiOpen] = useState(false)
  const [collapsed, setCollapsed] = useState<boolean>(() => {
    try {
      return localStorage.getItem("appleart_sidebar_collapsed") === "1"
    } catch {
      return false
    }
  })

  const toggleSidebar = () => {
    setCollapsed((c) => {
      const next = !c
      try {
        localStorage.setItem("appleart_sidebar_collapsed", next ? "1" : "0")
      } catch {
        /* ignore */
      }
      return next
    })
  }

  // Current tab name shown in the topbar (route → label)
  const pageTitle =
    location.pathname.startsWith("/students/") ? "Trainee profile" :
    location.pathname === "/admin" ? "System admin" :
    NAV.find((n) => (n.end ? location.pathname === n.to : location.pathname.startsWith(n.to)))?.label ?? ""

  return (
    <div className="flex h-svh w-full overflow-hidden bg-muted/30">
        {/* Desktop / tablet sidebar */}
        <aside
          className={cn(
            "hidden shrink-0 border-r bg-sidebar transition-[width] duration-200 md:block",
            collapsed ? "w-14" : "w-56",
          )}
        >
          <SidebarContent collapsed={collapsed} />
        </aside>

        {/* Main column */}
        <div className="flex min-w-0 flex-1 flex-col">
          {/* Topbar */}
          <header className="flex h-14 shrink-0 items-center justify-between gap-3 border-b bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/80">
            <div className="flex items-center gap-2">
              {/* Mobile nav trigger */}
              <Sheet>
                <SheetTrigger asChild>
                  <Button variant="ghost" size="icon" className="md:hidden" aria-label="Open navigation">
                    <Menu className="size-5" />
                  </Button>
                </SheetTrigger>
                <SheetContent side="left" className="w-64 p-0">
                  <SheetTitle className="sr-only">Navigation</SheetTitle>
                  <SidebarContent />
                </SheetContent>
              </Sheet>
              {/* Desktop sidebar collapse toggle */}
              <Button
                variant="ghost"
                size="icon"
                className="hidden md:inline-flex"
                onClick={toggleSidebar}
                aria-label={collapsed ? "Expand sidebar" : "Collapse sidebar"}
              >
                {collapsed ? <PanelLeftOpen className="size-4.5" /> : <PanelLeftClose className="size-4.5" />}
              </Button>
              {/* Current page / tab name header */}
              <span className="min-w-0 truncate text-sm font-semibold tracking-tight">{pageTitle}</span>
            </div>
            <div className="flex items-center gap-1.5">
              <TimeMachinePicker />
              <Button variant="ghost" size="icon" aria-label="AI assistant" onClick={() => setAiOpen(true)} className="text-primary">
                <Sparkles className="size-4" />
              </Button>
              <ThemeToggle />
              <UserMenu />
            </div>
          </header>
          <AiChatDialog open={aiOpen} onOpenChange={setAiOpen} />

          {/* Content */}
          <main className="flex-1 overflow-y-auto">
            <div className="mx-auto w-full max-w-[1400px] p-4 md:p-6 lg:p-8">
              <Outlet />
            </div>
          </main>
        </div>
      </div>
  )
}
