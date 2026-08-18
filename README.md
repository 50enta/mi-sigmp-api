# SIGMP API

API do Sistema Integrado de Gestão do Ministério Público (SIGMP), construída com Laravel 11, PHP 8.2, MySQL e Laravel Sanctum.

Esta API serve duas aplicações frontend:

- **SIGMP UI:** `http://localhost:3001` em desenvolvimento;
- **Gestor de Unidades Organizacionais:** `http://localhost:5173` em desenvolvimento.

Em produção, a topologia atualmente prevista é:

- SIGMP UI: `https://5enta.org`;
- Gestor de Unidades Organizacionais: `https://5enta.org/ou`;
- API: `https://5enta.org/sigmp-api`;
- endpoints REST: `https://5enta.org/sigmp-api/api/*`;
- inicialização CSRF: `https://5enta.org/sigmp-api/sanctum/csrf-cookie`;
- ficheiros enviados: `https://5enta.org/sigmp-api/uploads/*`.

## 1. Requisitos

Instale antes de começar:

- PHP **8.2 ou superior**;
- Composer 2;
- MySQL 8 ou MariaDB compatível;
- extensões PHP usuais do Laravel, incluindo `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `session`, `tokenizer` e `xml`;
- Git;
- Node.js e npm apenas se precisar dos recursos Vite existentes no projeto Laravel;
- opcionalmente Laragon no Windows.

Confirme o ambiente:

```bash
php -v
composer --version
php -m
mysql --version
```

## 2. Instalação local

### 2.1. Instalar dependências

Entre na raiz da API e execute:

```bash
composer install
```

### 2.2. Criar o ficheiro de ambiente

No Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

No Linux/macOS:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

Nunca reutilize a `APP_KEY` de desenvolvimento em produção e nunca envie o ficheiro `.env` ao repositório.

### 2.3. Criar e configurar a base de dados

Crie uma base MySQL vazia:

```sql
CREATE DATABASE mi_sigmp
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Configure `.env`:

```env
APP_NAME=SIGMP
APP_ENV=local
APP_KEY=base64:CHAVE_GERADA_PELO_ARTISAN
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mi_sigmp
DB_USERNAME=root
DB_PASSWORD=
```

Se o MySQL estiver noutro servidor, substitua host, porta e credenciais.

### 2.4. Configurar sessão, Sanctum e CORS

Para a topologia local padrão, mantenha:

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

SANCTUM_STATEFUL_DOMAINS=localhost:3001,127.0.0.1:3001,localhost:5173,127.0.0.1:5173
CORS_ALLOWED_ORIGINS=http://localhost:3001,http://127.0.0.1:3001,http://localhost:5173,http://127.0.0.1:5173
```

Regras importantes:

- `SANCTUM_STATEFUL_DOMAINS` recebe host e porta, **sem** `http://` ou `https://`;
- `CORS_ALLOWED_ORIGINS` recebe a origem completa, incluindo protocolo e porta;
- não coloque caminhos como `/ou` ou `/api` nestas listas;
- não misture `localhost` e `127.0.0.1` ao abrir os frontends: cookies são associados ao host;
- os frontends usam `withCredentials` e `withXSRFToken` para enviar sessão e CSRF;
- `/sanctum/csrf-cookie` cria o cookie `XSRF-TOKEN`; ele não realiza o login.

### 2.5. Executar migrações

```bash
php artisan migrate
```

Para carregar os seeders num ambiente novo, depois de rever os dados que serão inseridos:

```bash
php artisan db:seed
```

Ou, apenas quando puder apagar e recriar todos os dados locais:

```bash
php artisan migrate:fresh --seed
```

> `migrate:fresh` elimina todas as tabelas. Nunca execute esse comando numa base de produção com dados reais.

### 2.6. Preparar diretórios graváveis

A aplicação grava logs e cache em `storage/` e `bootstrap/cache/`. Existem também controladores que guardam anexos em `public/uploads/`.

No Linux:

```bash
mkdir -p public/uploads
chmod -R ug+rwX storage bootstrap/cache public/uploads
```

Se utilizar o disco público do Laravel, crie também a ligação simbólica:

```bash
php artisan storage:link
```

No Windows/Laragon, confirme que o utilizador que executa PHP possui permissão de escrita nesses diretórios.

### 2.7. Iniciar a API

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

A API ficará disponível em `http://127.0.0.1:8000`. Mantenha esse terminal aberto.

### 2.8. Validar a instalação

Em outro terminal:

```bash
php artisan about
php artisan migrate:status
php artisan route:list --path=api
php artisan route:list --path=sanctum/csrf-cookie
```

Teste a rota pública:

```bash
curl http://127.0.0.1:8000/api/endpointTest
```

Teste o endpoint CSRF; uma resposta `204 No Content` é esperada:

```bash
curl -i http://127.0.0.1:8000/sanctum/csrf-cookie
```

## 3. Ordem completa para executar o sistema local

Use três terminais separados:

1. API:

   ```bash
   cd mi-sigmp-api
   php artisan serve --host=127.0.0.1 --port=8000
   ```

2. SIGMP UI:

   ```bash
   cd mi-sigmp-ui
   pnpm dev
   ```

3. Gestor de Unidades Organizacionais:

   ```bash
   cd manage-organizational-units
   npm run dev
   ```

Depois abra `http://localhost:3001`, autentique-se e aceda ao gestor pelo menu **Unidades organizacionais**. Não abra o SIGMP UI como `127.0.0.1:3001` se iniciou a sessão em `localhost:3001`.

