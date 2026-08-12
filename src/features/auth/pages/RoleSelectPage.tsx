import { Link } from "react-router-dom"
import { BookOpen, Check, ChevronRight, Clock, FileText, GraduationCap, LineChart, Mail, MapPin, MonitorPlay, Phone, Sparkles, Users, Wrench } from "lucide-react"
import { Button } from "@/components/ui/button"

const NAV = [
  ["#academy", "Academy"],
  ["#onsite", "On-site"],
  ["#online", "Online Learning"],
  ["#courses", "Courses"],
  ["#pricing", "Pricing"],
  ["#contact", "Contact"],
] as const

const COURSES = [
  {
    icon: <BookOpen className="size-5" />,
    title: "Course — Theory",
    lessons: 25,
    desc: "iOS System, iOS Software, Schematic & Error — iPhone ရဲ့ အခြေခံကစပြီး အဆင့်မြင့် နားလည်မှု",
    topics: ["iOS System & Software", "Schematic & Error Analysis", "Spare Parts"],
  },
  {
    icon: <Wrench className="size-5" />,
    title: "Practical — Hands-on",
    lessons: 33,
    desc: "Hardware Tools ကစပြီး Chip Level, Screen Repair, Message ဖျောက်ခြင်း — လက်တွေ့ကျွမ်းကျင်မှု",
    topics: ["Hardware Tools & Hardware", "Chip Level Repair", "Screen & Message"],
  },
]

const ONLINE_POINTS = [
  "58 lessons — Course + Practical",
  "Course Material PDF — browser မှာ ဖတ်လို့ရ",
  "Certificate — သင်တန်းပြီးဆုံးသူများ",
  "Progress မှတ်တမ်း — ကိုယ့်တိုးတက်မှု",
  "ဖုန်း/တက်ဘလက်/ကွန်ပျူတာ — အချိန်မရွေး",
  "Subscription နဲ့ လေ့လာလို့ရ",
]

const FEATURES = [
  { icon: <BookOpen className="size-5" />, title: "58 Lessons", desc: "Course + Practical — အခြေခံကနေ Chip Level အထိ" },
  { icon: <FileText className="size-5" />, title: "Material PDF", desc: "သင်ခန်းစာတိုင်းရဲ့ material — browser ထဲမှာတင် ဖတ်လို့ရ" },
  { icon: <GraduationCap className="size-5" />, title: "Certificate", desc: "သင်တန်းပြီးဆုံးသူတွေအတွက် Certificate ထောက်ခံချက်" },
  { icon: <LineChart className="size-5" />, title: "Progress Tracking", desc: "ပြီးတဲ့သင်ခန်းစာ၊ လုပ်ဆဲ — ရှင်းရှင်းမြင်ရ" },
  { icon: <Users className="size-5" />, title: "Expert Trainers", desc: "i35 Apple Service ရဲ့ အတွေ့အကြုံရင့် နည်းပြတွေ" },
  { icon: <MonitorPlay className="size-5" />, title: "On-site + Online", desc: "ကျောင်းမှာရော၊ အွန်လိုင်းကရော သင်ယူလို့ရ" },
]

const PLANS = [
  { name: "Monthly", features: ["58 lessons အကုန်", "Material PDF အကုန်", "Progress tracking", "အချိန်မရွေး"], highlight: false },
  { name: "Quarterly", features: ["Monthly အကုန်ပါ", "လစဉ်ထက် သက်သာ", "ဦးစားပေး support", "Certificate"], highlight: true },
  { name: "Yearly", features: ["Quarterly အကုန်ပါ", "အသက်သာဆုံး", "အတန်းအသစ်ပါ", "1:1 လမ်းညွှန်"], highlight: false },
]

