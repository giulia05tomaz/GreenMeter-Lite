import { createContext, useContext, useMemo, useState, type ReactNode } from 'react'
import { api, setApiToken } from '../lib/api'

export type User = { name: string; email: string; is_demo?: boolean }

type AuthContextValue = {
  user: User | null
  authenticated: boolean
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string, passwordConfirmation: string) => Promise<void>
  loginDemo: () => Promise<void>
  logout: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | null>(null)

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null)

  const value = useMemo<AuthContextValue>(() => ({
    user,
    authenticated: user !== null,
    login: async (email, password) => {
      const response = await api.post<{ token: string; user: User }>('/auth/login', { email, password })
      setApiToken(response.data.token)
      setUser(response.data.user)
    },
    register: async (name, email, password, passwordConfirmation) => {
      const response = await api.post<{ token: string; user: User }>('/auth/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      })
      setApiToken(response.data.token)
      setUser(response.data.user)
    },
    loginDemo: async () => {
      const response = await api.post<{ token: string; user: User }>('/auth/demo')
      setApiToken(response.data.token)
      setUser(response.data.user)
    },
    logout: async () => {
      try {
        await api.post('/auth/logout')
      } finally {
        setApiToken(null)
        setUser(null)
      }
    },
  }), [user])

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (!context) throw new Error('useAuth must be used inside AuthProvider')

  return context
}
