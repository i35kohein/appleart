const API_BASE = "/api"

export class ApiError extends Error {
  status: number
  constructor(status: number, message: string) {
    super(message)
    this.status = status
  }
}

/**
 * Single fetch wrapper for the PHP backend.
 * - Always sends cookies (PHP session auth)
 * - Parses JSON, normalizes { status: 'error', message } responses
 * - 401 → throws ApiError(401) (router guard redirects to /login)
 */
export async function apiFetch<T>(
  path: string,
  init: RequestInit = {},
): Promise<T> {
  const res = await fetch(`${API_BASE}${path}`, {
    credentials: "include",
    headers: { "Content-Type": "application/json", ...(init.headers ?? {}) },
    ...init,
  })

  if (res.status === 401) {
    throw new ApiError(401, "Not authenticated")
  }

  const text = await res.text()
  let data: unknown = null
  try {
    data = text ? JSON.parse(text) : null
  } catch {
    throw new ApiError(res.status, `Invalid JSON from ${path}`)
  }

  if (!res.ok) {
    const msg =
      data && typeof data === "object" && "message" in data
        ? String((data as { message: unknown }).message)
        : `Request failed (${res.status})`
    throw new ApiError(res.status, msg)
  }

  // PHP endpoints return { status: 'success'|'error', ... }
  if (data && typeof data === "object" && "status" in data) {
    const d = data as { status: string; message?: string }
    if (d.status === "error") {
      throw new ApiError(res.status, d.message ?? "Request failed")
    }
    if (d.status === "success") {
      return data as T
    }
  }
  return data as T
}

/** POST helper for PHP endpoints that read $_POST (form-encoded). */
export async function apiPostForm<T>(path: string, fields: Record<string, string | number | Blob | null | undefined>): Promise<T> {
  const fd = new FormData()
  for (const [k, v] of Object.entries(fields)) {
    if (v == null) continue
    fd.append(k, v instanceof Blob ? v : String(v))
  }
  const res = await fetch(`${API_BASE}${path}`, {
    method: "POST",
    credentials: "include",
    body: fd,
  })
  const text = await res.text()
  let data: unknown = null
  try {
    data = text ? JSON.parse(text) : null
  } catch {
    throw new ApiError(res.status, `Invalid JSON from ${path}`)
  }
  if (!res.ok) throw new ApiError(res.status, `Request failed (${res.status})`)
  if (data && typeof data === "object" && "status" in data) {
    const d = data as { status: string; message?: string }
    if (d.status === "error") throw new ApiError(res.status, d.message ?? "Request failed")
  }
  return data as T
}
