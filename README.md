# Flowkly — Portal de Solicitações Internas

Portal interno para abertura e acompanhamento de solicitações de TI. Usuários
abrem solicitações (hardware, software, rede, acesso, etc.) e a equipe de TI
gerencia o atendimento em um quadro Kanban, com dashboard, histórico de status
e notificações por e-mail.

> Documentação complementar: [`docs/memorial-tecnico.pdf`](docs/memorial-tecnico.pdf)
> (memorial técnico detalhado), [`docs/database/schema.sql`](docs/database/schema.sql)
> e [`docs/database/dicionario-dados.md`](docs/database/dicionario-dados.md)
> (modelagem do banco), e [`docs/credenciais-avaliacao.md`](docs/credenciais-avaliacao.md)
> (credenciais e roteiro de avaliação).

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Frontend | Vue 3 (`<script setup>`, TypeScript), Inertia.js 3 |
| Estilo | Tailwind CSS v4 |
| Banco de dados | SQLite (padrão de desenvolvimento; compatível com MySQL/PostgreSQL) |
| Testes | Pest 4 (PHPUnit) |
| E-mail | Laravel Mail (Markdown mailables), driver `log` por padrão |
| Build | Vite (via `vite-plus`), Laravel Wayfinder (rotas tipadas para o frontend) |

## Perfis de acesso

| Perfil | Pode acessar |
|---|---|
| **Usuário** | Login próprio, Dashboard **não**, "Minhas solicitações" (criar, listar, filtrar, ver detalhes, editar/excluir enquanto "Aberto"), "Nova solicitação", Configurações (trocar senha) |
| **TI** | Login próprio, Dashboard (métricas e solicitações vinculadas a si), Kanban (todas as solicitações, qualquer uma pode ser editada/excluída/ter status alterado), "Solicitações" (aprovar/recusar pedidos de acesso), "Usuários" (gerar nova senha para qualquer conta), "Nova solicitação" (TI também pode abrir solicitações, sempre atribuídas a outra pessoa de TI), Configurações |

Não existe cadastro público direto: uma pessoa pede acesso pelo botão **"Solicitar
acesso"** na tela de login (nome, e-mail, motivo); a TI aprova ou recusa em
**Solicitações**. Ao aprovar, o sistema cria a conta (perfil "Usuário"), gera
uma senha aleatória e envia por e-mail.

## Funcionalidades

- **Autenticação** com dois portais de login (Usuário / TI), sessão via
  cookies do Laravel, logout, e guarda de rota por perfil (middleware
  `role:user|ti`, ver [`app/Http/Middleware/EnsureUserHasRole.php`](app/Http/Middleware/EnsureUserHasRole.php)).
- **Solicitações (tickets)**: título, descrição, categoria, até 5 imagens
  anexadas, atribuição obrigatória a uma pessoa de TI. Data e solicitante são
  preenchidos automaticamente; status inicial é sempre "Aberto". Enquanto
  "Aberto", o próprio solicitante pode editar ou excluir (regra aplicada em
  [`app/Policies/TicketPolicy.php`](app/Policies/TicketPolicy.php); a TI pode
  editar/excluir/mudar status a qualquer momento).
- **Listagem** ("Minhas solicitações" e Kanban): código, título, categoria,
  solicitante, data e status, com filtros por período, categoria, status e
  título.
- **Fluxo de status**: Aberto → Em Atendimento → Concluído, gerenciado pela TI
  no Kanban; cada mudança é registrada em `ticket_status_histories` (quem
  mudou e quando) e exibida como histórico na tela de detalhes.
- **Dashboard (TI)**: total de solicitações, abertas, em atendimento,
  concluídas, distribuição por categoria (gráfico) e as solicitações mais
  recentes vinculadas à pessoa logada.
- **Detalhes da solicitação**: modal com todas as informações (categoria,
  solicitante, data de abertura, descrição, anexos, histórico de status) —
  disponível tanto em "Minhas solicitações" quanto no Kanban.
- **E-mails transacionais**: a pessoa de TI designada recebe um e-mail ao ser
  vinculada a uma nova solicitação; quem teve o acesso aprovado recebe a senha
  gerada por e-mail.

