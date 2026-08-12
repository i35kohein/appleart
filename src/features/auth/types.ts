export type UserRole = "user" | "admin" | "master_admin"

export interface Me {
  id: number
  name: string
  email: string
  role: UserRole
}

export interface LoginResponse {
  status: "success"
  message: string
}