## 4. Testes e qualidade

Execute os testes automatizados:

```bash
php artisan test
```

Execute apenas os testes de unidades organizacionais:

```bash
php artisan test --filter=Local
```

Verifique o estilo PHP:

```bash
vendor/bin/pint --test
```

Para corrigir automaticamente o estilo:

```bash
vendor/bin/pint
```

## 5. Publicação em produção

### 5.1. Requisitos do servidor

- PHP-FPM 8.2+;
- Composer 2;
- MySQL/MariaDB;
- Nginx ou Apache;
- HTTPS válido;
- um gestor de processos como Supervisor ou systemd se houver filas assíncronas;
- acesso de escrita a `storage/`, `bootstrap/cache/` e `public/uploads/`.

O document root da API deve apontar para o diretório `public/`, nunca para a raiz do repositório. Se a API for publicada sob `/sigmp-api`, o servidor web deve encaminhar esse prefixo para o `public/index.php` do Laravel e preservar corretamente o URI do pedido.

### 5.2. Exemplo de `.env` de produção

Adapte credenciais e serviços externos:

```env
APP_NAME=SIGMP
APP_ENV=production
APP_KEY=base64:CHAVE_UNICA_DE_PRODUCAO
APP_DEBUG=false
APP_URL=https://5enta.org/sigmp-api

APP_LOCALE=pt
APP_FALLBACK_LOCALE=pt

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mi_sigmp
DB_USERNAME=sigmp
DB_PASSWORD=UMA_SENHA_FORTE

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=.5enta.org
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

SANCTUM_STATEFUL_DOMAINS=5enta.org,www.5enta.org
CORS_ALLOWED_ORIGINS=https://5enta.org,https://www.5enta.org

CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log
```

Se não utilizar `www`, remova-o das listas. Se os frontends estiverem noutro subdomínio, adicione a origem exata e confirme que todos partilham o mesmo domínio de topo exigido pela autenticação SPA do Sanctum.

### 5.3. Instalar uma nova versão

No diretório da API:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Crie `public/uploads` e aplique permissões adequadas ao utilizador do PHP-FPM. Não use permissões globais `777`.

Quando trocar uma configuração do `.env`, limpe e recrie o cache:

```bash
php artisan optimize:clear
php artisan optimize
```

### 5.4. Deployment com indisponibilidade mínima

Para uma atualização simples:

```bash
php artisan down --retry=60
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize
php artisan up
```

Certifique-se de que possui backup da base de dados e dos anexos antes de migrar. Em deployments com releases e symlink, mantenha `.env`, `storage/` e `public/uploads/` em armazenamento persistente partilhado.

### 5.5. Filas

Como `QUEUE_CONNECTION=database`, mantenha um worker se existirem jobs assíncronos:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

O worker deve ser supervisionado por Supervisor/systemd. Depois de cada deployment:

```bash
php artisan queue:restart
```

## 6. Autenticação entre as aplicações

O fluxo recomendado não coloca tokens no URL:

1. SIGMP UI chama `GET /sanctum/csrf-cookie`;
2. o navegador recebe `XSRF-TOKEN`;
3. SIGMP UI envia `POST /api/login`;
4. Laravel cria a sessão;
5. SIGMP UI abre o gestor de unidades;
6. o gestor envia os cookies com `withCredentials: true`;
7. as rotas protegidas usam `auth:sanctum`.

Em produção, `/`, `/ou` e `/sigmp-api` estão no mesmo host, facilitando a partilha segura da sessão. Evite transportar bearer tokens em query strings, pois URLs podem aparecer em histórico, logs e cabeçalhos `Referer`.

## 7. Resolução de problemas

### Resposta `401 Unauthenticated`

- confirme que o login foi feito no mesmo host usado pelo gestor;
- confirme `SANCTUM_STATEFUL_DOMAINS` e `SESSION_DOMAIN`;
- confirme que Axios utiliza `withCredentials: true`;
- limpe os cookies antigos e autentique-se novamente;
- execute `php artisan optimize:clear` depois de mudar `.env`.

### Resposta `419 Page Expired`

- confirme que `/sanctum/csrf-cookie` responde `204`;
- confirme `withXSRFToken: true`;
- confirme que `XSRF-TOKEN` e o cookie de sessão aparecem no navegador;
- confirme `CORS_ALLOWED_ORIGINS` e `supports_credentials=true`;
- em produção, confirme HTTPS e `SESSION_SECURE_COOKIE=true`.

### Erro CORS

- origens CORS devem incluir protocolo e porta;
- não inclua caminhos como `/ou`;
- não use `*` juntamente com credenciais;
- confirme que o proxy ou servidor web não remove os cabeçalhos CORS.

### Erro `429 Too Many Requests`

As rotas de login e recuperação usam `throttle:5,1`: são permitidos até cinco pedidos por minuto para a chave de limitação correspondente. Aguarde o período indicado no cabeçalho `Retry-After`.

### Upload não abre

- confirme que `public/uploads` existe e é gravável;
- confirme que o servidor publica `/sigmp-api/uploads/*`;
- confirme que `VITE_APP_FILE_URL` dos frontends aponta para esse endereço.

### Configuração antiga continua ativa

```bash
php artisan optimize:clear
php artisan config:clear
```

## 8. Comandos úteis

```bash
php artisan about
php artisan route:list
php artisan migrate:status
php artisan optimize:clear
php artisan cache:clear
php artisan queue:failed
php artisan queue:retry all
php artisan test
```
