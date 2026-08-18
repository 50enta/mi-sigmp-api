# Requisitos — SIGMP API

[Voltar ao índice](../README.md)

## Software obrigatório

- PHP **8.2 ou superior**;
- Composer 2;
- MySQL 8 ou MariaDB compatível;
- Git;
- Nginx, Apache ou Laragon para servir a aplicação;
- HTTPS válido no ambiente de produção.

Node.js e npm são necessários apenas se forem utilizados os recursos Vite presentes no projeto Laravel. A API pode ser executada normalmente com Composer e Artisan.

## Extensões PHP

Confirme pelo menos:

- `ctype`;
- `curl`;
- `dom` e `xml`;
- `fileinfo`;
- `filter`;
- `mbstring`;
- `openssl`;
- `pdo` e `pdo_mysql`;
- `session`;
- `tokenizer`.

Verificação:

```bash
php -v
php -m
composer --version
mysql --version
```

## Serviços e diretórios

A aplicação precisa de:

- uma base de dados MySQL;
- escrita em `storage/`;
- escrita em `bootstrap/cache/`;
- escrita em `public/uploads/` para os anexos atuais;
- tabela de sessões, porque `SESSION_DRIVER=database`;
- tabelas de cache e filas quando `CACHE_STORE=database` e `QUEUE_CONNECTION=database`.

As tabelas necessárias são criadas pelas migrações do projeto.

## Dependências principais

- Laravel 11;
- Laravel Sanctum 4;
- Pest/PHPUnit para testes;
- Laravel Pint para estilo PHP.

As versões exatas estão em `composer.json` e `composer.lock`. Em instalações reproduzíveis, não elimine nem regenere `composer.lock` sem intenção explícita de atualizar dependências.

## Arquitetura esperada

### Desenvolvimento

| Componente | Endereço |
| --- | --- |
| API | `http://127.0.0.1:8000` |
| SIGMP UI | `http://localhost:3001` |
| Org Units Manager | `http://localhost:5173` |

### Produção

| Componente | Endereço |
| --- | --- |
| SIGMP UI | `https://5enta.org` |
| Org Units Manager | `https://5enta.org/ou` |
| API | `https://5enta.org/sigmp-api` |

O document root da API deve apontar exclusivamente para `public/`. A raiz do repositório, `.env`, `vendor/` e `storage/` não podem ser expostos diretamente pelo servidor web.

## Conhecimentos necessários para operação

Antes de publicar, o operador deve saber:

- criar utilizadores e bases MySQL com privilégios mínimos;
- configurar PHP-FPM ou Apache PHP;
- configurar TLS/HTTPS;
- gerir permissões sem usar `777`;
- executar migrações Laravel com backup;
- reiniciar workers de fila;
- inspecionar logs Laravel e logs do servidor web.
