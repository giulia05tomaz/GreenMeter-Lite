import { useRef, useState, type FormEvent } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useAuth } from '../auth/AuthContext'
import { api, apiErrorMessage } from '../lib/api'

type ImportRecord = {
  id: number
  filename: string
  line_count: number
  date_range: string
  status: string
  message: string | null
  created_at: string
}

export default function UploadPage() {
  const { user } = useAuth()
  const isDemo = Boolean(user?.is_demo)
  const [file, setFile] = useState<File | null>(null)
  const [error, setError] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [success, setSuccess] = useState('')
  const inputRef = useRef<HTMLInputElement>(null)
  const history = useQuery({
    queryKey: ['imports'],
    queryFn: async () => (await api.get<ImportRecord[]>('/imports')).data,
  })

  const selectFile = (selected: File | null) => {
    setError('')
    setSuccess('')
    if (!selected) return setFile(null)
    if (!selected.name.toLowerCase().endsWith('.csv')) {
      setFile(null)
      return setError('Selecione um arquivo com extensão .csv.')
    }
    if (selected.size > 2 * 1024 * 1024) {
      setFile(null)
      return setError('O arquivo excede o limite de 2 MB.')
    }
    setFile(selected)
  }

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault()
    if (!file || isDemo) return
    setSubmitting(true)
    setError('')

    try {
      const formData = new FormData()
      formData.append('file', file)
      const response = await api.post<{ inserted: number; date_range: string }>('/readings/upload', formData)
      setSuccess(`${response.data.inserted} leituras importadas (${response.data.date_range}).`)
      setFile(null)
      await history.refetch()
    } catch (requestError) {
      setError(apiErrorMessage(requestError, 'Não foi possível importar o arquivo.'))
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <section>
      <div className="page-heading page-heading-row">
        <div>
          <p className="eyebrow">Nova leitura</p>
          <h1>Importar dados de energia</h1>
          <p>Envie um CSV de até 2 MB e 10.000 linhas. A importação é validada antes de salvar.</p>
        </div>
        <a className="button button-ghost" href={`${api.defaults.baseURL}/samples/energy_readings.csv`} download>
          Baixar CSV de exemplo
        </a>
      </div>

      {isDemo && <div className="alert alert-error" role="alert">A conta de demonstração é somente leitura. Crie uma conta para importar arquivos.</div>}
      {success && <div className="alert alert-success" role="status">{success}</div>}

      <div className="content-grid">
        <form className="card upload-card" onSubmit={handleSubmit}>
          {error && <div className="alert alert-error" role="alert">{error}</div>}
          <button
            type="button"
            className={`dropzone ${isDemo ? 'dropzone-disabled' : ''}`}
            onClick={() => !isDemo && inputRef.current?.click()}
            disabled={isDemo}
          >
            <span className="upload-icon" aria-hidden="true">↑</span>
            <strong>{file ? file.name : 'Selecione um arquivo CSV'}</strong>
            <span>{file ? `${(file.size / 1024).toFixed(1)} KB` : 'Clique para procurar no computador'}</span>
          </button>
          <input
            ref={inputRef}
            className="visually-hidden"
            type="file"
            accept=".csv,text/csv"
            disabled={isDemo}
            onChange={(event) => selectFile(event.target.files?.[0] ?? null)}
          />
          <button className="button button-primary" disabled={!file || submitting || isDemo}>
            {submitting ? 'Validando e importando…' : 'Importar leituras'}
          </button>
        </form>

        <aside className="card format-card">
          <p className="eyebrow">Formato esperado</p>
          <h2>Quatro colunas obrigatórias</h2>
          <pre><code>timestamp,metric,value,unit{`\n`}2026-01-01T00:00:00Z,energy,12.5,kWh</code></pre>
          <ul className="check-list">
            <li>Timestamp em formato de data e hora válido</li>
            <li><code>metric</code> igual a <code>energy</code></li>
            <li>Valor numérico não negativo</li>
            <li><code>unit</code> igual a <code>kWh</code></li>
          </ul>
        </aside>
      </div>

      <section className="card history-card" aria-labelledby="history-title">
        <div className="card-heading"><div><p className="eyebrow">Auditoria</p><h2 id="history-title">Histórico de importações</h2></div></div>
        {history.isLoading ? <p className="muted">Carregando histórico…</p> : history.isError ? (
          <div className="alert alert-error">Não foi possível carregar o histórico. <button className="text-button" onClick={() => history.refetch()}>Tentar novamente</button></div>
        ) : history.data?.length ? (
          <div className="table-wrap"><table className="data-table"><thead><tr><th>Data</th><th>Arquivo</th><th>Linhas</th><th>Período</th><th>Status</th></tr></thead><tbody>
            {history.data.map((item) => <tr key={item.id}><td>{new Date(item.created_at).toLocaleString('pt-BR')}</td><td>{item.filename}</td><td>{item.line_count}</td><td>{item.date_range}</td><td>{item.status === 'success' ? 'Sucesso' : item.status}</td></tr>)}
          </tbody></table></div>
        ) : <p className="muted">Nenhuma importação registrada.</p>}
      </section>
    </section>
  )
}
