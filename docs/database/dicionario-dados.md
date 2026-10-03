# Dicionário de dados — Flowkly

Banco de desenvolvimento: **SQLite** (`database/flowkly.sqlite`). O schema é
portável para MySQL/PostgreSQL sem alterações estruturais (ver
[`schema.sql`](schema.sql)). Os tipos abaixo refletem os `casts`/migrations em
`app/Models` e `database/migrations`.

Esta é a modelagem **de domínio da aplicação**. O projeto também possui
tabelas de infraestrutura geradas pelo próprio framework Laravel
(`sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`,
`password_reset_tokens`, `migrations`) — não fazem parte da regra de negócio
e por isso não são detalhadas aqui; seu propósito é padrão do framework
(sessão de login, fila de jobs, cache, controle de migrations).

## Visão geral das tabelas

| Tabela | Descrição |
|---|---|
| `users` | Contas de acesso (perfil Usuário ou TI) |
| `access_requests` | Pedidos de acesso ao portal, antes de virarem uma conta |
| `tickets` | As solicitações abertas pelos usuários |
| `ticket_attachments` | Imagens anexadas a uma solicitação |
| `ticket_status_histories` | Histórico de mudanças de status de uma solicitação |

## Relacionamentos

- `tickets.user_id` → `users.id` (quem abriu a solicitação) — **1:N** (um
  usuário pode ter várias solicitações)
- `tickets.assigned_to` → `users.id` (pessoa de TI responsável) — **1:N**
- `ticket_attachments.ticket_id` → `tickets.id` — **1:N** (uma solicitação
  pode ter várias imagens)
- `ticket_status_histories.ticket_id` → `tickets.id` — **1:N** (uma
  solicitação acumula um histórico de mudanças)
- `ticket_status_histories.changed_by` → `users.id` — quem realizou a mudança
- `access_requests.reviewed_by` → `users.id` — qual pessoa de TI
  aprovou/recusou o pedido

```
users 1───N tickets (user_id)        "solicitante"
users 1───N tickets (assigned_to)    "responsável de TI"
tickets 1───N ticket_attachments
tickets 1───N ticket_status_histories
users 1───N ticket_status_histories (changed_by)
users 1───N access_requests (reviewed_by)
```

---

## `users`

Contas de acesso ao portal. Senhas são sempre armazenadas com hash (bcrypt,
cast `hashed` no model `User`).

| Coluna | Tipo | Nulo | Padrão | Descrição |
|---|---|---|---|---|
| `id` | INTEGER (PK, autoincrement) | não | — | Identificador único |
| `name` | VARCHAR(255) | não | — | Nome completo |
| `email` | VARCHAR(255) | não | — | E-mail de login. **Único** (`users_email_unique`) |
| `role` | VARCHAR(20) | não | `user` | Perfil de acesso: `user` ou `ti` |
| `email_verified_at` | DATETIME | sim | `null` | Reservado (verificação de e-mail não está habilitada no fluxo atual) |
| `password` | VARCHAR(255) | não | — | Hash da senha |
| `remember_token` | VARCHAR(100) | sim | `null` | Token do "lembrar-me" do Laravel |
| `created_at` / `updated_at` | DATETIME | sim | — | Timestamps padrão |

## `access_requests`

Pedido de acesso enviado pelo formulário "Solicitar acesso" na tela de login,
antes de existir uma conta.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|---|---|---|---|---|
| `id` | INTEGER (PK) | não | — | Identificador único |
| `name` | VARCHAR(255) | não | — | Nome informado pelo solicitante |
| `email` | VARCHAR(255) | não | — | E-mail informado (vira o e-mail de login se aprovado) |
| `reason` | TEXT | não | — | Motivo do pedido de acesso |
| `status` | VARCHAR(20) | não | `pending` | `pending`, `accepted` ou `declined` |
| `reviewed_by` | INTEGER (FK → `users.id`) | sim | `null` | Pessoa de TI que decidiu |
| `reviewed_at` | DATETIME | sim | `null` | Quando foi decidido |
| `created_at` / `updated_at` | DATETIME | sim | — | Timestamps padrão |

Regra de negócio: um e-mail não pode ter duas solicitações `pending` ao mesmo
tempo, nem ser solicitado se já existe uma conta com aquele e-mail
(`AccessRequestController::store`).

