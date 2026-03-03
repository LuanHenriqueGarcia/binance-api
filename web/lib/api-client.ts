import { useSettingsStore } from './store'

const BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8080/api'

export interface ApiResponse<T = unknown> {
  success: boolean
  data?: T
  error?: string
  code?: number
}

export interface ApiMeta {
  status: number
  latency: number
  requestId: string | null
  retryAfter: number | null
}

export interface ApiResult<T = unknown> {
  response: ApiResponse<T>
  meta: ApiMeta
}

type HttpMethod = 'GET' | 'POST' | 'DELETE' | 'PUT' | 'PATCH'

interface RequestOptions {
  method?: HttpMethod
  params?: Record<string, string | number | boolean | undefined>
  body?: Record<string, unknown>
  credentialFields?: Record<string, string>
}

function buildQueryString(params: Record<string, string | number | boolean | undefined>): string {
  const entries = Object.entries(params).filter(([, v]) => v !== undefined && v !== '')
  if (entries.length === 0) return ''
  const searchParams = new URLSearchParams()
  entries.forEach(([k, v]) => searchParams.append(k, String(v)))
  return '?' + searchParams.toString()
}

export async function apiClient<T = unknown>(
  endpoint: string,
  options: RequestOptions = {}
): Promise<ApiResult<T>> {
  const { method = 'GET', params, body, credentialFields } = options
  const store = useSettingsStore.getState()

  const headers: Record<string, string> = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  }

  // Basic Auth
  const { username, password } = store.credentials.basicAuth
  if (username && password) {
    headers['Authorization'] = 'Basic ' + btoa(`${username}:${password}`)
  }

  // Correlation ID
  if (store.correlationEnabled && store.correlationId) {
    headers['X-Correlation-Id'] = store.correlationId
  }

  // Merge credential fields into body or params
  let finalBody = body ? { ...body } : undefined
  let finalParams = params ? { ...params } : undefined

  if (credentialFields) {
    const filteredCredentialFields = Object.fromEntries(
      Object.entries(credentialFields).filter(([, value]) => value !== '')
    )

    if (method === 'GET') {
      finalParams = { ...finalParams, ...filteredCredentialFields }
    } else {
      finalBody = { ...finalBody, ...filteredCredentialFields }
    }
  }

  // The current backend parses DELETE payload from JSON body, not query string.
  if (method === 'DELETE' && !finalBody && finalParams) {
    finalBody = { ...finalParams }
    finalParams = undefined
  }

  const finalUrl = `${BASE_URL}${endpoint}${finalParams ? buildQueryString(finalParams) : ''}`

  const start = performance.now()

  try {
    const res = await fetch(finalUrl, {
      method,
      headers,
      body: finalBody && method !== 'GET' ? JSON.stringify(finalBody) : undefined,
    })

    const latency = Math.round(performance.now() - start)
    const requestId = res.headers.get('X-Request-Id')
    const retryAfter = res.headers.get('Retry-After')
      ? parseInt(res.headers.get('Retry-After')!, 10)
      : null

    let responseData: ApiResponse<T>

    try {
      responseData = await res.json()
    } catch {
      responseData = {
        success: false,
        error: `HTTP ${res.status}: ${res.statusText}`,
        code: res.status,
      }
    }

    return {
      response: responseData,
      meta: {
        status: res.status,
        latency,
        requestId,
        retryAfter,
      },
    }
  } catch (err) {
    const latency = Math.round(performance.now() - start)
    return {
      response: {
        success: false,
        error: err instanceof Error ? err.message : 'Erro de rede desconhecido',
        code: 0,
      },
      meta: {
        status: 0,
        latency,
        requestId: null,
        retryAfter: null,
      },
    }
  }
}

export function getBaseUrl(): string {
  return BASE_URL
}
