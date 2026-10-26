import { useState } from 'react'

type Page = 'landing' | 'dashboard' | 'tasks' | 'team' | 'kb' | 'servers' | 'ai' | 'docs'

function App() {
  const [page, setPage] = useState<Page>('landing')
  const [sidebarOpen, setSidebarOpen] = useState(false)

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
            {page === 'servers' && <ServersPage />}
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
              className={`w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors ${page === l.id ? 'bg-[#1f2b3d] text-[#4a9eff] font-medium' : 'text-[#8899a6] hover:bg-[#1f2b3d] hover:text-[#e1e8ed]'}`}
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

function ServersPage() {
  const servers = [
    { name: 'CS2 Competitive #1', game: '🎯 CS2', status: 'online', players: '24/32', map: 'de_dust2', ping: 12 },
    { name: 'CS2 Aims #2', game: '🎯 CS2', status: 'offline', players: '0/32', map: '—', ping: null },
    { name: 'Minecraft Survival', game: '⛏️ MC', status: 'online', players: '18/50', map: 'world', ping: 25 },
    { name: 'Unturned PvE', game: '🧟 UR', status: 'online', players: '8/24', map: 'PEI', ping: 45 },
    { name: 'CS:GO Classic', game: '🔫 CSGO', status: 'online', players: '12/20', map: 'de_inferno', ping: 18 },
  ]
  return (
    <div>
      <h1 className="text-2xl font-bold mb-6">🖥️ Сервери</h1>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {servers.map(s => (
          <div key={s.name} className="bg-[#1a2332] border border-[#2a3a4a] rounded-xl p-4">
            <div className="flex justify-between items-start mb-2">
              <div>
                <div className="font-medium">{s.name}</div>
                <div className="text-xs text-[#8899a6]">{s.game}</div>
              </div>
              <span className={`text-xs px-2 py-0.5 rounded ${s.status === 'online' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'}`}>
                {s.status === 'online' ? '● Online' : '● Offline'}
              </span>
            </div>
            <div className="flex gap-4 text-xs text-[#8899a6]">
              <span>👥 {s.players}</span>
              <span>🗺️ {s.map}</span>
              {s.ping && <span>📶 {s.ping}ms</span>}
            </div>
          </div>
        ))}
      </div>
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
