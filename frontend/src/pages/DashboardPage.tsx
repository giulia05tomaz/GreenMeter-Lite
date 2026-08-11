import { useQuery } from '@tanstack/react-query'
import { useState, type FormEvent } from 'react'
import {
  CategoryScale,
  Chart as ChartJS,
  Filler,
  Legend,
  LineElement,
  LinearScale,
  PointElement,
  Tooltip,
} from 'chart.js'
import { Line } from 'react-chartjs-2'
import { Link, useLocation } from 'react-router-dom'
import { api } from '../lib/api'

ChartJS.register(CategoryScale, Filler, Legend, LineElement, LinearScale, PointElement, Tooltip)

type Kpis = {
  total_energy_kwh: number
  total_co2e_t: number
  daily_avg_kwh: number
}
type SeriesPoint = { date: string; kwh: number; co2e: number }
type PeakAlert = { date: string; total_kwh: number; threshold_kwh: number; week_avg_kwh: number; percent_over: number }

const number = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 3 })
const co2 = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 4, maximumFractionDigits: 6 })

export default function DashboardPage() {
  const location = useLocation()
  const [from, setFrom] = useState('')
  const [to, setTo] = useState('')
  const [filters, setFilters] = useState<{ from?: string; to?: string }>({})
  const [filterError, setFilterError] = useState('')
  const [showTable, setShowTable] = useState(false)
  const imported = Boolean((location.state as { imported?: boolean } | null)?.imported)
  const kpis = useQuery({
    queryKey: ['kpis', filters],
    queryFn: async () => (await api.get<Kpis>('/dashboard/kpis', { params: filters })).data,
  })
  const series = useQuery({
    queryKey: ['series', filters],
    queryFn: async () => (await api.get<SeriesPoint[]>('/dashboard/series', { params: filters })).data,
  })
  const alerts = useQuery({
    queryKey: ['alerts', filters],
    queryFn: async () => (await api.get<PeakAlert[]>('/alerts', { params: filters })).data,
  })

  const applyFilters = (event: FormEvent) => {
    event.preventDefault()
    if (from && to && from > to) {
      setFilterError('A data inicial não pode ser posterior à data final.')
      return
    }
    setFilterError('')
    setFilters({ from: from || undefined, to: to || undefined })
  }

  const clearFilters = () => {
    setFrom('')
    setTo('')
    setFilterError('')
    setFilters({})
  }

  const retryAll = () => {
    void kpis.refetch()
    void series.refetch()
    void alerts.refetch()
  }

  const loading = kpis.isLoading || series.isLoading || alerts.isLoading
  const failed = kpis.isError || series.isError || alerts.isError
  const hasData = (series.data?.length ?? 0) > 0
  const chartData = {
    labels: series.data?.map((item) => new Date(`${item.date}T12:00:00`).toLocaleDateString('pt-BR')) ?? [],
    datasets: [{
      label: 'Consumo (kWh)',
      data: series.data?.map((item) => item.kwh) ?? [],
      borderColor: '#46d69a',
      backgroundColor: 'rgba(70, 214, 154, .14)',
      pointBackgroundColor: '#0b7752',
      pointRadius: 4,
      tension: 0.32,
      fill: true,
    }],
  }

  return (
    <section>
      <div className="page-heading page-heading-row">
        <div>
          <p className="eyebrow">Visão geral</p>
          <h1>Dashboard de consumo</h1>
          <p>Indicadores calculados a partir das leituras da sua conta.</p>
        </div>
        <Link className="button button-primary" to="/upload">Importar CSV</Link>
      </div>

      <form className="card filter-bar" onSubmit={applyFilters}>
        <label className="field"><span>De</span><input type="date" value={from} onChange={(event) => setFrom(event.target.value)} /></label>
        <label className="field"><span>Até</span><input type="date" value={to} onChange={(event) => setTo(event.target.value)} /></label>
        <button className="button button-primary">Aplicar período</button>
        <button type="button" className="button button-ghost" onClick={clearFilters}>Limpar filtros</button>
        {filterError && <div className="alert alert-error filter-error" role="alert">{filterError}</div>}
      </form>

      {imported && <div className="alert alert-success" role="status">Arquivo importado com sucesso.</div>}
      {failed && <div className="alert alert-error" role="alert">Não foi possível carregar o dashboard. <button className="text-button" onClick={retryAll}>Tentar novamente</button></div>}

      <div className="kpi-grid" aria-busy={loading}>
        <KpiCard label="Energia total" value={loading ? '—' : `${number.format(kpis.data?.total_energy_kwh ?? 0)} kWh`} tone="green" />
        <KpiCard label="Emissões estimadas" value={loading ? '—' : `${co2.format(kpis.data?.total_co2e_t ?? 0)} tCO₂e`} tone="blue" />
        <KpiCard label="Média diária" value={loading ? '—' : `${number.format(kpis.data?.daily_avg_kwh ?? 0)} kWh`} tone="amber" />
      </div>

      {!loading && !hasData ? (
        <div className="card empty-state">
          <span className="empty-icon" aria-hidden="true">↗</span>
          <h2>Seu painel está pronto para receber dados</h2>
          <p>Importe o arquivo de exemplo ou um CSV no formato documentado.</p>
          <Link className="button button-primary" to="/upload">Fazer primeira importação</Link>
        </div>
      ) : (
        <div className="dashboard-grid">
          <article className="card chart-card">
            <div className="card-heading">
              <div><p className="eyebrow">Série histórica</p><h2>Consumo diário</h2></div>
              <button className="button button-ghost button-small" onClick={() => setShowTable((value) => !value)}>{showTable ? 'Ver gráfico' : 'Ver tabela'}</button>
            </div>
            {showTable ? <div className="table-wrap"><table className="data-table"><caption className="visually-hidden">Consumo diário no período</caption><thead><tr><th>Data</th><th>Consumo</th><th>CO₂e estimado</th></tr></thead><tbody>
              {series.data?.map((item) => <tr key={item.date}><td>{new Date(`${item.date}T12:00:00`).toLocaleDateString('pt-BR')}</td><td>{number.format(item.kwh)} kWh</td><td>{co2.format(item.co2e)} tCO₂e</td></tr>)}
            </tbody></table></div> : <div className="chart-wrap">
              <Line
                data={chartData}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: { legend: { display: false } },
                  scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: 'rgba(19, 50, 42, .08)' } },
                  },
                }}
              />
            </div>}
          </article>

          <article className="card alerts-card">
            <div className="card-heading">
              <div><p className="eyebrow">Anomalias simples</p><h2>Dias em pico</h2></div>
              <span className="count-badge">{alerts.data?.length ?? 0}</span>
            </div>
            {alerts.data?.length ? (
              <ul className="peak-list">
                {alerts.data.map((alert) => (
                  <li key={alert.date}>
                    <span><strong>{new Date(`${alert.date}T12:00:00`).toLocaleDateString('pt-BR')}</strong><small>média semanal {number.format(alert.week_avg_kwh)} kWh · limite {number.format(alert.threshold_kwh)} kWh</small></span>
                    <span><strong>{number.format(alert.total_kwh)} kWh</strong><small>+{number.format(alert.percent_over)}% acima</small></span>
                  </li>
                ))}
              </ul>
            ) : <p className="muted">Nenhum dia ficou 30% acima da média da própria semana.</p>}
          </article>
        </div>
      )}
    </section>
  )
}

function KpiCard({ label, value, tone }: { label: string; value: string; tone: string }) {
  return (
    <article className={`card kpi-card kpi-${tone}`}>
      <span className="kpi-dot" aria-hidden="true" />
      <p>{label}</p>
      <strong>{value}</strong>
    </article>
  )
}
