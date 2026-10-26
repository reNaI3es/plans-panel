import { useState, useEffect } from 'react'

function App() {
  const [mounted, setMounted] = useState(false)
  const [time, setTime] = useState(new Date())
  const [greeting, setGreeting] = useState('')
  const [particles, setParticles] = useState<Array<{ id: number; x: number; y: number; size: number; delay: number }>>([])

  useEffect(() => {
    setMounted(true)
    const interval = setInterval(() => setTime(new Date()), 1000)
    
    // Generate particles
    const newParticles = Array.from({ length: 20 }, (_, i) => ({
      id: i,
      x: Math.random() * 100,
      y: Math.random() * 100,
      size: Math.random() * 4 + 2,
      delay: Math.random() * 5,
    }))
    setParticles(newParticles)

    return () => clearInterval(interval)
  }, [])

  useEffect(() => {
    const hour = time.getHours()
    if (hour >= 5 && hour < 12) setGreeting('Доброе утро')
    else if (hour >= 12 && hour < 17) setGreeting('Добрый день')
    else if (hour >= 17 && hour < 22) setGreeting('Добрый вечер')
    else setGreeting('Доброй ночи')
  }, [time])

  const formatTime = (date: Date) => {
    return date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
  }

  const formatDate = (date: Date) => {
    return date.toLocaleDateString('ru-RU', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex items-center justify-center overflow-hidden relative">
      {/* Animated background particles */}
      {particles.map((particle) => (
        <div
          key={particle.id}
          className="absolute rounded-full bg-white/10 animate-float"
          style={{
            left: `${particle.x}%`,
            top: `${particle.y}%`,
            width: `${particle.size}px`,
            height: `${particle.size}px`,
            animationDelay: `${particle.delay}s`,
            animationDuration: `${3 + particle.delay}s`,
          }}
        />
      ))}

      {/* Gradient orbs */}
      <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-pulse" />
      <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl animate-pulse" style={{ animationDelay: '1s' }} />
      <div className="absolute top-1/2 left-1/2 w-64 h-64 bg-pink-500/10 rounded-full blur-3xl animate-pulse" style={{ animationDelay: '2s' }} />

      {/* Main content */}
      <div className={`relative z-10 text-center px-6 transition-all duration-1000 ${mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'}`}>
        {/* Wave emoji */}
        <div className="text-7xl md:text-8xl mb-6 animate-bounce-slow">
          👋
        </div>

        {/* Greeting */}
        <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-4 tracking-tight">
          <span className="bg-gradient-to-r from-purple-400 via-pink-400 to-blue-400 bg-clip-text text-transparent">
            {greeting}!
          </span>
        </h1>

        {/* Subtitle */}
        <p className="text-xl md:text-2xl text-white/70 mb-8 max-w-2xl mx-auto">
          Рад тебя видеть! Вот твоя интерактивная страничка ✨
        </p>

        {/* Time card */}
        <div className="inline-block bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 mb-8 shadow-2xl hover:bg-white/10 transition-all duration-300 hover:scale-105">
          <div className="text-5xl md:text-6xl font-mono font-bold text-white mb-3 tracking-wider">
            {formatTime(time)}
          </div>
          <div className="text-white/50 text-lg capitalize">
            {formatDate(time)}
          </div>
        </div>

        {/* Feature cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto mt-8">
          <FeatureCard
            emoji="🎨"
            title="Красивый дизайн"
            description="Градиенты, анимации и эффекты"
            delay={0}
          />
          <FeatureCard
            emoji="⚡"
            title="Быстрый"
            description="React + Vite + Tailwind"
            delay={200}
          />
          <FeatureCard
            emoji="🚀"
            title="Современный"
            description="TypeScript и ES6+"
            delay={400}
          />
        </div>

        {/* Interactive button */}
        <div className="mt-10">
          <button
            onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
            className="px-8 py-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold rounded-full text-lg hover:from-purple-600 hover:to-pink-600 transition-all duration-300 hover:scale-110 hover:shadow-lg hover:shadow-purple-500/25 active:scale-95"
          >
            ✨ Нажми меня!
          </button>
        </div>
      </div>

      {/* Footer */}
      <div className="absolute bottom-6 left-0 right-0 text-center text-white/30 text-sm">
        Сделано с ❤️ и React
      </div>
    </div>
  )
}

function FeatureCard({ emoji, title, description, delay }: { emoji: string; title: string; description: string; delay: number }) {
  const [visible, setVisible] = useState(false)

  useEffect(() => {
    const timer = setTimeout(() => setVisible(true), 800 + delay)
    return () => clearTimeout(timer)
  }, [delay])

  return (
    <div
      className={`bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-5 transition-all duration-500 hover:bg-white/10 hover:border-white/20 hover:scale-105 cursor-default ${
        visible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'
      }`}
    >
      <div className="text-3xl mb-2">{emoji}</div>
      <h3 className="text-white font-semibold text-lg">{title}</h3>
      <p className="text-white/50 text-sm mt-1">{description}</p>
    </div>
  )
}

export default App
