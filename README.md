# Ecommerce

Aplicación Laravel (v13) con Filament, Livewire y Laravel Sail.

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (o Docker Engine + Compose)
- Git
- Composer 2 (en el host) **o** Docker para instalar dependencias la primera vez
- Node.js 20+ (opcional en el host; los comandos de front se pueden correr con Sail)

## Setup (primera vez)

### 1. Clonar el repositorio

```bash
git clone <url-del-repo> ecommerce
cd ecommerce
```

### 2. Instalar dependencias PHP

Con Composer en el host:

```bash
composer install
```

Si no tenés Composer local, usá Docker (no hay imagen oficial `php85-composer`; usá `php84` + `--ignore-platform-reqs`):

```bash
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer install --ignore-platform-reqs
```

Alternativa con la imagen oficial de Composer:

```bash
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/app" \
  -w /app \
  composer:2 \
  composer install --ignore-platform-reqs
```

### 3. Configurar entorno

```bash
cp .env.example .env
```

Ajustá `.env` para Sail (MySQL del `compose.yaml`). Ejemplo:

```env
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

REDIS_HOST=redis
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

> Los valores de `DB_*` deben coincidir con los del servicio `mysql` en `compose.yaml` / variables `FORWARD_*` si las personalizás.

### 4. Levantar contenedores

```bash
./vendor/bin/sail up -d
```

La primera vez construye la imagen (`sail-8.5/app`). Puede tardar varios minutos.

### 5. Clave de aplicación, migraciones y storage

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan storage:link
```

### 6. Dependencias front + Git hooks (Lefthook)

```bash
./vendor/bin/sail npm install
```

El script `prepare` de npm instala los hooks de [Lefthook](https://github.com/evilmartians/lefthook) automáticamente.

Si los hooks no quedaron activos:

```bash
./vendor/bin/sail npx lefthook install
# o, con Node en el host:
npx lefthook install
```

### 7. Assets (desarrollo)

```bash
./vendor/bin/sail npm run dev
```

Para un build de producción local:

```bash
./vendor/bin/sail npm run build
```

### 8. Verificar

```bash
./vendor/bin/sail artisan test --compact
./vendor/bin/sail open
```

La app queda en [http://localhost](http://localhost) (puerto `APP_PORT`, por defecto `80`).

Mailpit (correos de desarrollo): [http://localhost:8025](http://localhost:8025) (puerto por defecto de Sail).

---

## Comandos del día a día

Usá **siempre** Sail para PHP, Artisan, Composer, tests y Node del proyecto:

| Acción | Comando |
|--------|---------|
| Subir servicios | `./vendor/bin/sail up -d` |
| Bajar servicios | `./vendor/bin/sail stop` |
| Artisan | `./vendor/bin/sail artisan …` |
| Tests | `./vendor/bin/sail artisan test --compact` |
| Pint (formato) | `./vendor/bin/sail bin pint --dirty` |
| Composer | `./vendor/bin/sail composer …` |
| npm | `./vendor/bin/sail npm …` |
| Shell del contenedor | `./vendor/bin/sail shell` |

Alias opcional (añadir al shell):

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

Luego podés usar `sail up -d`, `sail artisan migrate`, etc.

---

## Git hooks (Lefthook)

Definidos en `lefthook.yml`. Requieren **Sail levantado**.

| Hook | Qué hace |
|------|----------|
| **pre-commit** | Formatea PHP sucios con Pint y re-stagea los cambios |
| **pre-push** | Verifica estilo (`pint --dirty --test`) y corre la suite de tests |

Saltar un hook (solo si es necesario):

```bash
LEFTHOOK=0 git commit -m "…"
git push --no-verify
```

---

## Stack relevante

- PHP 8.5 / Laravel 13
- Filament 5 / Livewire 4
- Laravel Sail (Docker)
- MySQL 8.4, Redis, Meilisearch, Mailpit, Selenium
- Vite + Tailwind CSS 4
- Laravel Pint + PHPUnit 12
- Lefthook (hooks locales)

---

## Troubleshooting

**Los hooks fallan con error de Docker / sail**  
Levantá los servicios: `./vendor/bin/sail up -d`.

**Puerto 80 ocupado**  
Definí otro en `.env`: `APP_PORT=8080` y usá `http://localhost:8080`.

**Vite no refleja cambios**  
Asegurate de tener `./vendor/bin/sail npm run dev` corriendo, o regenerá assets con `npm run build`.

**Permisos en Linux**  
Sail usa `WWWUSER` / `WWWGROUP`. Si hay problemas de escritura en `storage/` o `bootstrap/cache`, revisá ownership de esos directorios o recreá contenedores con tu UID.
