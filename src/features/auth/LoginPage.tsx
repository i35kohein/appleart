import { useState } from "react"
import { useNavigate } from "react-router-dom"
import { z } from "zod"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { GraduationCap, Loader2 } from "lucide-react"
import { useLogin } from "./api"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Alert, AlertDescription } from "@/components/ui/alert"

const schema = z.object({
  email: z.string().email("Enter a valid email"),
  password: z.string().min(1, "Password is required"),
})

type FormValues = z.infer<typeof schema>

export function LoginPage() {
  const navigate = useNavigate()
  const login = useLogin()
  const [error, setError] = useState<string | null>(null)
  const [need2fa, setNeed2fa] = useState(false)
  const [totpCode, setTotpCode] = useState("")
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")

  const form = useForm<FormValues>({
    resolver: zodResolver(schema),
    defaultValues: { email: "", password: "" },
  })

  async function onSubmit(values: FormValues) {
    setError(null)
    setEmail(values.email)
    setPassword(values.password)
    login.mutate(values, {
      onSuccess: (data) => {
        if (data.status === "2fa_required") {
          setNeed2fa(true)
        } else {
          navigate("/", { replace: true })
        }
      },
      onError: (e) => setError(e instanceof Error ? e.message : "Login failed"),
    })
  }

  async function submit2fa() {
    setError(null)
    login.mutate(
      { email, password, totp_code: totpCode },
      {
        onSuccess: () => navigate("/", { replace: true }),
        onError: (e) => setError(e instanceof Error ? e.message : "Login failed"),
      },
    )
  }

  return (
    <div className="flex min-h-svh items-center justify-center bg-gradient-to-br from-primary/10 via-background to-teal-50 p-4 dark:from-zinc-950 dark:via-zinc-950 dark:to-zinc-900">
      <div className="grid w-full max-w-4xl gap-8 lg:grid-cols-2 lg:items-center">
        {/* Brand side */}
        <div className="hidden lg:block">
          <div className="mb-4 flex size-14 items-center justify-center rounded-2xl bg-primary text-primary-foreground shadow-lg shadow-primary/20">
            <GraduationCap className="size-8" />
          </div>
          <h1 className="text-3xl font-semibold tracking-tight">Apple Art Academy</h1>
          <p className="mt-2 max-w-sm text-muted-foreground">
            Training management for the next generation of device repair technicians —
            theory, practical, roll call and payments in one place.
          </p>
          <ul className="mt-6 space-y-2 text-sm text-muted-foreground">
            <li className="flex items-center gap-2"><span className="size-1.5 rounded-full bg-primary" /> Daily course &amp; practical plans per trainee</li>
            <li className="flex items-center gap-2"><span className="size-1.5 rounded-full bg-primary" /> Live roll call and attendance history</li>
            <li className="flex items-center gap-2"><span className="size-1.5 rounded-full bg-primary" /> Progress from basic to advanced</li>
          </ul>
        </div>

        {/* Form side */}
        <Card className="border-none shadow-xl shadow-zinc-900/5">
          <CardHeader>
            <CardTitle className="text-xl">Admin Panel</CardTitle>
            <CardDescription>i35 Apple Service staff account နဲ့ ဝင်ပါ။</CardDescription>
          </CardHeader>
          <CardContent>
            {need2fa ? (
              <form
                onSubmit={(e) => {
                  e.preventDefault()
                  void submit2fa()
                }}
                className="space-y-4"
              >
                <div className="rounded-lg border bg-muted/30 p-3 text-sm">
                  <p className="font-medium">Two-factor authentication</p>
                  <p className="mt-1 text-xs text-muted-foreground">
                    Authenticator app ကရတဲ့ 6 လုံးကုဒ်ကို ရိုက်ထည့်ပါ။
                  </p>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="totp-code">Authenticator code</Label>
                  <Input
                    id="totp-code"
                    inputMode="numeric"
                    maxLength={6}
                    placeholder="000000"
                    autoFocus
                    value={totpCode}
                    onChange={(e) => setTotpCode(e.target.value.replace(/\D/g, ""))}
                  />
                </div>
                {error && (
                  <Alert variant="destructive">
                    <AlertDescription>{error}</AlertDescription>
                  </Alert>
                )}
                <Button type="submit" className="w-full" disabled={login.isPending || totpCode.length !== 6}>
                  {login.isPending && <Loader2 className="size-4 animate-spin" />}
                  Verify
                </Button>
                <Button type="button" variant="ghost" size="sm" className="w-full text-xs" onClick={() => setNeed2fa(false)}>
                  ← Back to sign in
                </Button>
              </form>
            ) : (
              <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4" noValidate>
                <div className="space-y-2">
                  <Label htmlFor="email">Email</Label>
                  <Input id="email" type="email" placeholder="you@appleart.com" autoComplete="email" {...form.register("email")} />
                  {form.formState.errors.email && (
                    <p className="text-xs text-destructive">{form.formState.errors.email.message}</p>
                  )}
                </div>
                <div className="space-y-2">
                  <Label htmlFor="password">Password</Label>
                  <Input id="password" type="password" placeholder="••••••••" autoComplete="current-password" {...form.register("password")} />
                  {form.formState.errors.password && (
                    <p className="text-xs text-destructive">{form.formState.errors.password.message}</p>
                  )}
                </div>

                {error && (
                  <Alert variant="destructive">
                    <AlertDescription>{error}</AlertDescription>
                  </Alert>
                )}

                <Button type="submit" className="w-full" disabled={login.isPending}>
                  {login.isPending && <Loader2 className="size-4 animate-spin" />}
                  Sign in
                </Button>
              </form>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
