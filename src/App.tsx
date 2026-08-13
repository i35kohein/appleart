import { Suspense, lazy } from "react"
import { createBrowserRouter, Navigate, RouterProvider } from "react-router-dom"
import { QueryClient, QueryClientProvider } from "@tanstack/react-query"
import { ToastProvider } from "@/components/ui/toast"
import { Loader2 } from "lucide-react"
import { AppShell } from "@/components/layout/AppShell"
import { LoginPage } from "@/features/auth/LoginPage"
import { useMe } from "@/features/auth/api"
import { TimeMachineProvider } from "@/lib/timemachine"

// Route-level code splitting — each feature page loads on demand.
const StudentPortalPage = lazy(() => import("@/features/student/StudentPortalPage").then((m) => ({ default: m.StudentPortalPage })))
const RoleSelectPage = lazy(() => import("@/features/auth/pages/RoleSelectPage").then((m) => ({ default: m.RoleSelectPage })))
const DashboardPage = lazy(() => import("@/features/dashboard/DashboardPage").then((m) => ({ default: m.DashboardPage })))
const StudentsPage = lazy(() => import("@/features/students/pages/StudentsPage").then((m) => ({ default: m.StudentsPage })))
const StudentDetailPage = lazy(() => import("@/features/students/pages/StudentDetailPage").then((m) => ({ default: m.StudentDetailPage })))
const RollCallPage = lazy(() => import("@/features/rollcall/pages/RollCallPage").then((m) => ({ default: m.RollCallPage })))
const CoursesPage = lazy(() => import("@/features/courses/pages/CoursesPage").then((m) => ({ default: m.CoursesPage })))
const PaymentsPage = lazy(() => import("@/features/payments/pages/PaymentsPage").then((m) => ({ default: m.PaymentsPage })))
const ExamsPage = lazy(() => import("@/features/exams/pages/ExamsPage").then((m) => ({ default: m.ExamsPage })))
const FinancePage = lazy(() => import("@/features/finance/pages/FinancePage").then((m) => ({ default: m.FinancePage })))
const CalendarPage = lazy(() => import("@/features/calendar/pages/CalendarPage").then((m) => ({ default: m.CalendarPage })))
const TodayScreenPage = lazy(() => import("@/features/today/pages/TodayScreenPage").then((m) => ({ default: m.TodayScreenPage })))
const ContactsPage = lazy(() => import("@/features/contacts/pages/ContactsPage").then((m) => ({ default: m.ContactsPage })))
const AdminPage = lazy(() => import("@/features/admin/pages/AdminPage").then((m) => ({ default: m.AdminPage })))
const TeachingLogPage = lazy(() => import("@/features/teachinglog/TeachingLogPage").then((m) => ({ default: m.TeachingLogPage })))

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 30_000,
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
})

function PageLoader() {
  return (
    <div className="flex h-[50vh] items-center justify-center">
      <Loader2 className="size-5 animate-spin text-muted-foreground" />
    </div>
  )
}

function RequireAuth({ children }: { children: React.ReactNode }) {
  const me = useMe()
  if (me.isLoading) {
    return (
      <div className="flex h-svh items-center justify-center">
        <Loader2 className="size-6 animate-spin text-muted-foreground" />
      </div>
    )
  }
  if (me.isError || !me.data) {
    return <Navigate to="/welcome" replace />
  }
  return children
}

const withSuspense = (el: React.ReactNode) => <Suspense fallback={<PageLoader />}>{el}</Suspense>

const router = createBrowserRouter([
  { path: "/welcome", element: withSuspense(<RoleSelectPage />) },
  { path: "/login", element: <LoginPage /> },
  { path: "/student", element: withSuspense(<StudentPortalPage />) },
  {
    path: "/",
    element: (
      <RequireAuth>
        <AppShell />
      </RequireAuth>
    ),
    children: [
      { index: true, element: withSuspense(<DashboardPage />) },
      { path: "students", element: withSuspense(<StudentsPage />) },
      { path: "students/:id", element: withSuspense(<StudentDetailPage />) },
      { path: "rollcall", element: withSuspense(<RollCallPage />) },
      { path: "courses", element: withSuspense(<CoursesPage />) },
      { path: "payments", element: withSuspense(<PaymentsPage />) },
      { path: "finance", element: withSuspense(<FinancePage />) },
      { path: "exams", element: withSuspense(<ExamsPage />) },
      { path: "calendar", element: withSuspense(<CalendarPage />) },
      { path: "today", element: withSuspense(<TodayScreenPage />) },
      { path: "teachinglog", element: withSuspense(<TeachingLogPage />) },
      { path: "contacts", element: withSuspense(<ContactsPage />) },
      { path: "admin", element: withSuspense(<AdminPage />) },
    ],
  },
  { path: "*", element: <Navigate to="/" replace /> },
])

export function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <TimeMachineProvider>
        <ToastProvider>
          <RouterProvider router={router} />
        </ToastProvider>
      </TimeMachineProvider>
    </QueryClientProvider>
  )
}
