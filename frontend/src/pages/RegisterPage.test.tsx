import { cleanup, fireEvent, render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { AuthProvider } from '../auth/AuthContext'
import { api } from '../lib/api'
import RegisterPage from './RegisterPage'

vi.mock('../lib/api', async () => {
  const actual = await vi.importActual<typeof import('../lib/api')>('../lib/api')
  return { ...actual, api: { ...actual.api, post: vi.fn() } }
})

function renderPage() {
  return render(
    <MemoryRouter initialEntries={['/cadastro']}>
      <AuthProvider>
        <Routes>
          <Route path="/cadastro" element={<RegisterPage />} />
          <Route path="/dashboard" element={<div>Dashboard</div>} />
        </Routes>
      </AuthProvider>
    </MemoryRouter>,
  )
}

describe('RegisterPage', () => {
  beforeEach(() => vi.clearAllMocks())
  afterEach(() => cleanup())

  it('renders accessible registration fields and a login link', () => {
    renderPage()
    expect(screen.getByRole('heading', { name: 'Criar minha conta' })).toBeInTheDocument()
    expect(screen.getByLabelText('Nome completo')).toHaveAttribute('autocomplete', 'name')
    expect(screen.getByLabelText('E-mail')).toHaveAttribute('autocomplete', 'email')
    expect(screen.getByLabelText('Senha')).toHaveAttribute('autocomplete', 'new-password')
    expect(screen.getByLabelText('Confirmar senha')).toHaveAttribute('autocomplete', 'new-password')
    expect(screen.getByRole('link', { name: 'Entrar' })).toHaveAttribute('href', '/login')
  })

  it('rejects mismatched passwords before sending the request', async () => {
    renderPage()
    fireEvent.change(screen.getByLabelText('Nome completo'), { target: { value: 'Maria Silva' } })
    fireEvent.change(screen.getByLabelText('E-mail'), { target: { value: 'maria@example.com' } })
    fireEvent.change(screen.getByLabelText('Senha'), { target: { value: 'Senha123' } })
    fireEvent.change(screen.getByLabelText('Confirmar senha'), { target: { value: 'Outra123' } })
    fireEvent.click(screen.getByRole('button', { name: 'Criar minha conta' }))
    expect(await screen.findByText('As senhas não coincidem.')).toBeInTheDocument()
    expect(api.post).not.toHaveBeenCalled()
  })

  it('registers, keeps the token in memory and redirects', async () => {
    vi.mocked(api.post).mockResolvedValueOnce({
      data: { token: 'sanctum-token', user: { name: 'Maria Silva', email: 'maria@example.com', is_demo: false } },
    })
    renderPage()
    fireEvent.change(screen.getByLabelText('Nome completo'), { target: { value: 'Maria Silva' } })
    fireEvent.change(screen.getByLabelText('E-mail'), { target: { value: 'MARIA@example.com' } })
    fireEvent.change(screen.getByLabelText('Senha'), { target: { value: 'Senha123' } })
    fireEvent.change(screen.getByLabelText('Confirmar senha'), { target: { value: 'Senha123' } })
    fireEvent.click(screen.getByRole('button', { name: 'Criar minha conta' }))

    await waitFor(() => expect(api.post).toHaveBeenCalledWith('/auth/register', {
      name: 'Maria Silva',
      email: 'maria@example.com',
      password: 'Senha123',
      password_confirmation: 'Senha123',
    }))
    expect(await screen.findByText('Dashboard')).toBeInTheDocument()
    expect(localStorage.getItem('token')).toBeNull()
  })
})
