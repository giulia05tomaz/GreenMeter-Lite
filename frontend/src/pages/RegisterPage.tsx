import { useRef, useState, type FormEvent } from 'react'
import { Link, Navigate, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'
import { apiErrorMessage } from '../lib/api'

type FieldErrors = {
  name?: string
  email?: string
  password?: string
  passwordConfirmation?: string
}

export default function RegisterPage() {
  const { authenticated, register } = useAuth()
  const navigate = useNavigate()
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [submitting, setSubmitting] = useState(false)
  const [generalError, setGeneralError] = useState('')
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({})
  const nameRef = useRef<HTMLInputElement>(null)
  const emailRef = useRef<HTMLInputElement>(null)
  const passwordRef = useRef<HTMLInputElement>(null)
  const confirmationRef = useRef<HTMLInputElement>(null)

  if (authenticated) return <Navigate to="/dashboard" replace />

  const validate = () => {
    const errors: FieldErrors = {}
    const normalizedName = name.trim()
    const normalizedEmail = email.trim()

    if (normalizedName.length < 2) errors.name = 'Informe um nome com pelo menos 2 caracteres.'
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalizedEmail)) errors.email = 'Informe um e-mail válido.'
    if (password.length < 8 || !/[A-Za-z]/.test(password) || !/\d/.test(password)) {
      errors.password = 'Use no mínimo 8 caracteres, com pelo menos uma letra e um número.'
    }
    if (password !== passwordConfirmation) errors.passwordConfirmation = 'As senhas não coincidem.'

    setFieldErrors(errors)
    if (errors.name) nameRef.current?.focus()
    else if (errors.email) emailRef.current?.focus()
    else if (errors.password) passwordRef.current?.focus()
    else if (errors.passwordConfirmation) confirmationRef.current?.focus()

    return Object.keys(errors).length === 0
  }

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault()
    setGeneralError('')
    if (!validate()) return
    setSubmitting(true)

    try {
      await register(name.trim(), email.trim().toLowerCase(), password, passwordConfirmation)
      navigate('/dashboard', { replace: true })
    } catch (requestError: unknown) {
      const response = (requestError as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } }).response
      if (response?.status === 422 && response.data?.errors) {
        const errors = response.data.errors
        setFieldErrors({
          name: errors.name?.[0],
          email: errors.email?.[0],
          password: errors.password?.[0],
          passwordConfirmation: errors.password_confirmation?.[0],
        })
      } else {
        setGeneralError(apiErrorMessage(requestError, 'Não foi possível criar a conta. Tente novamente.'))
      }
      setPassword('')
      setPasswordConfirmation('')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <main className="login-page">
      <section className="login-story">
        <Link to="/login" className="brand brand-light" aria-label="GreenMeter Lite">
          <span className="brand-mark" aria-hidden="true">G</span>
          <span>GreenMeter <strong>Lite</strong></span>
        </Link>
        <div>
          <p className="eyebrow">Crie sua conta</p>
          <h1>Comece a acompanhar seu consumo de energia.</h1>
          <p>Cadastre-se para importar leituras, acompanhar indicadores e manter seu histórico isolado.</p>
        </div>
        <p className="story-note">Projeto de portfólio com estimativas demonstrativas de emissões.</p>
      </section>

      <section className="login-panel" aria-labelledby="register-heading">
        <div className="auth-card">
          <p className="eyebrow">Novo acesso</p>
          <h2 id="register-heading">Criar minha conta</h2>
          <p className="muted">Seus dados ficam separados das demais contas.</p>
          {generalError && <div className="alert alert-error" role="alert">{generalError}</div>}

          <form onSubmit={handleSubmit} noValidate>
            <div className="field">
              <label htmlFor="register-name">Nome completo</label>
              <input id="register-name" ref={nameRef} autoComplete="name" value={name} onChange={(e) => setName(e.target.value)} disabled={submitting} aria-invalid={Boolean(fieldErrors.name)} aria-describedby={fieldErrors.name ? 'register-name-error' : undefined} />
              {fieldErrors.name && <small id="register-name-error" className="field-error-text">{fieldErrors.name}</small>}
            </div>

            <div className="field">
              <label htmlFor="register-email">E-mail</label>
              <input id="register-email" ref={emailRef} type="email" autoComplete="email" value={email} onChange={(e) => setEmail(e.target.value)} disabled={submitting} aria-invalid={Boolean(fieldErrors.email)} aria-describedby={fieldErrors.email ? 'register-email-error' : undefined} />
              {fieldErrors.email && <small id="register-email-error" className="field-error-text">{fieldErrors.email}</small>}
            </div>

            <div className="field">
              <label htmlFor="register-password">Senha</label>
              <input id="register-password" ref={passwordRef} type={showPassword ? 'text' : 'password'} autoComplete="new-password" value={password} onChange={(e) => setPassword(e.target.value)} disabled={submitting} aria-invalid={Boolean(fieldErrors.password)} aria-describedby="password-requirements" />
              <small id="password-requirements" className={fieldErrors.password ? 'field-error-text' : 'field-hint'}>{fieldErrors.password || 'Mínimo de 8 caracteres, com uma letra e um número.'}</small>
            </div>

            <div className="field">
              <label htmlFor="register-password-confirmation">Confirmar senha</label>
              <input id="register-password-confirmation" ref={confirmationRef} type={showPassword ? 'text' : 'password'} autoComplete="new-password" value={passwordConfirmation} onChange={(e) => setPasswordConfirmation(e.target.value)} disabled={submitting} aria-invalid={Boolean(fieldErrors.passwordConfirmation)} aria-describedby={fieldErrors.passwordConfirmation ? 'register-confirmation-error' : undefined} />
              {fieldErrors.passwordConfirmation && <small id="register-confirmation-error" className="field-error-text">{fieldErrors.passwordConfirmation}</small>}
            </div>

            <button type="button" className="password-toggle" onClick={() => setShowPassword((value) => !value)}>
              {showPassword ? 'Ocultar senhas' : 'Mostrar senhas'}
            </button>
            <button className="button button-primary button-block" disabled={submitting}>
              {submitting ? 'Criando conta…' : 'Criar minha conta'}
            </button>
          </form>

          <p className="auth-footer-link">Já possui uma conta? <Link to="/login">Entrar</Link></p>
        </div>
      </section>
    </main>
  )
}
