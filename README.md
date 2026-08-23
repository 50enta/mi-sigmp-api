# SIGMP API

API Laravel 11 do Sistema Integrado de Gestão do Ministério Público.

## Documentação

Escolha o guia correspondente ao trabalho que pretende realizar:

1. [Requisitos](docs/requirements.md) — software, extensões PHP, serviços e arquitetura esperada.
2. [Instalação local](docs/instalacao-local.md) — base de dados, `.env`, migrações, arranque, testes e problemas de desenvolvimento.
3. [Instalação no servidor](docs/instalacao-servidor.md) — configuração de produção, HTTPS, Sanctum, permissões, deployment, filas e diagnóstico.

## Endereços de referência

| Ambiente | API |
| --- | --- |
| Local | `http://127.0.0.1:8000` |
| Produção | `https://5enta.org/sigmp-api` |

Os endpoints REST usam `/api`; o endpoint CSRF é `/sanctum/csrf-cookie` e os anexos atuais são publicados em `/uploads`.