## `tickets`

A solicitação em si — a tabela central do domínio.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|---|---|---|---|---|
| `id` | INTEGER (PK) | não | — | **Código da solicitação** (exibido como `#id` na interface) |
| `title` | VARCHAR(255) | não | — | Título |
| `description` | TEXT | não | — | Descrição detalhada |
| `type` | VARCHAR(20) | não | — | Categoria: `hardware`, `software`, `rede`, `acesso` ou `outro` |
| `status` | VARCHAR(20) | não | `aberta` | `aberta`, `em_andamento` ou `concluida` |
| `user_id` | INTEGER (FK → `users.id`) | não | — | Quem abriu (preenchido automaticamente com o usuário autenticado) |
| `assigned_to` | INTEGER (FK → `users.id`) | não | — | Pessoa de TI responsável (escolhida no formulário; sempre `role = 'ti'`) |
| `created_at` | DATETIME | sim | — | **Data da solicitação** (preenchida automaticamente) |
| `updated_at` | DATETIME | sim | — | Última alteração |

Regras de negócio aplicadas em `app/Policies/TicketPolicy.php` e
`TicketController`:
- O status inicial é sempre `aberta`.
- Enquanto `status = 'aberta'`, **o próprio solicitante** pode editar
  (`title`, `description`, `type`) ou excluir a solicitação.
- Uma vez que o status muda para `em_andamento` ou `concluida`, apenas a TI
  pode editar/excluir ou mudar o status novamente (a transição de status é
  uma ação exclusiva da TI, feita pelo Kanban).

## `ticket_attachments`

Imagens anexadas no momento da criação da solicitação (até 5 arquivos, 5MB
cada, tipos de imagem).

| Coluna | Tipo | Nulo | Padrão | Descrição |
|---|---|---|---|---|
| `id` | INTEGER (PK) | não | — | Identificador único |
| `ticket_id` | INTEGER (FK → `tickets.id`) | não | — | Solicitação dona do anexo |
| `path` | VARCHAR(255) | não | — | Caminho relativo no disco `public` (`storage/app/public/...`) |
| `original_name` | VARCHAR(255) | não | — | Nome original do arquivo enviado |
| `created_at` / `updated_at` | DATETIME | sim | — | Timestamps padrão |

A URL pública de um anexo é `/storage/{path}` (ver accessor `url` no model
`TicketAttachment`), servida pelo link simbólico criado por
`php artisan storage:link`.

## `ticket_status_histories`

Auditoria de cada mudança de status — é o que alimenta a seção "Histórico" na
tela de detalhes da solicitação.

| Coluna | Tipo | Nulo | Padrão | Descrição |
|---|---|---|---|---|
| `id` | INTEGER (PK) | não | — | Identificador único |
| `ticket_id` | INTEGER (FK → `tickets.id`) | não | — | Solicitação relacionada |
| `from_status` | VARCHAR(20) | sim | `null` | Status anterior; `null` na primeira linha (criação da solicitação) |
| `to_status` | VARCHAR(20) | não | — | Novo status |
| `changed_by` | INTEGER (FK → `users.id`) | sim | `null` | Quem fez a mudança |
| `created_at` / `updated_at` | DATETIME | sim | — | Quando a mudança ocorreu |

Uma linha é criada automaticamente:
1. Na criação da solicitação (`from_status = null`, `to_status = 'aberta'`).
2. A cada chamada de `TicketController::updateStatus` (exclusiva da TI).

---

## Enums do domínio (`app/Enums`)

Os valores abaixo são armazenados como `VARCHAR` no banco e convertidos para
enum nativo do PHP pelos `casts()` dos models (`UserRole`, `TicketType`,
`TicketStatus`, `AccessRequestStatus`).

| Enum | Valores | Usado em |
|---|---|---|
| `UserRole` | `user`, `ti` | `users.role` |
| `TicketType` | `hardware`, `software`, `rede`, `acesso`, `outro` | `tickets.type` |
| `TicketStatus` | `aberta`, `em_andamento`, `concluida` | `tickets.status`, `ticket_status_histories.from_status`/`to_status` |
| `AccessRequestStatus` | `pending`, `accepted`, `declined` | `access_requests.status` |