## Como rodar localmente

Pré-requisitos: PHP 8.3+, Composer, Node 20+, npm.

```bash
git clone <url-do-repositorio> flowkly
cd flowkly

composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/flowkly.sqlite   # ou ajuste DB_DATABASE no .env para outro caminho
php artisan migrate --seed

npm run dev          # terminal 1 — Vite (assets)
php artisan serve    # terminal 2 — servidor PHP
```

Acesse `http://127.0.0.1:8000` (ou a porta indicada por `php artisan serve`).
Credenciais de teste em [`docs/credenciais-avaliacao.md`](docs/credenciais-avaliacao.md).

### Rodando os testes

```bash
php artisan test
```

### Verificação de tipos e lint do frontend

```bash
npx vue-tsc --noEmit
npx vp check          # lint + formatação (Vue/TS)
```

## Deploy / execução em produção

1. Defina as variáveis de ambiente de produção (`APP_ENV=production`,
   `APP_DEBUG=false`, `APP_URL`, banco de dados, e um `MAIL_MAILER` real —
   por padrão o projeto usa `log`, que apenas grava os e-mails em
   `storage/logs/laravel.log` em vez de enviá-los).
2. Instale as dependências sem pacotes de desenvolvimento e compile os
   assets:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
3. Gere a chave da aplicação (se ainda não existir) e rode as migrações:
   ```bash
   php artisan key:generate --force
   php artisan migrate --force
   php artisan storage:link
   ```
4. Otimize a aplicação:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. Sirva `public/` com seu servidor web (Nginx/Apache + PHP-FPM) apontando o
   document root para a pasta `public/`, ou use `php artisan serve` apenas
   para demonstrações.

## Estrutura do projeto (resumo)

```
app/
  Enums/          UserRole, TicketType, TicketStatus, AccessRequestStatus
  Http/Controllers  TicketController, KanbanController, DashboardController,
                     AccessRequestController, UserController, SettingsController, ...
  Http/Middleware  EnsureUserHasRole (guarda de rota por perfil)
  Mail/           TicketAssigned, AccessRequestApproved
  Models/         User, Ticket, TicketAttachment, TicketStatusHistory, AccessRequest
  Policies/       TicketPolicy (regra "dono pode editar/excluir enquanto Aberto")
database/
  migrations/     histórico de alterações do schema
  seeders/        DatabaseSeeder (dados de demonstração)
  schema/         dump gerado por `php artisan schema:dump`
docs/
  memorial-tecnico.pdf       memorial técnico detalhado
  database/schema.sql        script SQL comentado
  database/dicionario-dados.md  dicionário de dados
  credenciais-avaliacao.md   credenciais de teste e roteiro de avaliação
resources/js/
  Components/     LateralBar (menu lateral), Modal (modal reutilizável)
  pages/          telas Inertia (Dashboard, Kanban, Requests, AccessRequests, Users, Settings, Auth)
  routes/, actions/  rotas tipadas geradas pelo Laravel Wayfinder
routes/
  web.php, Auth/login.php, app.php   definição das rotas e middlewares
tests/Feature/    testes de autenticação, solicitações, dashboard, etc. (Pest)
```

## Decisões técnicas (resumo)

O racional completo de cada decisão está no
[memorial técnico](docs/memorial-tecnico.pdf). Resumo rápido:

- **Inertia.js em vez de uma API REST separada**: o backend Laravel serve
  diretamente os componentes Vue como "páginas", trocando dados por
  requisições HTTP/JSON nos bastidores — sem precisar duplicar rotas em uma
  API JSON + um cliente SPA separado. Os `Controllers` em `app/Http/Controllers`
  cumprem o papel de camada de API (validam, aplicam regras de negócio e
  devolvem dados), e o frontend os consome via `Inertia::render`.
- **Papéis de acesso via enum (`UserRole`) + policy**, não um sistema de
  permissões genérico — escopo do teste não pedia granularidade além de
  usuário/TI.
- **SQLite em desenvolvimento** por não exigir serviço externo; o schema é
  padrão (chaves estrangeiras, enums como `string`) e migra sem alterações
  para MySQL/PostgreSQL.