const FAQS = [
  { q: "Apple Art ဆိုတာ ဘာလဲ?", a: "i35 Apple Service ရဲ့ iPhone Repair သင်တန်းကျောင်းပါ — လူကိုယ်တိုင် သင်တန်းနဲ့ Online Learning နှစ်မျိုးလုံး ရှိပါတယ်။" },
  { q: "Online Learning နဲ့ ဘာတွေ လေ့လာရမလဲ?", a: "Course + Practical သင်ခန်းစာ ၅၈ ခု — material PDF တွေနဲ့တကွ ကိုယ့်အချိန်နဲ့ကိုယ် သင်ယူနိုင်ပါတယ်။" },
  { q: "Material တွေ download လို့ရလား?", a: "မရပါ — watermark ပါပြီး browser ထဲမှာပဲ ကြည့်ရတာ၊ ကူးယူခြင်းကနေ ကာကွယ်ထားပါတယ်။" },
  { q: "Subscription ဘယ်လိုဝယ်ရလဲ?", a: "အောက်က ဆက်သွယ်ရန် နံပါတ်ကနေ မေးမြန်းလို့ရပါတယ် — ပေးချေပြီးတာနဲ့ ချက်ချင်း access ရပါတယ်။" },
]

function AppleLogo({ className = "h-9 w-9" }: { className?: string }) {
  return <img src="/logo.png" alt="Apple Art" className={`${className} rounded-xl object-cover`} />
}

