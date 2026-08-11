import { useState, type FormEvent } from 'react'
import { Navigate, useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'
import { apiErrorMessage } from '../lib/api'

export default function LoginPage() {
  const { authenticated, login } = useAuth()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const navigate = useNavigate()
  const location = useLocation()

  if (authenticated) return <Navigate to="/dashboard" replace />

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault()
    setSubmitting(true)
    setError('')

    try {
      await login(email, password)
      const destination = (location.state as { from?: string } | null)?.from || '/dashboard'
      navigate(destination, { replace: true })
    } catch (requestError) {
      setError(apiErrorMessage(requestError, 'Não foi possível entrar. Tente novamente.'))
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <main className="login-page">
      <section className="login-story">
        <a href="/" className="brand brand-light" aria-label="GreenMeter Lite">
          <span className="brand-mark" aria-hidden="true">G</span>
          <span>GreenMeter <strong>Lite</strong></span>
        </a>
        <div>
          <p className="eyebrow">Energia sob controle</p>
          <h1>Transforme leituras de consumo em decisões mais conscientes.</h1>
          <p>Importe dados, acompanhe emissões estimadas e identifique picos em um painel simples.</p>
        </div>
        <p className="story-note">MVP de portfólio com dados demonstrativos — não substitui inventário ambiental certificado.</p>
      </section>

      <section className="login-panel" aria-labelledby="login-heading">
        <form className="auth-card" onSubmit={handleSubmit}>
          <p className="eyebrow">Acesso ao painel</p>
          <h2 id="login-heading">Bem-vinda de volta</h2>
          <p className="muted">Use a conta configurada no seu ambiente local.</p>

          {error && <div className="alert alert-error" role="alert">{error}</div>}

          <label className="field">
            <span>E-mail</span>
            <input
              type="email"
              autoComplete="email"
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              placeholder="voce@exemplo.com"
              required
            />
          </label>
          <label className="field">
            <span>Senha</span>
            <input
              type="password"
              autoComplete="current-password"
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              required
            />
          </label>
          <button className="button button-primary button-block" disabled={submitting}>
            {submitting ? 'Entrando…' : 'Entrar'}
          </button>
        </form>
      </section>
    </main>
  )
}
