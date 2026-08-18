# Instalação no servidor — SIGMP API

[Voltar ao índice](../README.md) · [Ver requisitos](requirements.md)

## 1. Preparar infraestrutura

Providencie PHP-FPM 8.2+, Composer 2, MySQL/MariaDB, Nginx ou Apache e certificado HTTPS válido. Crie um utilizador de base de dados exclusivo, com acesso apenas à base SIGMP.

O servidor web deve apontar para `public/`. Quando a API é publicada em `/sigmp-api`, o proxy deve encaminhar esse prefixo para o `public/index.php` do Laravel e também servir `/sigmp-api/uploads/*` a partir de `public/uploads/`.

## 2. Instalar o código

No diretório de release:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
```

Mantenha fora do release ou em área persistente:

- `.env`;
- `storage/`;
- `public/uploads/`;
- backups e segredos.

## 3. Configurar `.env`

Exemplo para a topologia atual:

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

Remova `www` se esse host não for utilizado. Nunca coloque caminhos como `/ou` em domínios Sanctum ou origens CORS.

Gere `APP_KEY` uma única vez para a instalação. Não troque a chave numa atualização normal: isso invalida dados criptografados e sessões.

## 4. Permissões

```bash
mkdir -p public/uploads
chmod -R ug+rwX storage bootstrap/cache public/uploads
```

Defina proprietário e grupo conforme o utilizador do PHP-FPM. Não use `chmod -R 777`.

Se utilizar o disco público:

```bash
php artisan storage:link
```

## 5. Base de dados e otimização

Faça backup antes de migrar:

```bash
php artisan down --retry=60
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan up
```

Não execute `migrate:fresh` ou seeders em produção sem procedimento explícito de recuperação.

## 6. Servidor web

Requisitos de encaminhamento:

- `/sigmp-api/api/*` → Laravel;
- `/sigmp-api/sanctum/csrf-cookie` → Laravel;
- `/sigmp-api/uploads/*` → ficheiros em `public/uploads/`;
- pedidos desconhecidos da API → `public/index.php`;
- `.env`, logs e diretórios internos → nunca públicos.

Como a configuração exata de `alias`, `fastcgi_param SCRIPT_FILENAME` e remoção do prefixo depende da localização real do release, valide a configuração antes de recarregar:

```bash
nginx -t
sudo systemctl reload nginx
```

Em Apache, ative `mod_rewrite`, permita o `.htaccess` de `public/` ou replique as regras no VirtualHost.

## 7. Filas

Quando houver jobs assíncronos:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Execute o worker por Supervisor ou systemd. Depois de cada deployment:

```bash
php artisan queue:restart
```

## 8. Processo de atualização

1. crie backup da base e de `public/uploads`;
2. publique o novo código num diretório de release;
3. execute `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`;
4. ligue `.env`, `storage/` e uploads persistentes;
5. execute `php artisan migrate --force`;
6. execute `php artisan optimize`;
7. troque o symlink do release de forma atómica;
8. execute `php artisan queue:restart`;
9. verifique logs e endpoints;
10. mantenha o release anterior disponível para rollback de código.

Migrações destrutivas exigem estratégia própria de rollback; reverter apenas o código pode não reverter o schema.

## 9. Validação pós-deployment

```bash
php artisan about
php artisan migrate:status
php artisan route:list --path=sanctum/csrf-cookie
```

Confirme:

- `APP_DEBUG=false`;
- HTTPS sem conteúdo misto;
- `/sigmp-api/sanctum/csrf-cookie` responde `204`;
- login, logout e `/api/user` funcionam;
- Org Units carrega raiz e filhos;
- criação, edição e eliminação funcionam;
- anexos abrem em `/sigmp-api/uploads`;
- nenhum `.env` pode ser descarregado;
- logs não contêm erros novos.

## 10. Problemas no servidor

### `401`

Confira `SESSION_DOMAIN`, `SANCTUM_STATEFUL_DOMAINS`, cookies `Secure` e o cabeçalho `Origin` recebido pela API.

### `419`

Confira o endpoint CSRF, `CORS_ALLOWED_ORIGINS`, `supports_credentials=true`, HTTPS e se o proxy preserva os cookies.

### `500`

```bash
tail -f storage/logs/laravel.log
```

Confira também logs do PHP-FPM e Nginx/Apache. Mantenha `APP_DEBUG=false` enquanto consulta os logs no servidor.

### Alteração de `.env` não aplicada

```bash
php artisan optimize:clear
php artisan optimize
```

### Worker usa código antigo

```bash
php artisan queue:restart
```

### Upload não é servido

Confira mapeamento de `/sigmp-api/uploads`, permissões e persistência do diretório entre releases.
