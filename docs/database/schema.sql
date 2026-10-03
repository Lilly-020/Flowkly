-- =============================================================================
-- Flowkly — Portal de Solicitações Internas
-- Script de criação do schema (tabelas de domínio da aplicação).
--
-- Sintaxe compatível com SQLite (banco padrão do projeto). Para MySQL/
-- PostgreSQL, troque "AUTOINCREMENT" por "AUTO_INCREMENT" (MySQL) ou por uma
-- coluna "SERIAL"/"GENERATED ALWAYS AS IDENTITY" (PostgreSQL); os demais
-- tipos e constraints (CHECK, FOREIGN KEY) são padrão ANSI SQL.
--
-- Gerado a partir das migrations em database/migrations/. Não inclui as
-- tabelas de infraestrutura do framework (sessions, cache, jobs,
-- password_reset_tokens, migrations) — ver dicionario-dados.md para a lista
-- completa, incluindo essas tabelas de apoio.
-- =============================================================================

-- -----------------------------------------------------------------------------
-- users
-- Contas de acesso ao portal. O campo "role" define o perfil (usuário ou TI).
-- -----------------------------------------------------------------------------
CREATE TABLE users (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    name                VARCHAR(255) NOT NULL,
    email               VARCHAR(255) NOT NULL,
    role                VARCHAR(20)  NOT NULL DEFAULT 'user'
                         CHECK (role IN ('user', 'ti')),
    email_verified_at   DATETIME,
    password            VARCHAR(255) NOT NULL,
    remember_token      VARCHAR(100),
    created_at          DATETIME,
    updated_at          DATETIME,

    CONSTRAINT users_email_unique UNIQUE (email)
);

-- -----------------------------------------------------------------------------
-- access_requests
-- Pedidos de acesso ao portal, feitos por quem ainda não tem conta (tela de
-- login, botão "Solicitar acesso"). A TI aprova (cria um "user") ou recusa.
-- -----------------------------------------------------------------------------
CREATE TABLE access_requests (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            VARCHAR(255) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    reason          TEXT NOT NULL,
    status          VARCHAR(20) NOT NULL DEFAULT 'pending'
                    CHECK (status IN ('pending', 'accepted', 'declined')),
    reviewed_by     INTEGER,
    reviewed_at     DATETIME,
    created_at      DATETIME,
    updated_at      DATETIME,

    CONSTRAINT access_requests_reviewed_by_fk
        FOREIGN KEY (reviewed_by) REFERENCES users (id) ON DELETE SET NULL
);

-- -----------------------------------------------------------------------------
-- tickets
-- A solicitação em si: título, descrição, categoria, status e para quem foi
-- encaminhada. "user_id" é sempre quem abriu; "assigned_to" é sempre uma
-- conta com role = 'ti'.
-- -----------------------------------------------------------------------------
CREATE TABLE tickets (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    title           VARCHAR(255) NOT NULL,
    description     TEXT NOT NULL,
    type            VARCHAR(20) NOT NULL
                    CHECK (type IN ('hardware', 'software', 'rede', 'acesso', 'outro')),
    status          VARCHAR(20) NOT NULL DEFAULT 'aberta'
                    CHECK (status IN ('aberta', 'em_andamento', 'concluida')),
    user_id         INTEGER NOT NULL,
    assigned_to     INTEGER NOT NULL,
    created_at      DATETIME,
    updated_at      DATETIME,

    CONSTRAINT tickets_user_id_fk
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT tickets_assigned_to_fk
        FOREIGN KEY (assigned_to) REFERENCES users (id) ON DELETE CASCADE
);

-- -----------------------------------------------------------------------------
-- ticket_attachments
-- Imagens anexadas a uma solicitação no momento da criação (até 5, 5MB cada).
-- "path" é relativo ao disco público (storage/app/public), servido em
-- /storage/{path}.
-- -----------------------------------------------------------------------------
CREATE TABLE ticket_attachments (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_id       INTEGER NOT NULL,
    path            VARCHAR(255) NOT NULL,
    original_name   VARCHAR(255) NOT NULL,
    created_at      DATETIME,
    updated_at      DATETIME,

    CONSTRAINT ticket_attachments_ticket_id_fk
        FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE
);

-- -----------------------------------------------------------------------------
-- ticket_status_histories
-- Auditoria de cada mudança de status de uma solicitação (quem mudou, de
-- qual status para qual, e quando). A primeira linha de cada ticket tem
-- from_status = NULL e representa a criação ("Solicitação criada").
-- -----------------------------------------------------------------------------
CREATE TABLE ticket_status_histories (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_id       INTEGER NOT NULL,
    from_status     VARCHAR(20)
                    CHECK (from_status IS NULL OR from_status IN ('aberta', 'em_andamento', 'concluida')),
    to_status       VARCHAR(20) NOT NULL
                    CHECK (to_status IN ('aberta', 'em_andamento', 'concluida')),
    changed_by      INTEGER,
    created_at      DATETIME,
    updated_at      DATETIME,

    CONSTRAINT ticket_status_histories_ticket_id_fk
        FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE,
    CONSTRAINT ticket_status_histories_changed_by_fk
        FOREIGN KEY (changed_by) REFERENCES users (id) ON DELETE SET NULL
);

-- -----------------------------------------------------------------------------
-- Índices efetivamente criados pelas migrations
-- -----------------------------------------------------------------------------
CREATE UNIQUE INDEX users_email_unique ON users (email);

-- -----------------------------------------------------------------------------
-- Índices recomendados para um volume maior de dados (ainda não criados por
-- nenhuma migration — as foreign keys acima já garantem a integridade
-- referencial, mas não criam índice secundário automaticamente no SQLite).
-- -----------------------------------------------------------------------------
-- CREATE INDEX tickets_user_id_index ON tickets (user_id);
-- CREATE INDEX tickets_assigned_to_index ON tickets (assigned_to);
-- CREATE INDEX tickets_status_index ON tickets (status);
-- CREATE INDEX ticket_attachments_ticket_id_index ON ticket_attachments (ticket_id);
-- CREATE INDEX ticket_status_histories_ticket_id_index ON ticket_status_histories (ticket_id);