export function RoleSelectPage() {
  return (
    <div className="flex min-h-svh flex-col bg-white text-black dark:bg-zinc-950 dark:text-white">
      {/* ===== Navbar ===== */}
      <header className="sticky top-0 z-30 border-b border-zinc-200/70 bg-white/80 backdrop-blur-xl dark:border-zinc-800 dark:bg-zinc-950/80">
        <div className="mx-auto flex h-14 max-w-6xl items-center justify-between gap-3 px-4">
          <a href="#top" className="flex items-center gap-2.5">
            <AppleLogo />
            <span className="text-[15px] font-semibold tracking-tight">Apple Art</span>
          </a>
          <nav className="hidden items-center gap-7 md:flex">
            {NAV.map(([href, label]) => (
              <a key={href} href={href} className="text-[13px] text-zinc-600 transition-colors hover:text-black dark:text-zinc-400 dark:hover:text-white">
                {label}
              </a>
            ))}
          </nav>
          <Button asChild className="h-8 rounded-full bg-black px-4 text-xs text-white hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200">
            <Link to="/student">
              <Sparkles className="size-3.5" /> Online login
            </Link>
          </Button>
        </div>
      </header>

      {/* ===== Hero ===== */}
      <section id="top" className="mx-auto max-w-6xl px-4 pb-20 pt-16 text-center sm:pt-24">
        <span className="inline-flex items-center gap-1.5 text-[13px] font-medium text-zinc-500">
          <GraduationCap className="size-4" /> Apple Art — i Device Repair Training Academy
        </span>
        <h1 className="mx-auto mt-4 max-w-3xl text-5xl font-semibold leading-[1.05] tracking-tight sm:text-7xl">
          iPhone Repair.
          <br />
          <span className="text-zinc-400">Learn it. Master it.</span>
        </h1>
        <p className="mx-auto mt-6 max-w-xl text-base text-zinc-500 sm:text-lg">
          ကျောင်းမှာ လူကိုယ်တိုင် တက်ရောက်လို့ရသလို — <span className="font-medium text-black dark:text-white">Online Learning</span> နဲ့ အွန်လိုင်းကနေလည်း သင်ယူနိုင်ပါတယ်။
        </p>
        <div className="mt-9 flex flex-wrap items-center justify-center gap-3">
          <Button asChild size="lg" className="h-11 rounded-full bg-black px-7 text-sm text-white hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200">
            <Link to="/student?signup=1">
              <MonitorPlay className="size-4" /> Start Online Learning
            </Link>
          </Button>
          <Button asChild size="lg" variant="ghost" className="h-11 rounded-full px-5 text-sm text-black hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-900">
            <a href="#online">
              Learn more <ChevronRight className="size-4" />
            </a>
          </Button>
        </div>
        <div className="mx-auto mt-14 grid max-w-md grid-cols-3 gap-6">
          {[
            ["58+", "Online Lessons"],
            ["7", "Sections"],
            ["Since 2015", "Experience"],
          ].map(([n, l]) => (
            <div key={l}>
              <div className="text-2xl font-semibold tabular-nums tracking-tight sm:text-3xl">{n}</div>
              <div className="mt-1 text-xs text-zinc-500">{l}</div>
            </div>
          ))}
        </div>
      </section>

      {/* ===== Academy ===== */}
      <section id="academy" className="bg-[#f5f5f7] py-20 dark:bg-zinc-900">
        <div className="mx-auto max-w-6xl px-4">
          <div className="grid items-center gap-12 md:grid-cols-2">
            <div>
              <h2 className="text-3xl font-semibold tracking-tight sm:text-4xl">Apple Art Academy</h2>
              <p className="mt-4 text-base leading-relaxed text-zinc-600 dark:text-zinc-400">
                i35 Apple Service ရဲ့ iPhone ပြုပြင်ရေး သင်တန်းကျောင်း။ 2015 ကတည်းက လက်တွေ့ အတွေ့အကြုံရင့် ဆရာတွေက သင်ကြားပေးပါတယ် — သင်ရိုးက သီအိုရီကအစ Chip Level လက်တွေ့အထိ။
              </p>
              <ul className="mt-6 space-y-3">
                {[
                  "On-site classes — North Dagon, Yangon",
                  "Online Learning — အွန်လိုင်းကနေ အချိန်မရွေး",
                  "Certificate for graduates",
                ].map((t) => (
                  <li key={t} className="flex items-center gap-2.5 text-[15px] text-zinc-700 dark:text-zinc-300">
                    <Check className="size-4 shrink-0 text-emerald-500" /> {t}
                  </li>
                ))}
              </ul>
            </div>
            <div className="rounded-3xl bg-white p-8 shadow-sm ring-1 ring-zinc-200/60 dark:bg-zinc-950 dark:ring-zinc-800">
              <AppleLogo className="h-16 w-16" />
              <h3 className="mt-5 text-xl font-semibold tracking-tight">Trainers</h3>
              <p className="mt-2 text-sm leading-relaxed text-zinc-500">
                ဆရာတွေက နေ့စဉ် iPhone ပြုပြင်မှုတွေ လက်တွေ့ကိုင်တွယ်နေသူတွေပါ — real-world ပြဿနာတွေနဲ့ ဖြေရှင်းနည်းတွေပါ သင်ရိုးထဲမှာ ပါဝင်ပါတယ်။
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* ===== On-site Hands-on Training ===== */}
      <section id="onsite" className="py-20">
        <div className="mx-auto max-w-6xl px-4">
          <div className="text-center">
            <h2 className="text-3xl font-semibold tracking-tight sm:text-4xl">On-site Hands-on Training</h2>
            <p className="mx-auto mt-2 max-w-md text-[15px] text-zinc-500">
              ကျောင်းမှာ လူကိုယ်တိုင် တက်ရောက်တဲ့ လက်တွေ့သင်တန်း — iPhone အစစ်တွေနဲ့ ကိုယ်တိုင်လုပ်ပြီး သင်ယူပါ
            </p>
          </div>
          <div className="mt-12 grid items-center gap-10 md:grid-cols-2">
            <div className="rounded-3xl bg-black p-8 text-white dark:bg-zinc-950">
              <div className="flex items-center gap-3">
                <span className="flex size-10 items-center justify-center rounded-full bg-white/10">
                  <Wrench className="size-5" />
                </span>
                <h3 className="text-xl font-semibold tracking-tight">Apple Art — North Dagon</h3>
              </div>
              <ul className="mt-6 space-y-3.5">
                {[
                  "iPhone အစစ်တွေနဲ့ လက်တွေ့ ပြုပြင်လေ့ကျင့်",
                  "ဆရာက ဘေးမှာ လိုက်ပြီး 1:1 လမ်းညွှန်",
                  "Tools / Equipment အပြည့် — ကျောင်းမှာ ပါ",
                  "Chip Level ကအစ Screen Repair အထိ လက်တွေ့",
                  "ပြီးဆုံးသူတွေအတွက် Certificate",
                ].map((t) => (
                  <li key={t} className="flex items-start gap-2.5 text-sm text-zinc-300">
                    <Check className="mt-0.5 size-4 shrink-0 text-emerald-400" /> {t}
                  </li>
                ))}
              </ul>
              <div className="mt-7 rounded-2xl bg-white/5 px-4 py-3 text-xs text-zinc-400">
                <span className="font-semibold text-white">Hours</span> — Tue–Sat 09:00–18:00 · Mon ပိတ်
              </div>
            </div>
            <div className="space-y-5">
              <div className="rounded-3xl bg-[#f5f5f7] p-7 dark:bg-zinc-900">
                <h3 className="text-lg font-semibold tracking-tight">ဘယ်လိုလဲ</h3>
                <p className="mt-2 text-sm leading-relaxed text-zinc-500">
                  Online Learning က ကိုယ့်အချိန်နဲ့ကိုယ် သီအိုရီ/အခြေခံကို သင်ယူပြီး — On-site သင်တန်းကတော့ ကျောင်းမှာ လာရောက်ပြီး iPhone အစစ်တွေနဲ့ လက်တွေ့ ကျွမ်းကျင်မှုကို တည်ဆောက်တာပါ။ နှစ်မျိုးလုံး ပေါင်းတက်လို့ရပါတယ်။
                </p>
              </div>
              <div className="rounded-3xl bg-[#f5f5f7] p-7 dark:bg-zinc-900">
                <h3 className="text-lg font-semibold tracking-tight">ဘယ်သူတွေ တက်လို့ရလဲ</h3>
                <p className="mt-2 text-sm leading-relaxed text-zinc-500">
                  စတင်လေ့လာသူကအစ လက်ရှိ ပြုပြင်သူအထိ — class အသေးနဲ့ သင်ကြားပေးတာမို့ ဆရာနဲ့ အနီးကပ် လေ့ကျင့်ခွင့်ရပါတယ်။
                </p>
              </div>
              <Button asChild size="lg" className="h-11 w-full rounded-full bg-black text-sm text-white hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200">
                <a href="#contact">
                  သင်တန်းအကြောင်း မေးမြန်းရန် <ChevronRight className="size-4" />
                </a>
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* ===== Online Learning (dark, Apple-style product section) ===== */}
      <section id="online" className="bg-black py-24 text-white dark:bg-zinc-950">
        <div className="mx-auto max-w-6xl px-4 text-center">
          <p className="text-sm font-semibold uppercase tracking-widest text-zinc-500">Online Learning</p>
          <h2 className="mx-auto mt-3 max-w-2xl text-4xl font-semibold leading-tight tracking-tight sm:text-5xl">
            Apple Art Online Learning.
          </h2>
          <p className="mx-auto mt-4 max-w-xl text-base text-zinc-400">
            သင်ခန်းစာ ၅၈ ခု၊ material PDF တွေနဲ့တကွ — ဘယ်အချိန် ဘယ်နေရာမှာမဆို သင်ယူနိုင်ပါပြီ။
          </p>
          <div className="mx-auto mt-10 grid max-w-3xl gap-3 text-left sm:grid-cols-2">
            {ONLINE_POINTS.map((p) => (
              <div key={p} className="flex items-start gap-3 rounded-2xl bg-white/5 px-5 py-4 text-sm">
                <Check className="mt-0.5 size-4 shrink-0 text-emerald-400" /> {p}
              </div>
            ))}
          </div>
          <div className="mt-10 flex flex-wrap items-center justify-center gap-3">
            <Button asChild size="lg" className="h-11 rounded-full bg-white px-7 text-sm text-black hover:bg-zinc-200">
              <Link to="/student?signup=1">
                <Sparkles className="size-4" /> Create free account
              </Link>
            </Button>
            <Button asChild size="lg" variant="ghost" className="h-11 rounded-full px-5 text-sm text-white hover:bg-white/10">
              <Link to="/student">Student login</Link>
            </Button>
          </div>
        </div>
      </section>

      {/* ===== Courses ===== */}
      <section id="courses" className="bg-[#f5f5f7] py-20 dark:bg-zinc-900">
        <div className="mx-auto max-w-6xl px-4">
          <div className="text-center">
            <h2 className="text-3xl font-semibold tracking-tight sm:text-4xl">Curriculum</h2>
            <p className="mx-auto mt-2 max-w-md text-[15px] text-zinc-500">Online ရော ကျောင်းမှာရော သင်ယူနိုင်တဲ့ သင်ရိုး ၂ မျိုး</p>
          </div>
          <div className="mt-12 grid gap-5 md:grid-cols-2">
            {COURSES.map((c) => (
              <div key={c.title} className="rounded-3xl bg-white p-7 shadow-sm ring-1 ring-zinc-200/60 transition-all hover:-translate-y-1 hover:shadow-lg dark:bg-zinc-950 dark:ring-zinc-800">
                <div className="flex items-center gap-3">
                  <span className="flex size-10 items-center justify-center rounded-full bg-zinc-100 text-black dark:bg-zinc-800 dark:text-white">{c.icon}</span>
                  <div>
                    <h3 className="text-lg font-semibold tracking-tight">{c.title}</h3>
                    <p className="text-xs text-zinc-500">{c.lessons} lessons</p>
                  </div>
                </div>
                <p className="mt-3 text-sm text-zinc-500">{c.desc}</p>
                <ul className="mt-4 space-y-2">
                  {c.topics.map((t) => (
                    <li key={t} className="flex items-center gap-2 text-sm">
                      <Check className="size-4 shrink-0 text-emerald-500" /> {t}
                    </li>
                  ))}
                </ul>
                <Button asChild variant="outline" className="mt-6 w-full rounded-full">
                  <Link to="/student?signup=1">Start on Online Learning →</Link>
                </Button>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ===== Features ===== */}
      <section className="py-20">
        <div className="mx-auto max-w-6xl px-4">
          <div className="text-center">
            <h2 className="text-3xl font-semibold tracking-tight sm:text-4xl">Everything included</h2>
          </div>
          <div className="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            {FEATURES.map((f) => (
              <div key={f.title} className="rounded-3xl bg-[#f5f5f7] p-6 dark:bg-zinc-900">
                <span className="flex size-10 items-center justify-center rounded-full bg-white text-black shadow-sm ring-1 ring-zinc-200/60 dark:bg-zinc-800 dark:text-white dark:ring-zinc-700">
                  {f.icon}
                </span>
                <h3 className="mt-4 text-[15px] font-semibold tracking-tight">{f.title}</h3>
                <p className="mt-1 text-sm text-zinc-500">{f.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ===== Pricing ===== */}
      <section id="pricing" className="bg-[#f5f5f7] py-20 dark:bg-zinc-900">
        <div className="mx-auto max-w-6xl px-4">
          <div className="text-center">
            <h2 className="text-3xl font-semibold tracking-tight sm:text-4xl">Subscription</h2>
            <p className="mx-auto mt-2 max-w-md text-[15px] text-zinc-500">Plan တွေ — စျေးနှုန်းအတိအကျ ဆက်သွယ်မေးမြန်းပါ</p>
          </div>
          <div className="mt-12 grid gap-5 md:grid-cols-3">
            {PLANS.map((p) => (
              <div key={p.name} className={`rounded-3xl p-7 ${p.highlight ? "bg-black text-white shadow-lg dark:bg-white dark:text-black" : "bg-white ring-1 ring-zinc-200/60 dark:bg-zinc-950 dark:ring-zinc-800"}`}>
                <h3 className="text-lg font-semibold tracking-tight">{p.name}</h3>
                <p className={`mt-1 text-xs ${p.highlight ? "text-zinc-400 dark:text-zinc-500" : "text-zinc-500"}`}>
                  {p.highlight ? "POPULAR" : "Online Learning"}
                </p>
                <div className="mt-5 text-2xl font-semibold tracking-tight">Contact us</div>
                <ul className="mt-5 space-y-2.5">
                  {p.features.map((f) => (
                    <li key={f} className={`flex items-center gap-2 text-sm ${p.highlight ? "text-zinc-300 dark:text-zinc-600" : "text-zinc-500"}`}>
                      <Check className={`size-4 shrink-0 ${p.highlight ? "text-emerald-400 dark:text-emerald-500" : "text-emerald-500"}`} /> {f}
                    </li>
                  ))}
                </ul>
                <Button asChild className={`mt-7 w-full rounded-full ${p.highlight ? "bg-white text-black hover:bg-zinc-200 dark:bg-black dark:text-white dark:hover:bg-zinc-800" : ""}`} variant={p.highlight ? "default" : "outline"}>
                  <a href="#contact">Contact</a>
                </Button>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ===== FAQ ===== */}
      <section id="faq" className="py-20">
        <div className="mx-auto max-w-3xl px-4">
          <div className="text-center">
            <h2 className="text-3xl font-semibold tracking-tight sm:text-4xl">FAQ</h2>
          </div>
          <div className="mt-10 space-y-3">
            {FAQS.map((f) => (
              <details key={f.q} className="group rounded-2xl bg-[#f5f5f7] p-5 dark:bg-zinc-900">
                <summary className="flex cursor-pointer list-none items-center justify-between gap-3 text-[15px] font-medium">
                  {f.q}
                  <ChevronRight className="size-4 shrink-0 text-zinc-400 transition-transform group-open:rotate-90" />
                </summary>
                <p className="mt-3 text-sm leading-relaxed text-zinc-500">{f.a}</p>
              </details>
            ))}
          </div>
        </div>
      </section>

      {/* ===== Contact CTA ===== */}
      <section id="contact" className="bg-black py-20 text-white dark:bg-zinc-950">
        <div className="mx-auto max-w-4xl px-4 text-center">
          <h2 className="text-3xl font-semibold tracking-tight sm:text-4xl">Start learning today.</h2>
          <p className="mx-auto mt-3 max-w-md text-[15px] text-zinc-400">
            Account ဖွင့်ရတာ အခမဲ့ — Subscription နဲ့ ပတ်သက်လို့ ဆက်သွယ်နိုင်ပါတယ်။
          </p>
          <div className="mt-7 flex flex-col items-center justify-center gap-3 text-sm text-zinc-300 sm:flex-row sm:gap-8">
            <span className="flex items-center gap-2"><Phone className="size-4" /> 09 996 219 8380</span>
            <span className="flex items-center gap-2"><Mail className="size-4" /> i35kohein@gmail.com</span>
            <span className="flex items-center gap-2"><MapPin className="size-4" /> No 1031, North Dagon, Yangon</span>
          </div>
          <Button asChild size="lg" className="mt-8 h-11 rounded-full bg-white px-7 text-sm text-black hover:bg-zinc-200">
            <Link to="/student?signup=1">Create free account</Link>
          </Button>
        </div>
      </section>

      {/* ===== Footer ===== */}
      <footer className="bg-white py-12 text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400">
        <div className="mx-auto max-w-6xl px-4">
          <div className="flex flex-col items-center gap-6 sm:flex-row sm:items-start sm:justify-between">
            <div className="flex items-center gap-2.5">
              <AppleLogo className="h-8 w-8" />
              <div className="leading-tight">
                <div className="text-sm font-semibold text-black dark:text-white">Apple Art</div>
                <div className="text-[11px]">i Device Repair Training Academy</div>
              </div>
            </div>
            <nav className="flex flex-wrap items-center justify-center gap-5">
              {NAV.map(([href, label]) => (
                <a key={href} href={href} className="text-xs transition-colors hover:text-black dark:hover:text-white">
                  {label}
                </a>
              ))}
            </nav>
            <div className="text-center text-[11px] leading-relaxed sm:text-right">
              <div className="flex items-center gap-1.5 justify-center sm:justify-end"><Clock className="size-3" /> Mon ပိတ် · Tue–Sat 09:00–18:00</div>
              <div className="mt-1">© 2026 Apple Art · i35 Apple Service</div>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}
