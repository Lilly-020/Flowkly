# Credenciais e roteiro de avaliação

Este documento reúne as credenciais de teste e um roteiro passo a passo para
validar cada critério do projeto. As contas abaixo são criadas por
`database/seeders/DatabaseSeeder.php` ao rodar `php artisan migrate --seed`
(ou `migrate:fresh --seed`) — portanto reproduzíveis em qualquer ambiente
limpo.

## Credenciais

| Perfil | E-mail | Senha | Observação |
|---|---|---|---|
| TI | `lillycardoso02@gmail.com` | `password` | Conta principal de TI, com solicitações vinculadas (usada no roteiro do Dashboard) |
| TI | `ti@flowkly.test` | `password` | Segunda conta de TI — útil para validar que "várias contas de TI" funcionam e para testar o fluxo de atribuição |
| Usuário | `usuario@flowkly.test` | `password` | Conta de usuário comum, já com solicitações criadas em diferentes status |

> A tela de login tem dois portais (Usuário / TI). Use o e-mail/senha acima no
> portal correspondente ao perfil da conta — o backend recusa o login se a
> conta não pertencer ao portal escolhido (ver `LoginController::login`).

O seeder também cria **um pedido de acesso pendente** (`novo.colaborador@flowkly.test`,
status `pending`) para testar a aprovação/recusa sem precisar preencher o
formulário manualmente.

## Roteiro de avaliação

### 1. Autenticação
1. Acesse `/login` → escolha "Usuário" → entre com `usuario@flowkly.test` / `password`.
2. Confirme que o menu lateral mostra apenas "Minhas solicitações", "Nova
   solicitação" e "Configurações" (sem Dashboard/Kanban/Usuários).
3. Clique em "Sair" (logout) e repita o login escolhendo "TI" com
   `lillycardoso02@gmail.com` / `password` — confirme que agora aparecem
   Dashboard, Solicitações, Kanban, Usuários e Configurações (sem "Minhas
   solicitações"/"Nova solicitação" exclusivas de usuário — "Nova solicitação"
   também aparece para TI, propositalmente).
4. Tente abrir `http://.../dashboard` logado como usuário comum: deve
   retornar **403** (guarda de rota por perfil).

### 2. Criar solicitação
1. Logado como `usuario@flowkly.test`, vá em "Nova solicitação".
2. Preencha título, descrição, categoria, anexe uma imagem (opcional) e
   escolha uma pessoa de TI em "Encaminhar para".
3. Envie — confirme o redirecionamento para "Minhas solicitações" com a nova
   linha, status "Aberto", e que a pessoa de TI escolhida recebeu um e-mail
   (ver `storage/logs/laravel.log`, já que `MAIL_MAILER=log` por padrão).
4. Clique na linha criada → no modal de detalhes, com o status ainda
   "Aberto", confirme os botões **Editar** e **Excluir** (ícone de lixeira).
   Edite o título e salve; confirme a alteração na lista.

### 3. Listagem e filtros
1. Em "Minhas solicitações", confirme as colunas: Código, Título, Categoria,
   Solicitante, Data, Status.
2. Use os filtros (título, categoria, status, período) e confirme que a
   lista é atualizada de acordo.

### 4. Fluxo de status / Kanban (TI)
1. Logado como TI, abra "Kanban" — confirme as 3 colunas (Aberto / Em
   Atendimento / Concluído) com as solicitações distribuídas por status.
2. Clique em um cartão → no modal, use "Alterar Status" para mover entre
   Aberto → Em Atendimento → Concluído. Confirme que o "Histórico" no modal
   ganha uma nova entrada a cada mudança.
3. Volte para a conta de usuário (`usuario@flowkly.test`) e confirme que a
   solicitação alterada **não pode mais** ser editada/excluída pelo usuário
   (o modal de detalhes mostra apenas o aviso, sem os botões).

### 5. Dashboard (TI)
1. Logado como `lillycardoso02@gmail.com`, abra "Dashboard".
2. Confirme os 4 indicadores (Total, Abertas, Em atendimento, Concluídas),
   a lista "Minhas solicitações" (as mais recentes vinculadas a essa conta de
   TI) e o gráfico "Solicitações por categoria" com percentuais.

### 6. Detalhes
- Já exercitado nos passos 2 e 4 — tanto em "Minhas solicitações" quanto no
  Kanban, clicar em uma solicitação abre um modal com todas as informações
  (categoria, solicitante, data de abertura, descrição, anexos e histórico).

### 7. Pedido de acesso (fluxo completo de autoatendimento)
1. Faça logout e, na tela de login, clique em "Solicitar acesso" (ou acesse
   `/solicitar-acesso`, que redireciona para o login com o modal já aberto).
2. Preencha o formulário e envie.
3. Logado como TI, vá em "Solicitações" → encontre o pedido pendente
   (inclui o de exemplo `novo.colaborador@flowkly.test` criado pelo seeder) →
   clique em **Aceitar**.
4. Confirme em `storage/logs/laravel.log` que um e-mail com a senha gerada
   foi "enviado" para o e-mail do pedido, e que a conta aparece em
   "Usuários" com o perfil "Usuário".
5. Em "Usuários", teste **"Gerar nova senha"** para qualquer conta e confirme
   que a nova senha aparece em um modal (para o TI repassar manualmente).

### 8. Validações e tratamento de erros (exemplos rápidos)
- Tentar abrir uma solicitação sem preencher campos obrigatórios → mensagens
  de validação por campo (Laravel Form Request + exibidas pelo Inertia).
- Tentar logar com senha errada → mensagem genérica "E-mail ou senha
  inválidos." (sem revelar se o e-mail existe).
- Tentar logar com credenciais corretas no portal errado (ex.: conta de TI
  pelo portal "Usuário") → acesso recusado.
- Acessar uma rota fora do seu perfil (ex.: usuário tentando `PATCH
  /tickets/{id}/status`) → **403 Forbidden**.
- Acessar uma solicitação inexistente (`/tickets/999999`) → **404** (route
  model binding padrão do Laravel).

## Testes automatizados

```bash
php artisan test
```

35 testes cobrindo autenticação, controle de acesso por perfil, CRUD de
solicitações (incluindo a regra "editar/excluir somente enquanto Aberto"),
filtros, Kanban, dashboard, pedidos de acesso e gerenciamento de usuários —
ver `tests/Feature/`.
