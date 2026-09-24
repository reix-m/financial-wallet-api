# Financial Wallet API

API para uma carteira financeira: cadastro, autenticação e operações de depósito,
transferência e reversão de transações. Construída em **Laravel 13 / PHP 8.4**, com
autenticação por token (Sanctum), **PostgreSQL** e **Redis**.

## Stack

- PHP 8.4, Laravel 13
- Laravel Sanctum (tokens de API com abilities por endpoint)
- PostgreSQL 16, Redis 7
- Pest (testes), Larastan + Pint (qualidade), Scribe (documentação da API)
- Docker / Docker Compose (imagem multi-stage)

## Subindo o ambiente

Com Docker, a partir de `financial-wallet-api/`:

```bash
docker compose up -d                 # api + postgres + redis + mailpit
docker compose exec app php artisan migrate --seed
```

Para subir também o front (profile `web`; requer o repositório
`financial-wallet-web` como pasta irmã):

```bash
docker compose --profile web up -d   # + front em http://localhost:3000
```

Sem Docker:

```bash
composer setup       # dependências, .env, key e migrate
composer dev         # servidor de desenvolvimento
```

Serviços:

| Serviço        | URL                              |
| -------------- | -------------------------------- |
| API            | http://localhost:8000            |
| Documentação   | http://localhost:8000/docs       |
| Front (Vue)    | http://localhost:3000            |
| Mailpit (e-mail)| http://localhost:8025           |

## Contas de demonstração

O `DatabaseSeeder` cria dois usuários **verificados**, com carteira e histórico:

| E-mail              | Senha      |
| ------------------- | ---------- |
| alice@example.com   | password   |
| bob@example.com     | password   |

O seed gera depósitos, uma transferência de Alice para Bob e um estorno. Novos
cadastros nascem **não verificados**; o link de verificação é enviado para o
Mailpit em http://localhost:8025.

## Endpoints (`/api/v1`)

| Método | Rota                                  | Descrição                          |
| ------ | ------------------------------------- | ---------------------------------- |
| POST   | `/auth/register`                      | Cria conta e retorna token         |
| POST   | `/auth/login`                         | Autentica e retorna token          |
| GET    | `/auth/me`                            | Usuário autenticado                |
| POST   | `/wallets`                            | Cria a carteira                    |
| GET    | `/wallets/my`                         | Saldo e código da conta            |
| POST   | `/wallets/deposit`                    | Depósito                           |
| POST   | `/wallets/transfer`                   | Transferência para outro código    |
| GET    | `/wallets/transactions`               | Extrato (paginado)                 |
| POST   | `/wallets/transactions/{id}/revert`   | Reverte uma transação              |

Detalhes completos em http://localhost:8000/docs (Scribe) ou via `php artisan scribe:generate`.

## Regras de domínio

- Dinheiro é **inteiro em centavos** (`Money::fromCents()`); `10000` = `R$ 100,00`.
- Saldo é validado antes da transferência; depósito sempre soma.
- Mutações de saldo rodam em `DB::transaction()` com `lockForUpdate()` na carteira.
- Reversão gera uma transação de estorno e atualiza o status da original.
- E-mails de carteira exigem verificação (`verified`); tokens carregam abilities por endpoint.

## Arquitetura

- `app/Http/Controllers/Api/V1` — controllers finos
- `app/Actions/<Domain>/<Name>` — um caso de uso por pasta (Input/Result DTOs)
- `app/Domain/Wallet` — entidades, ValueObjects (`Money`), exceções e serviços
- `app/Models` — persistência (`toDomainEntity` / `fromDomainEntity`)
- `app/Support/AuditLog` — eventos de auditoria

## Qualidade

```bash
composer lint    # Pint (test)
composer stan    # Larastan
composer test    # Pest
```
