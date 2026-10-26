# Безпека reNaI3es

## Що реалізовано

### Аутентифікація
- ✅ **Argon2id** для паролів (memory_cost=65536, time_cost=4, threads=2)
- ✅ Автоматичний rehash при вході (`password_needs_rehash`)
- ✅ Політика паролів: мін. 10 символів, перевірка по списку частих
- ✅ **2FA TOTP** (RFC 6238, SHA1, 30 сек, 6 цифр, вікно ±1) — обов'язкова для owner/manager
- ✅ 8 резервних кодів (зберігаються хешами)
- ✅ **Re-authentication** (5 хв) для чутливих дій
- ✅ Блок аккаунта: 5 невдач → лок 15 хв (експоненційно), після 10 — тільки Owner
- ✅ Rate limit по IP на /login, /password-reset, /ai/chat

### Сесії
- ✅ Cookie: `HttpOnly`, `Secure`, `SameSite=Strict`
- ✅ `session_regenerate_id(true)` при логіні та кожні 30 хв
- ✅ Абсолютний TTL 8 год, idle 60 хв
- ✅ `session_version` — інкремент = миттєвий кикаут усіх сесій
- ✅ Відбиток: hash(UA + IP/24) — при неспівпадінні → повторний логін
- ✅ Опція "одна сесія на аккаунт"

### Авторизація
- ✅ **Default deny** — кожен маршрут вимагає permission
- ✅ Дворівнева перевірка: контролер + модель
- ✅ Горизонтальна ізоляція: member фізично не може отримати чуже (умова в WHERE)
- ✅ IDOR-захист: `public_id` (12 випадкових символів) замість sequential id
- ✅ Файли через `/files/{token}` з перевіркою прав

### Ін'єкції та XSS
- ✅ PDO + prepared statements всюди (`ATTR_EMULATE_PREPARES=false`)
- ✅ ORDER BY / LIMIT — білий список
- ✅ Вивід через `e()` (htmlspecialchars ENT_QUOTES|ENT_SUBSTITUTE)
- ✅ Markdown-парсер: спочатку екранування, потім розмітка
- ✅ CSP-заголовок

### CSRF
- ✅ Токен 32 байти на всі POST/PUT/DELETE
- ✅ Перевірка Origin/Referer
- ✅ `hash_equals` для порівняння

### Шифрування
- ✅ Sodium (`crypto_secretbox`) для API-ключів, RCON-паролів
- ✅ Ключ у config.php (0600), не в БД

### Аудит
- ✅ Журнал дій: хто, що, коли, з якого IP (хешованого)
- ✅ Лог входів: успішні та невдачі
- ✅ IP зберігаються **хешованими** (HMAC-SHA256 з серверним секретом)

### Заголовки безпеки
- ✅ X-Content-Type-Options: nosniff
- ✅ X-Frame-Options: SAMEORIGIN
- ✅ Referrer-Policy: strict-origin-when-cross-origin
- ✅ Strict-Transport-Security (якщо HTTPS)
- ✅ Content-Security-Policy

## Як перевірити

1. **Спробувати SQL-ін'єкцію** у пошуку → має не спрацювати (prepared statements)
2. **Спробувати XSS** у коментарях → має бути екрановано
3. **Залогінитись як member** → спробувати відкрити чуже завдання за URL → 403
4. **Ввести неправильний пароль 5 разів** → акаунт блокується
5. **Перевірити cookie** → має бути HttpOnly, Secure, SameSite=Strict
6. **Перевірити CSP** → `curl -I https://...` має показати Content-Security-Policy

## Impersonate
- ✅ Тільки для Owner
- ✅ Вимагає 2FA + re-auth
- ✅ Жирна запис в аудит
- ✅ Незнімаємий червоний банер
- ✅ Вихід однією кнопкою
