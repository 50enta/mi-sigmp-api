# Instalação local — SIGMP API

[Voltar ao índice](../README.md) · [Ver requisitos](requirements.md)

## 1. Instalar dependências

Na raiz da API:

```bash
composer install
```

## 2. Criar `.env`

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

Gere uma chave local:

```bash
php artisan key:generate
```

Não envie `.env` ou `APP_KEY` ao repositório.

## 3. Criar a base de dados

```sql
CREATE DATABASE mi_sigmp
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Configure as credenciais:

```env
APP_NAME=SIGMP
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mi_sigmp
DB_USERNAME=root
DB_PASSWORD=
```

## 4. Configurar sessão, Sanctum e CORS

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

SANCTUM_STATEFUL_DOMAINS=localhost:3001,127.0.0.1:3001,localhost:5173,127.0.0.1:5173
CORS_ALLOWED_ORIGINS=http://localhost:3001,http://127.0.0.1:3001,http://localhost:5173,http://127.0.0.1:5173

CACHE_STORE=database
QUEUE_CONNECTION=database
```

Regras:

- Sanctum recebe host e porta sem protocolo;
- CORS recebe protocolo, host e porta;
- não inclua `/ou`, `/api` ou outros caminhos;
- use `localhost` nos dois frontends; não alterne com `127.0.0.1` no navegador;
- a API pode continuar em `127.0.0.1:8000`, porque os proxies Vite fazem o encaminhamento.

## 5. Migrar a base

```bash
php artisan migrate
```

Seeders são opcionais e devem ser revistos antes da execução:

```bash
php artisan db:seed
```

Somente num ambiente descartável:

```bash
php artisan migrate:fresh --seed
```

> `migrate:fresh` elimina todas as tabelas. Não o execute numa base que precise preservar.

## 6. Preparar anexos e armazenamento

Linux/macOS:

```bash
mkdir -p public/uploads
chmod -R ug+rwX storage bootstrap/cache public/uploads
php artisan storage:link
```

No Windows/Laragon, confirme permissões de escrita nos mesmos diretórios. `storage:link` é necessário para o disco público Laravel; os controladores atuais também gravam diretamente em `public/uploads`.

## 7. Iniciar

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Mantenha o terminal aberto. A API estará em `http://127.0.0.1:8000`.

## 8. Iniciar o sistema completo

Use três terminais:

```text
Terminal 1: mi-sigmp-api                   → php artisan serve --host=127.0.0.1 --port=8000
Terminal 2: mi-sigmp-ui                    → pnpm dev
Terminal 3: manage-organizational-units   → npm run dev
```

Abra primeiro `http://localhost:3001`, faça login e use o menu de unidades organizacionais para abrir `http://localhost:5173`.

## 9. Validar

```bash
php artisan about
php artisan migrate:status
php artisan route:list --path=api
php artisan route:list --path=sanctum/csrf-cookie
php artisan test
vendor/bin/pint --test
```

Teste a API:

```bash
curl http://127.0.0.1:8000/api/endpointTest
curl -i http://127.0.0.1:8000/sanctum/csrf-cookie
```

O endpoint CSRF deve responder `204 No Content` e criar `XSRF-TOKEN` quando chamado pelo navegador com a configuração correta.

## 10. Problemas locais

### `SQLSTATE` ou ligação recusada

- inicie MySQL;
- confira `DB_HOST`, `DB_PORT`, base e credenciais;
- confirme que a base existe;
- execute `php artisan config:clear` depois de mudar `.env`.

### `401 Unauthenticated`

- faça login no SIGMP UI primeiro;
- use `localhost` nos dois frontends;
- confira `SANCTUM_STATEFUL_DOMAINS`;
- elimine cookies antigos e autentique-se novamente.

### `419 Page Expired`

- confirme `GET /sanctum/csrf-cookie` com resposta `204`;
- confirme cookies `XSRF-TOKEN` e sessão no navegador;
- confira `withCredentials`, `withXSRFToken` e CORS;
- execute `php artisan optimize:clear`.

### `429 Too Many Requests`

Login e recuperação usam `throttle:5,1`: cinco pedidos por minuto por chave de limitação. Aguarde `Retry-After` antes de tentar novamente.

### Upload falha

- crie `public/uploads`;
- confira permissões;
- confira limite de upload do PHP;
- inspecione `storage/logs/laravel.log`.
