import { useState } from 'react'

type Page = 'landing' | 'dashboard' | 'tasks' | 'team' | 'kb' | 'servers' | 'server-edit' | 'ai' | 'docs'

function App() {
  const [page, setPage] = useState<Page>('landing')
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [editingServerId, setEditingServerId] = useState<string | null>(null)

  const navigateToServerEdit = (id: string) => {
    setEditingServerId(id)
    setPage('server-edit')
  }

  return (
    <div className="min-h-screen bg-[#0f1419] text-[#e1e8ed]" style={{ fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif" }}>
      {page === 'landing' ? (
        <LandingPage onEnter={() => setPage('dashboard')} />
      ) : (
        <div className="flex">
          <Sidebar page={page} setPage={setPage} open={sidebarOpen} setOpen={setSidebarOpen} />
          <main className="flex-1 p-4 md:p-6 ml-0 md:ml-[260px] min-h-screen">
            <button
              onClick={() => setSidebarOpen(!sidebarOpen)}
              className="md:hidden mb-4 p-2 bg-[#1a2332] border border-[#2a3a4a] rounded-lg"
            >
              ☰ Меню
            </button>
            {page === 'dashboard' && <DashboardPage />}
            {page === 'tasks' && <TasksPage />}
            {page === 'team' && <TeamPage />}
            {page === 'kb' && <KbPage />}
            {page === 'servers' && <ServersPage onEdit={navigateToServerEdit} />}
            {page === 'server-edit' && <ServerEditPage serverId={editingServerId || 'srv000000001'} onBack={() => setPage('servers')} />}
            {page === 'ai' && <AiPage />}
            {page === 'docs' && <DocsPage />}
          </main>
        </div>
      )}
    </div>
  )
}

function LandingPage({ onEnter }: { onEnter: () => void }) {
  return (
    <div className="min-h-screen flex items-center justify-center relative overflow-hidden">
      <div className="absolute inset-0 opacity-20">
        <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-[#4a9eff] rounded-full blur-[128px] animate-pulse" />
        <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500 rounded-full blur-[128px] animate-pulse" style={{ animationDelay: '1s' }} />
      </div>
      <div className="relative text-center px-6 max-w-2xl">
        <div className="text-7xl mb-6">🎮</div>
        <h1 className="text-4xl md:text-6xl font-bold mb-4 bg-gradient-to-r from-[#4a9eff] to-purple-400 bg-clip-text text-transparent">
          reNaI3es
        </h1>
        <p className="text-lg text-[#8899a6] mb-8 leading-relaxed">
          Закрита панель керування кіберспортивною командою.<br />
          Завдання, сервери CS2/Minecraft/Unturned, база знань, ШІ-помічник.
        </p>
        <button
          onClick={onEnter}
          className="px-8 py-3 bg-[#4a9eff] text-white font-semibold rounded-lg hover:bg-[#3a8eef] transition-all hover:scale-105"
        >
          Увійти →
        </button>
        <div className="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-[#5c6e7e]">
          <div>🔐 Argon2id + 2FA</div>
          <div>📋 Kanban + Календар</div>
          <div>🖥️ Моніторинг серверів</div>
          <div>🤖 ШІ-помічник</div>
        </div>
      </div>
    </div>
  )
}

function Sidebar({ page, setPage, open, setOpen }: { page: Page; setPage: (p: Page) => void; open: boolean; setOpen: (v: boolean) => void }) {
  const links: { id: Page; label: string; icon: string }[] = [
    { id: 'dashboard', label: 'Головна', icon: '📊' },
    { id: 'tasks', label: 'Завдання', icon: '✅' },
    { id: 'team', label: 'Команда', icon: '👥' },
    { id: 'kb', label: 'База знань', icon: '📚' },
    { id: 'servers', label: 'Сервери', icon: '🖥️' },
    { id: 'ai', label: 'ШІ-помічник', icon: '🤖' },
    { id: 'docs', label: 'Документація', icon: '📄' },
  ]

  const activePage = (page === 'server-edit') ? 'servers' : page

  return (
    <>
      {open && <div className="fixed inset-0 bg-black/50 z-40 md:hidden" onClick={() => setOpen(false)} />}
      <aside className={`fixed top-0 left-0 h-full w-[260px] bg-[#1a2332] border-r border-[#2a3a4a] p-4 z-50 transition-transform md:translate-x-0 ${open ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="mb-6">
          <span className="text-xl font-bold">🎮 reNaI3es</span>
        </div>
        <nav className="space-y-1">
          {links.map(l => (
            <button
              key={l.id}
              onClick={() => { setPage(l.id); setOpen(false) }}
              className={`w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors ${activePage === l.id ? 'bg-[#1f2b3d] text-[#4a9eff] font-medium' : 'text-[#8899a6] hover:bg-[#1f2b3d] hover:text-[#e1e8ed]'}`}
            >
              <span>{l.icon}</span>
              {l.label}
            </button>
          ))}
        </nav>
        <div className="absolute bottom-4 left-4 right-4 pt-4 border-t border-[#2a3a4a]">
          <div className="text-sm text-[#8899a6]">Засновник</div>
          <div className="text-xs text-[#5c6e7e]">owner</div>
        </div>
      </aside>
    </>
  )
}

function DashboardPage() {
  const stats = [
    { value: '12', label: 'Активних завдань', color: '#4a9eff' },
    { value: '3', label: 'Прострочено', color: '#f44336' },
    { value: '5', label: 'На перевірці', color: '#ff9800' },
    { value: '28', label: 'Виконано за 7 днів', color: '#4caf50' },
  ]
  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">Панель керування</h1>
      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        {stats.map(s => (
          <div key={s.label} className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-4 text-center">
            <div className="text-3xl font-bold" style={{ color: s.color }}>{s.value}</div>
            <div className="text-xs text-[#8899a6] mt-1">{s.label}</div>
          </div>
        ))}
      </div>
      <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-4 mb-6">
        <h2 className="font-semibold mb-3">⚡ Потребує уваги</h2>
        {['Прострочено: оновлення плагінів Minecraft', 'На перевірці: звіт контенту за тиждень', 'Сервер CS2 #2 — offline 15 хв'].map((t, i) => (
          <div key={i} className="py-2 border-b border-[#2a3a4a] last:border-0 text-sm text-[#8899a6]">{t}</div>
        ))}
      </div>
      <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-4">
        <h2 className="font-semibold mb-3">👥 Команда</h2>
        <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
          {['Олексій', 'Марія', 'Дмитро', 'Анна', 'Іван'].map(name => (
            <div key={name} className="bg-[#0f1419] border border-[#2a3a4a] rounded-lg p-3 flex items-center gap-3">
              <div className="w-8 h-8 rounded-full bg-[#4a9eff] flex items-center justify-center text-xs font-bold text-white">
                {name[0]}
              </div>
              <div>
                <div className="text-sm font-medium">{name}</div>
                <div className="text-xs text-[#5c6e7e]">3 завдання</div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}

function TasksPage() {
  const tasks = [
    { code: 'RN-000142', title: 'Оновити плагіни Minecraft', priority: 'high', status: 'in_progress', due: '15.01' },
    { code: 'RN-000141', title: 'Перевірити бекапи CS2', priority: 'normal', status: 'review', due: '14.01' },
    { code: 'RN-000140', title: 'Звіт контенту за тиждень', priority: 'normal', status: 'new', due: '16.01' },
    { code: 'RN-000139', title: 'Реагувати на скарги гравців', priority: 'critical', status: 'in_progress', due: '13.01' },
    { code: 'RN-000138', title: 'Оновити SSL-сертифікати', priority: 'high', status: 'done', due: '12.01' },
  ]
  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">✅ Завдання</h1>
        <button className="px-4 py-2 bg-[#4a9eff] text-white text-sm rounded-lg">+ Нове</button>
      </div>
      <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-[#2a3a4a]">
              <th className="text-left p-3 text-xs text-[#5c6e7e] uppercase">Код</th>
              <th className="text-left p-3 text-xs text-[#5c6e7e] uppercase">Назва</th>
              <th className="text-left p-3 text-xs text-[#5c6e7e] uppercase">Пріоритет</th>
              <th className="text-left p-3 text-xs text-[#5c6e7e] uppercase">Статус</th>
              <th className="text-left p-3 text-xs text-[#5c6e7e] uppercase">Строк</th>
            </tr>
          </thead>
          <tbody>
            {tasks.map(t => (
              <tr key={t.code} className="border-b border-[#2a3a4a] hover:bg-[#1f2b3d]">
                <td className="p-3 font-mono text-xs">{t.code}</td>
                <td className="p-3 font-medium">{t.title}</td>
                <td className="p-3"><Badge text={t.priority} color={t.priority === 'critical' ? '#f44336' : t.priority === 'high' ? '#ff9800' : '#4a9eff'} /></td>
                <td className="p-3"><Badge text={t.status} color={t.status === 'done' ? '#4caf50' : t.status === 'review' ? '#9c27b0' : '#4a9eff'} /></td>
                <td className="p-3 text-[#8899a6]">{t.due}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}

function TeamPage() {
  const members = [
    { name: 'Олексій', role: 'Куратор', cats: ['CS2', 'Безпека'], tasks: 5, workload: 3 },
    { name: 'Марія', role: 'Учасник', cats: ['Minecraft', 'Контент'], tasks: 3, workload: 2 },
    { name: 'Дмитро', role: 'Учасник', cats: ['CS2', 'Розробка'], tasks: 4, workload: 4 },
    { name: 'Анна', role: 'Учасник', cats: ['Discord', 'Дизайн'], tasks: 2, workload: 1 },
    { name: 'Іван', role: 'Спостерігач', cats: [], tasks: 0, workload: 0 },
  ]
  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">👥 Команда</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        {members.map(m => (
          <div key={m.name} className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-4">
            <div className="flex items-center gap-3 mb-3">
              <div className="w-10 h-10 rounded-full bg-[#4a9eff] flex items-center justify-center font-bold">{m.name[0]}</div>
              <div>
                <div className="font-medium">{m.name}</div>
                <div className="text-xs text-[#8899a6]">{m.role}</div>
              </div>
            </div>
            <div className="flex gap-2 flex-wrap mb-2">
              {m.cats.map(c => <span key={c} className="text-xs px-2 py-0.5 bg-[#0f1419] border border-[#2a3a4a] rounded">{c}</span>)}
            </div>
            <div className="flex justify-between text-xs text-[#8899a6]">
              <span>Завдань: {m.tasks}</span>
              <span>Навантаження: {'●'.repeat(m.workload)}{'○'.repeat(5 - m.workload)}</span>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

function KbPage() {
  const articles = [
    { title: 'Регламент команди reNaI3es', section: 'Регламенти', pinned: true },
    { title: 'Як оформлювати звіт за завданням', section: 'Регламенти', pinned: true },
    { title: 'Доступи: де що лежить', section: 'Доступи', pinned: true },
    { title: 'Інструкція: сервери CS2', section: 'Сервери', pinned: false },
    { title: 'Інструкція: сервери Minecraft', section: 'Сервери', pinned: false },
    { title: 'Що робити, якщо сервер впав', section: 'Інструменти', pinned: true },
    { title: 'Новачку: перші кроки', section: 'Для новачків', pinned: true },
    { title: 'Правила комунікації', section: 'Для новачків', pinned: false },
  ]
  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">📚 База знань</h1>
      <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-4 mb-4">
        <input type="text" placeholder="Пошук по базі знань..." className="w-full bg-[#0f1419] border border-[#2a3a4a] rounded-lg px-4 py-2 text-sm" />
      </div>
      <div className="space-y-2">
        {articles.map(a => (
          <div key={a.title} className="bg-[#1a2332] border border-[#2a3a4a] rounded-lg p-4 hover:border-[#4a9eff] transition-colors cursor-pointer">
            <div className="flex items-center gap-2">
              {a.pinned && <span className="text-xs">📌</span>}
              <span className="font-medium">{a.title}</span>
            </div>
            <div className="text-xs text-[#8899a6] mt-1">{a.section}</div>
          </div>
        ))}
      </div>
    </div>
  )
}

function ServersPage({ onEdit }: { onEdit: (id: string) => void }) {
  const servers = [
    { id: 'srv000000001', name: 'CS2 Competitive #1', game: '🎯 CS2', status: 'online', players: '24/32', map: 'de_dust2', ping: 12 },
    { id: 'srv000000002', name: 'CS2 Aims #2', game: '🎯 CS2', status: 'offline', players: '0/32', map: '—', ping: null },
    { id: 'srv000000003', name: 'Minecraft Survival', game: '⛏️ MC', status: 'online', players: '18/50', map: 'world', ping: 25 },
    { id: 'srv000000004', name: 'Unturned PvE', game: '🧟 UR', status: 'online', players: '8/24', map: 'PEI', ping: 45 },
    { id: 'srv000000005', name: 'CS:GO Classic', game: '🔫 CSGO', status: 'online', players: '12/20', map: 'de_inferno', ping: 18 },
  ]
  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">🖥️ Сервери</h1>
        <button className="px-4 py-2 bg-[#4a9eff] text-white text-sm rounded-lg hover:bg-[#3a8eef] transition-colors">+ Додати</button>
      </div>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {servers.map(s => (
          <div key={s.id} className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-4 hover:border-[#4a9eff]/50 transition-colors">
            <div className="flex justify-between items-start mb-2">
              <div>
                <div className="font-medium">{s.name}</div>
                <div className="text-xs text-[#8899a6]">{s.game}</div>
              </div>
              <span className={`text-xs px-2 py-0.5 rounded ${s.status === 'online' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'}`}>
                {s.status === 'online' ? '● Online' : '● Offline'}
              </span>
            </div>
            <div className="flex gap-4 text-xs text-[#8899a6] mb-3">
              <span>👥 {s.players}</span>
              <span>🗺️ {s.map}</span>
              {s.ping && <span>📶 {s.ping}ms</span>}
            </div>
            <div className="flex gap-2">
              <button
                onClick={() => onEdit(s.id)}
                className="flex-1 text-center text-xs px-3 py-1.5 bg-[#4a9eff]/10 text-[#4a9eff] border border-[#4a9eff]/30 rounded-lg hover:bg-[#4a9eff]/20 transition-colors"
              >
                ✏️ Редагувати
              </button>
              <button className="text-xs px-3 py-1.5 bg-red-500/10 text-red-400 border border-red-500/30 rounded-lg hover:bg-red-500/20 transition-colors">
                🗑️
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

function ServerEditPage({ serverId, onBack }: { serverId: string; onBack: () => void }) {
  const [activeTab, setActiveTab] = useState<'general' | 'config' | 'plugins' | 'access' | 'logs'>('general')
  const [saved, setSaved] = useState(false)

  const serverData = {
    id: serverId,
    name: 'CS2 Competitive #1',
    game: 'CS2',
    status: 'online',
    ip: '185.16.100.50',
    port: 27015,
    rconPort: 27015,
    maxPlayers: 32,
    map: 'de_dust2',
    tickrate: 128,
    region: 'EU',
    description: 'Основний змагальний сервер CS2 для тренувань команди.',
    autoRestart: true,
    backupInterval: 6,
    lastRestart: '2025-01-12 08:00',
    uptime: '3д 14г 22хв',
    ram: '4096',
    cpu: '4 cores',
    disk: '50 GB',
  }

  const handleSave = () => {
    setSaved(true)
    setTimeout(() => setSaved(false), 3000)
  }

  const tabs = [
    { id: 'general' as const, label: 'Загальне', icon: '📋' },
    { id: 'config' as const, label: 'Конфігурація', icon: '⚙️' },
    { id: 'plugins' as const, label: 'Плагіни', icon: '🧩' },
    { id: 'access' as const, label: 'Доступи', icon: '🔑' },
    { id: 'logs' as const, label: 'Логи', icon: '📝' },
  ]

  return (
    <div>
      {/* Breadcrumb */}
      <div className="flex items-center gap-2 text-sm text-[#8899a6] mb-4">
        <button onClick={onBack} className="hover:text-[#4a9eff] transition-colors">🖥️ Сервери</button>
        <span>/</span>
        <span className="text-[#e1e8ed]">{serverData.name}</span>
        <span>/</span>
        <span className="text-[#4a9eff]">Редагувати</span>
      </div>

      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
          <div className="flex items-center gap-3">
            <h1 className="text-2xl font-bold">{serverData.name}</h1>
            <span className="text-xs px-2 py-0.5 rounded bg-green-500/20 text-green-400">● Online</span>
          </div>
          <p className="text-sm text-[#8899a6] mt-1">ID: {serverId} • {serverData.game} • {serverData.ip}:{serverData.port}</p>
        </div>
        <div className="flex gap-2">
          <button className="px-4 py-2 text-sm bg-green-500/10 text-green-400 border border-green-500/30 rounded-lg hover:bg-green-500/20 transition-colors">
            ▶ Перезапустити
          </button>
          <button className="px-4 py-2 text-sm bg-red-500/10 text-red-400 border border-red-500/30 rounded-lg hover:bg-red-500/20 transition-colors">
            ⏹ Зупинити
          </button>
        </div>
      </div>

      {/* Stats row */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-lg p-3 text-center">
          <div className="text-lg font-bold text-[#4a9eff]">{serverData.uptime}</div>
          <div className="text-xs text-[#8899a6]">Аптайм</div>
        </div>
        <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-lg p-3 text-center">
          <div className="text-lg font-bold text-green-400">24/32</div>
          <div className="text-xs text-[#8899a6]">Гравці</div>
        </div>
        <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-lg p-3 text-center">
          <div className="text-lg font-bold text-[#ff9800]">12ms</div>
          <div className="text-xs text-[#8899a6]">Пінг</div>
        </div>
        <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-lg p-3 text-center">
          <div className="text-lg font-bold text-purple-400">67%</div>
          <div className="text-xs text-[#8899a6]">RAM</div>
        </div>
        <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-lg p-3 text-center">
          <div className="text-lg font-bold text-cyan-400">23%</div>
          <div className="text-xs text-[#8899a6]">CPU</div>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex gap-1 mb-6 overflow-x-auto pb-1 border-b border-[#2a3a4a]">
        {tabs.map(tab => (
          <button
            key={tab.id}
            onClick={() => setActiveTab(tab.id)}
            className={`px-4 py-2.5 text-sm rounded-t-lg transition-colors whitespace-nowrap ${
              activeTab === tab.id
                ? 'bg-[#1a2332] text-[#4a9eff] border-b-2 border-[#4a9eff] font-medium'
                : 'text-[#8899a6] hover:text-[#e1e8ed] hover:bg-[#1a2332]/50'
            }`}
          >
            {tab.icon} {tab.label}
          </button>
        ))}
      </div>

      {/* Tab content */}
      <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-6">
        {activeTab === 'general' && (
          <div className="space-y-5">
            <h2 className="text-lg font-semibold mb-4">Загальні налаштування</h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              <FormField label="Назва сервера" value={serverData.name} />
              <FormField label="Гра" value={serverData.game} disabled />
              <FormField label="IP-адреса" value={serverData.ip} />
              <FormField label="Порт" value={String(serverData.port)} type="number" />
              <FormField label="RCON порт" value={String(serverData.rconPort)} type="number" />
              <FormField label="Макс. гравців" value={String(serverData.maxPlayers)} type="number" />
              <FormField label="Карта" value={serverData.map} />
              <FormField label="Тікрейт" value={String(serverData.tickrate)} type="number" />
              <FormField label="Регіон" value={serverData.region} />
              <FormField label="RAM (MB)" value={serverData.ram} type="number" />
            </div>

            <div>
              <label className="block text-sm text-[#8899a6] mb-1.5">Опис</label>
              <textarea
                defaultValue={serverData.description}
                rows={3}
                className="w-full bg-[#0f1419] border border-[#2a3a4a] rounded-lg px-4 py-2.5 text-sm text-[#e1e8ed] focus:border-[#4a9eff] focus:outline-none transition-colors resize-none"
              />
            </div>

            <div className="flex items-center gap-3 pt-2">
              <label className="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" defaultChecked className="sr-only peer" />
                <div className="w-10 h-5 bg-[#2a3a4a] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#4a9eff]"></div>
              </label>
              <span className="text-sm text-[#e1e8ed]">Авто-перезапуск при падінні</span>
            </div>
          </div>
        )}

        {activeTab === 'config' && (
          <div className="space-y-5">
            <h2 className="text-lg font-semibold mb-4">Конфігурація сервера</h2>
            
            <div>
              <label className="block text-sm text-[#8899a6] mb-1.5">server.cfg</label>
              <textarea
                defaultValue={`hostname "reNaI3es Competitive #1"\nrcon_password "****"\nsv_cheats 0\nmp_maxrounds 30\nmp_freezetime 12\nmp_roundtime 1.92\nmp_startmoney 800\nmp_buytime 20\nsv_minrate 786432\nsv_maxrate 786432\nsv_minupdaterate 64\nsv_maxupdaterate 128\nmp_autokick 0\nsv_pure 1`}
                rows={14}
                className="w-full bg-[#0f1419] border border-[#2a3a4a] rounded-lg px-4 py-3 text-sm text-[#e1e8ed] font-mono focus:border-[#4a9eff] focus:outline-none transition-colors resize-none"
              />
            </div>

            <div>
              <label className="block text-sm text-[#8899a6] mb-1.5">gamemode.cfg</label>
              <textarea
                defaultValue={`// Competitive settings\nmp_warmup_end\nmp_restartgame 1\nmp_match_can_clinch 1\nmp_overtime_enable 1\nmp_overtime_maxrounds 6\nmp_overtime_startmoney 10000`}
                rows={8}
                className="w-full bg-[#0f1419] border border-[#2a3a4a] rounded-lg px-4 py-3 text-sm text-[#e1e8ed] font-mono focus:border-[#4a9eff] focus:outline-none transition-colors resize-none"
              />
            </div>

            <div className="bg-[#0f1419] border border-[#2a3a4a] rounded-lg p-4">
              <div className="flex items-center gap-2 mb-2">
                <span className="text-yellow-400">⚠️</span>
                <span className="text-sm font-medium text-yellow-400">Увага</span>
              </div>
              <p className="text-xs text-[#8899a6]">Зміни в конфігурації застосовуються після перезавантаження сервера. Переконайтеся, що параметри коректні.</p>
            </div>
          </div>
        )}

        {activeTab === 'plugins' && (
          <div className="space-y-4">
            <h2 className="text-lg font-semibold mb-4">Плагіни та моди</h2>
            
            <div className="space-y-2">
              {[
                { name: 'MetaMod:Source', version: '1.11.0', status: 'active', desc: 'Базовий мета-плагін' },
                { name: 'SourceMod', version: '1.12.0', status: 'active', desc: 'Адміністрування та розширення' },
                { name: 'GOTV Manager', version: '2.1.0', status: 'active', desc: 'Автоматичний запис демо' },
                { name: 'MatchZy', version: '0.7.5', status: 'active', desc: 'Матчевий режим' },
                { name: 'Discord Integration', version: '1.3.2', status: 'active', desc: 'Сповіщення в Discord' },
                { name: 'Anti-Cheat Extended', version: '3.0.1', status: 'inactive', desc: 'Розширений античіт' },
                { name: 'Map Voter', version: '1.0.4', status: 'inactive', desc: 'Голосування за карти' },
              ].map(plugin => (
                <div key={plugin.name} className="flex items-center justify-between bg-[#0f1419] border border-[#2a3a4a] rounded-lg p-3 hover:border-[#4a9eff]/30 transition-colors">
                  <div className="flex items-center gap-3">
                    <div className={`w-2 h-2 rounded-full ${plugin.status === 'active' ? 'bg-green-400' : 'bg-[#5c6e7e]'}`} />
                    <div>
                      <div className="text-sm font-medium">{plugin.name}</div>
                      <div className="text-xs text-[#8899a6]">{plugin.desc}</div>
                    </div>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className="text-xs text-[#5c6e7e]">v{plugin.version}</span>
                    <label className="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" defaultChecked={plugin.status === 'active'} className="sr-only peer" />
                      <div className="w-8 h-4 bg-[#2a3a4a] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-[#4a9eff]"></div>
                    </label>
                  </div>
                </div>
              ))}
            </div>

            <button className="w-full py-2.5 border border-dashed border-[#2a3a4a] rounded-lg text-sm text-[#8899a6] hover:border-[#4a9eff] hover:text-[#4a9eff] transition-colors">
              + Встановити плагін
            </button>
          </div>
        )}

        {activeTab === 'access' && (
          <div className="space-y-5">
            <h2 className="text-lg font-semibold mb-4">Доступи та RCON</h2>
            
            <div>
              <label className="block text-sm text-[#8899a6] mb-1.5">RCON пароль</label>
              <div className="flex gap-2">
                <input
                  type="password"
                  defaultValue="super_secret_rcon_pass"
                  className="flex-1 bg-[#0f1419] border border-[#2a3a4a] rounded-lg px-4 py-2.5 text-sm text-[#e1e8ed] font-mono focus:border-[#4a9eff] focus:outline-none transition-colors"
                />
                <button className="px-4 py-2.5 bg-[#0f1419] border border-[#2a3a4a] rounded-lg text-sm hover:border-[#4a9eff] transition-colors">
                  👁️
                </button>
                <button className="px-4 py-2.5 bg-[#4a9eff]/10 border border-[#4a9eff]/30 text-[#4a9eff] rounded-lg text-sm hover:bg-[#4a9eff]/20 transition-colors">
                  🔄 Згенерувати
                </button>
              </div>
            </div>

            <div>
              <label className="block text-sm text-[#8899a6] mb-1.5">Адміністратори</label>
              <div className="space-y-2">
                {[
                  { name: 'Олексій', steam: 'STEAM_0:1:12345678', flags: 'z', level: 'root' },
                  { name: 'Дмитро', steam: 'STEAM_0:1:87654321', flags: 'bcdefgz', level: 'admin' },
                  { name: 'Марія', steam: 'STEAM_0:0:11223344', flags: 'bce', level: 'moderator' },
                ].map(admin => (
                  <div key={admin.steam} className="flex items-center justify-between bg-[#0f1419] border border-[#2a3a4a] rounded-lg p-3">
                    <div className="flex items-center gap-3">
                      <div className="w-8 h-8 rounded-full bg-[#4a9eff] flex items-center justify-center text-xs font-bold">{admin.name[0]}</div>
                      <div>
                        <div className="text-sm font-medium">{admin.name}</div>
                        <div className="text-xs text-[#5c6e7e] font-mono">{admin.steam}</div>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      <span className="text-xs px-2 py-0.5 rounded bg-[#4a9eff]/10 text-[#4a9eff] border border-[#4a9eff]/30">{admin.level}</span>
                      <span className="text-xs text-[#5c6e7e] font-mono">{admin.flags}</span>
                      <button className="text-red-400 hover:text-red-300 text-sm">✕</button>
                    </div>
                  </div>
                ))}
              </div>
              <button className="mt-2 text-sm text-[#4a9eff] hover:underline">+ Додати адміністратора</button>
            </div>

            <div>
              <label className="block text-sm text-[#8899a6] mb-1.5">Whitelist (Steam ID)</label>
              <textarea
                defaultValue="STEAM_0:1:12345678&#10;STEAM_0:1:87654321&#10;STEAM_0:0:11223344"
                rows={4}
                className="w-full bg-[#0f1419] border border-[#2a3a4a] rounded-lg px-4 py-2.5 text-sm text-[#e1e8ed] font-mono focus:border-[#4a9eff] focus:outline-none transition-colors resize-none"
              />
            </div>
          </div>
        )}

        {activeTab === 'logs' && (
          <div className="space-y-4">
            <h2 className="text-lg font-semibold mb-4">Логи сервера</h2>
            
            <div className="flex gap-2 mb-4">
              <button className="px-3 py-1.5 text-xs bg-[#4a9eff]/10 text-[#4a9eff] border border-[#4a9eff]/30 rounded-lg">Всі</button>
              <button className="px-3 py-1.5 text-xs text-[#8899a6] border border-[#2a3a4a] rounded-lg hover:border-[#4a9eff]/30">Помилки</button>
              <button className="px-3 py-1.5 text-xs text-[#8899a6] border border-[#2a3a4a] rounded-lg hover:border-[#4a9eff]/30">Підключення</button>
              <button className="px-3 py-1.5 text-xs text-[#8899a6] border border-[#2a3a4a] rounded-lg hover:border-[#4a9eff]/30">RCON</button>
            </div>

            <div className="bg-[#0f1419] border border-[#2a3a4a] rounded-lg p-4 font-mono text-xs space-y-1 max-h-[400px] overflow-y-auto">
              {[
                { time: '14:22:05', type: 'info', msg: 'Map changed to de_dust2' },
                { time: '14:22:03', type: 'info', msg: 'Match started: Team A vs Team B' },
                { time: '14:20:11', type: 'connect', msg: 'Player "s1mple" connected (STEAM_0:1:52345678)' },
                { time: '14:19:45', type: 'connect', msg: 'Player "ZywOo" connected (STEAM_0:0:98765432)' },
                { time: '14:18:30', type: 'warn', msg: 'High CPU usage detected: 89%' },
                { time: '14:15:22', type: 'info', msg: 'GOTV recording started' },
                { time: '14:12:00', type: 'info', msg: 'Server restart scheduled in 30 minutes' },
                { time: '14:10:15', type: 'error', msg: 'Failed to connect to Steam master server (retry 1/3)' },
                { time: '14:10:18', type: 'info', msg: 'Steam master server connection restored' },
                { time: '14:08:00', type: 'rcon', msg: 'RCON command from 185.16.100.1: "mp_restartgame 3"' },
                { time: '14:05:33', type: 'connect', msg: 'Player "NiKo" disconnected (reason: timeout)' },
                { time: '14:00:00', type: 'info', msg: 'Backup saved: backup_round15.txt' },
                { time: '13:55:12', type: 'info', msg: 'Plugin "MatchZy" reloaded successfully' },
                { time: '13:50:00', type: 'info', msg: 'Server uptime: 3 days 14 hours' },
              ].map((log, i) => (
                <div key={i} className="flex gap-3">
                  <span className="text-[#5c6e7e]">{log.time}</span>
                  <span className={
                    log.type === 'error' ? 'text-red-400' :
                    log.type === 'warn' ? 'text-yellow-400' :
                    log.type === 'connect' ? 'text-green-400' :
                    log.type === 'rcon' ? 'text-purple-400' :
                    'text-[#8899a6]'
                  }>[{log.type.toUpperCase()}]</span>
                  <span className="text-[#e1e8ed]">{log.msg}</span>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Save bar */}
      <div className="mt-6 flex items-center justify-between bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-4">
        <div className="text-sm text-[#8899a6]">
          Остання зміна: 12.01.2025, 14:22 • Збережено автоматично
        </div>
        <div className="flex gap-2">
          {saved && (
            <span className="text-sm text-green-400 flex items-center gap-1 animate-pulse">
              ✓ Збережено
            </span>
          )}
          <button
            onClick={onBack}
            className="px-4 py-2 text-sm text-[#8899a6] border border-[#2a3a4a] rounded-lg hover:border-[#4a9eff]/50 hover:text-[#e1e8ed] transition-colors"
          >
            Скасувати
          </button>
          <button
            onClick={handleSave}
            className="px-6 py-2 text-sm bg-[#4a9eff] text-white rounded-lg hover:bg-[#3a8eef] transition-colors font-medium"
          >
            💾 Зберегти зміни
          </button>
        </div>
      </div>
    </div>
  )
}

function FormField({ label, value, type = 'text', disabled = false }: { label: string; value: string; type?: string; disabled?: boolean }) {
  return (
    <div>
      <label className="block text-sm text-[#8899a6] mb-1.5">{label}</label>
      <input
        type={type}
        defaultValue={value}
        disabled={disabled}
        className={`w-full bg-[#0f1419] border border-[#2a3a4a] rounded-lg px-4 py-2.5 text-sm text-[#e1e8ed] focus:border-[#4a9eff] focus:outline-none transition-colors ${disabled ? 'opacity-50 cursor-not-allowed' : ''}`}
      />
    </div>
  )
}

function AiPage() {
  const [messages, setMessages] = useState([
    { role: 'assistant', text: 'Привіт! Я ШІ-помічник команди reNaI3es. Чим можу допомогти?' },
  ])
  const [input, setInput] = useState('')

  const send = () => {
    if (!input.trim()) return
    setMessages([...messages, { role: 'user', text: input }, { role: 'assistant', text: 'Я обробляю ваше запитання... (демо-режим)' }])
    setInput('')
  }

  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">🤖 ШІ-помічник</h1>
      <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-4 mb-4 h-[400px] overflow-y-auto space-y-3">
        {messages.map((m, i) => (
          <div key={i} className={`flex ${m.role === 'user' ? 'justify-end' : 'justify-start'}`}>
            <div className={`max-w-[80%] px-4 py-2 rounded-lg text-sm ${m.role === 'user' ? 'bg-[#4a9eff] text-white' : 'bg-[#0f1419] border border-[#2a3a4a]'}`}>
              {m.text}
            </div>
          </div>
        ))}
      </div>
      <div className="flex gap-2">
        <input
          type="text"
          value={input}
          onChange={e => setInput(e.target.value)}
          onKeyDown={e => e.key === 'Enter' && send()}
          placeholder="Запитай щось..."
          className="flex-1 bg-[#1a2332] border border-[#2a3a4a] rounded-lg px-4 py-2 text-sm"
        />
        <button onClick={send} className="px-6 py-2 bg-[#4a9eff] text-white rounded-lg text-sm font-medium">Надіслати</button>
      </div>
    </div>
  )
}

function DocsPage() {
  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">📄 Документація проєкту</h1>
      <div className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-6 space-y-4 text-sm">
        <div>
          <h2 className="font-bold text-lg mb-2">🏗️ Архітектура</h2>
          <p className="text-[#8899a6]">PHP 8.2+ без фреймворків. Server-side рендеринг. Vanilla JS ≤150 рядків. three.js r128 через CDN.</p>
        </div>
        <div>
          <h2 className="font-bold text-lg mb-2">📁 Структура</h2>
          <pre className="bg-[#0f1419] p-4 rounded-lg text-xs text-[#8899a6] overflow-x-auto">{`renai3es/
├── public/          # Front controller + assets
├── app/
│   ├── Core/        # Router, View, Db, Auth, Acl, Csrf, Totp...
│   ├── Controllers/ # 10 контролерів
│   └── Services/    # Monitor, Notify, Cron, Ai
├── views/           # PHP-шаблони
├── config/          # 0600, поза webroot
├── database/        # schema.sql + seed.sql
├── cron/            # Фонові задачі
├── docs/            # DEPLOY, SECURITY, ADMIN
└── tests/           # smoke.php`}</pre>
        </div>
        <div>
          <h2 className="font-bold text-lg mb-2">🔐 Безпека</h2>
          <ul className="text-[#8899a6] space-y-1 list-disc list-inside">
            <li>Argon2id паролі + 2FA TOTP (RFC 6238)</li>
            <li>Re-authentication для чутливих дій</li>
            <li>CSRF-токени + Origin/Referer перевірка</li>
            <li>PDO prepared statements всюди</li>
            <li>Sodium шифрування секретів</li>
            <li>Default deny ACL з контекстною перевіркою</li>
            <li>IDOR-захист через public_id</li>
            <li>IP хешуються (HMAC-SHA256)</li>
            <li>Impersonate з аудитом</li>
          </ul>
        </div>
        <div>
          <h2 className="font-bold text-lg mb-2">🗄️ База даних</h2>
          <p className="text-[#8899a6]">27 таблиць: roles, permissions, users, categories, tasks, task_assignments, servers, kb_articles, ai_conversations, notifications, audit_logs та інші.</p>
        </div>
        <div>
          <h2 className="font-bold text-lg mb-2">🚀 Деплой</h2>
          <p className="text-[#8899a6]">nginx + php-fpm. Працює і в підпапці (/team/), і на піддомені. Детальніше — docs/DEPLOY.md</p>
        </div>
      </div>
    </div>
  )
}

function Badge({ text, color }: { text: string; color: string }) {
  return (
    <span className="text-xs px-2 py-0.5 rounded-full" style={{ background: color + '22', color }}>
      {text}
    </span>
  )
}

export default App
