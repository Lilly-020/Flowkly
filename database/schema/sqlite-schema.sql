CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "role" varchar not null default 'user'
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" varchar not null,
  "queue" varchar not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE INDEX "failed_jobs_connection_queue_failed_at_index" on "failed_jobs"(
  "connection",
  "queue",
  "failed_at"
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "access_requests"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "reason" text not null,
  "status" varchar not null default 'pending',
  "reviewed_by" integer,
  "reviewed_at" datetime,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("reviewed_by") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "tickets"(
  "id" integer primary key autoincrement not null,
  "title" varchar not null,
  "description" text not null,
  "type" varchar not null,
  "status" varchar not null default 'aberta',
  "user_id" integer not null,
  "assigned_to" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("user_id") references "users"("id") on delete cascade,
  foreign key("assigned_to") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "ticket_attachments"(
  "id" integer primary key autoincrement not null,
  "ticket_id" integer not null,
  "path" varchar not null,
  "original_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("ticket_id") references "tickets"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "ticket_status_histories"(
  "id" integer primary key autoincrement not null,
  "ticket_id" integer not null,
  "from_status" varchar,
  "to_status" varchar not null,
  "changed_by" integer,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("ticket_id") references "tickets"("id") on delete cascade,
  foreign key("changed_by") references "users"("id") on delete set null
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_10_03_000000_add_role_to_users_table',2);
INSERT INTO migrations VALUES(5,'2026_10_03_000001_create_access_requests_table',3);
INSERT INTO migrations VALUES(6,'2026_10_03_000002_create_tickets_table',4);
INSERT INTO migrations VALUES(7,'2026_10_03_000003_create_ticket_status_histories_table',5);
