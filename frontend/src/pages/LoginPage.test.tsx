import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { expect, it } from 'vitest'
import { AuthProvider } from '../auth/AuthContext'
import LoginPage from './LoginPage'

it('renders an accessible login form', () => {
  render(
    <MemoryRouter>
      <AuthProvider><LoginPage /></AuthProvider>
    </MemoryRouter>,
  )

  expect(screen.getByRole('heading', { name: 'Bem-vinda de volta' })).toBeInTheDocument()
  expect(screen.getByLabelText('E-mail')).toBeRequired()
  expect(screen.getByLabelText('Senha')).toHaveAttribute('type', 'password')
  expect(screen.getByRole('button', { name: 'Entrar' })).toBeEnabled()
})
