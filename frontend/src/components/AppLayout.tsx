import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'

export default function AppLayout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/login', { replace: true })
  }

  return (
    <div className="app-shell">
      {user?.is_demo && (
        <div className="demo-banner" role="status">
          Ambiente demonstrativo com dados fictícios — alterações estão bloqueadas.
        </div>
      )}
      <header className="topbar">
        <NavLink to="/dashboard" className="brand" aria-label="GreenMeter Lite">
          <span className="brand-mark" aria-hidden="true">G</span>
          <span>GreenMeter <strong>Lite</strong></span>
        </NavLink>
        <nav className="nav-links" aria-label="Navegação principal">
          <NavLink to="/dashboard">Dashboard</NavLink>
          <NavLink to="/upload">Importar CSV</NavLink>
        </nav>
        <div className="user-menu">
          {user?.is_demo && <span className="demo-badge">DEMO</span>}
          <span className="user-name">{user?.name}</span>
          <button className="button button-ghost" onClick={handleLogout}>Sair</button>
        </div>
      </header>
      <main className="page-container"><Outlet /></main>
    </div>
  )
}
