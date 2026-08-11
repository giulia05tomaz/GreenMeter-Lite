import { useRef, useState, type FormEvent } from 'react'
import { useNavigate } from 'react-router-dom'
import { api, apiErrorMessage } from '../lib/api'

export default function UploadPage() {
  const [file, setFile] = useState<File | null>(null)
  const [error, setError] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const inputRef = useRef<HTMLInputElement>(null)
  const navigate = useNavigate()

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault()
    if (!file) return
    setSubmitting(true)
    setError('')

    try {
      const formData = new FormData()
      formData.append('file', file)
      await api.post('/readings/upload', formData)
      navigate('/dashboard', { replace: true, state: { imported: true } })
    } catch (requestError) {
      setError(apiErrorMessage(requestError, 'Não foi possível importar o arquivo.'))
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <section>
      <div className="page-heading">
        <div>
          <p className="eyebrow">Nova leitura</p>
          <h1>Importar dados de energia</h1>
          <p>Envie um CSV de até 2 MB e 10.000 linhas. A importação é validada antes de salvar.</p>
        </div>
      </div>

      <div className="content-grid">
        <form className="card upload-card" onSubmit={handleSubmit}>
          {error && <div className="alert alert-error" role="alert">{error}</div>}
          <button
            type="button"
            className="dropzone"
            onClick={() => inputRef.current?.click()}
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
            onChange={(event) => setFile(event.target.files?.[0] ?? null)}
          />
          <button className="button button-primary" disabled={!file || submitting}>
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
    </section>
  )
}
