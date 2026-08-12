export interface Student {
  id: number
  name: string
  phone: string | null
  email: string | null
  address: string | null
  photo_path: string | null
  is_active: 0 | 1
  role: "student" | "online"
  rollcall_group: "Weekday" | "Weekend"
  shop_name: string | null
  created_at: string | null
  enrollment_date?: string | null
  course_completed: number
  total_course: number
  practical_completed: number
  total_practical: number
}

export interface StudentInput {
  name: string
  phone: string
  email: string
  address: string
  shop_name: string
  is_active: 0 | 1
  role: "student" | "online"
  rollcall_group: "Weekday" | "Weekend"
  enrollment_date?: string
}

export interface CurriculumMaterial {
  id: number
  item_id: number
  file_name: string
  file_path: string
  file_type: string
  file_size: number
  uploaded_at: string
}

export interface CurriculumItem {
  id: number
  type: "Course" | "Practical"
  category: string
  title: string
  tags: string
  practice: string | null
  details: string | null
  sort_order: number
  materials?: CurriculumMaterial[]
}

export interface TodayLesson {
  student_id: number
  student_name: string
  phone: string | null
  group: "Weekday" | "Weekend"
  is_training: boolean
  course: { id: number; title: string; category: string } | null
  practical: { id: number; title: string; category: string } | null
  course_done: number
  course_total: number
  practical_done: number
  practical_total: number
  scheduled: Array<{ start_time: string | null; lesson_type: string; topic: string }>
}

export interface TodayResponse {
  status: "success"
  date: string
  weekday: string
  schedules: Record<string, { days: number[]; start_time: string; end_time: string }>
  students: TodayLesson[]
}
