import React from 'react'
import { useQuery } from 'react-query'
import axios from 'axios'
import { Line } from 'react-chartjs-2'
import {
  Chart as ChartJS,
  LineElement,
  PointElement,
  LinearScale,
  CategoryScale,
  Tooltip,
  Legend,
} from 'chart.js'

ChartJS.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend)

const DashboardPage: React.FC = () => {
  const { data: kpis } = useQuery('kpis', async () => {
    const response = await axios.get('/api/dashboard/kpis')
    return response.data
  })

  const { data: series } = useQuery('series', async () => {
    const response = await axios.get('/api/dashboard/series')
    return response.data
  })

  const { data: alerts } = useQuery('alerts', async () => {
    const response = await axios.get('/api/alerts')
    return response.data
  })

  const chartData = {
    labels: series?.map((item: any) => item.date) ?? [],
    datasets: [
      {
        label: 'kWh',
        data: series?.map((item: any) => item.kwh) ?? [],
        tension: 0.3,
      },
    ],
  }

  return (
    <div className="p-8">
      <h1 className="text-2xl font-bold mb-4">Dashboard</h1>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div className="bg-white p-4 rounded shadow">
          <h2 className="text-sm text-gray-600">Total kWh</h2>
          <p className="text-xl font-bold">{kpis?.total_energy_kwh?.toFixed(3)}</p>
        </div>
        <div className="bg-white p-4 rounded shadow">
          <h2 className="text-sm text-gray-600">Total CO₂e (t)</h2>
          <p className="text-xl font-bold">{kpis?.total_co2e_t?.toFixed(6)}</p>
        </div>
        <div className="bg-white p-4 rounded shadow">
          <h2 className="text-sm text-gray-600">Média diária (kWh)</h2>
          <p className="text-xl font-bold">{kpis?.daily_avg_kwh?.toFixed(3)}</p>
        </div>
      </div>

      <div className="bg-white p-4 rounded shadow mb-8">
        <h2 className="text-lg font-bold mb-2">Consumo diário</h2>
        <Line data={chartData} />
      </div>

      <div className="bg-white p-4 rounded shadow">
        <h2 className="text-lg font-bold mb-2">Dias em pico</h2>
        {alerts && alerts.length > 0 ? (
          <ul className="list-disc pl-4">
            {alerts.map((date: string) => (
              <li key={date}>{date}</li>
            ))}
          </ul>
        ) : (
          <p>Nenhum alerta</p>
        )}
      </div>
    </div>
  )
}

export default DashboardPage