SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============ РОЛІ ТА ПРАВА ============
CREATE TABLE roles (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 slug VARCHAR(32) NOT NULL UNIQUE,
 title VARCHAR(64) NOT NULL,
 description VARCHAR(255) DEFAULT NULL,
 level TINYINT UNSIGNED NOT NULL DEFAULT 0,
 is_system TINYINT(1) NOT NULL DEFAULT 0,
 require_2fa TINYINT(1) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 slug VARCHAR(64) NOT NULL UNIQUE,
 title VARCHAR(128) NOT NULL,
 group_name VARCHAR(32) NOT NULL DEFAULT 'common'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
 role_id INT UNSIGNED NOT NULL,
 permission_id INT UNSIGNED NOT NULL,
 PRIMARY KEY (role_id, permission_id),
 CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
 CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ КОРИСТУВАЧІ ============
CREATE TABLE users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 public_id CHAR(12) NOT NULL UNIQUE,
 login VARCHAR(32) NOT NULL UNIQUE,
 email VARCHAR(190) DEFAULT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 display_name VARCHAR(64) NOT NULL,
 avatar_initials CHAR(3) DEFAULT NULL,
 telegram_id VARCHAR(32) DEFAULT NULL,
 discord_id VARCHAR(32) DEFAULT NULL,
 role_id INT UNSIGNED NOT NULL,
 status ENUM('pending','active','suspended','deleted') NOT NULL DEFAULT 'pending',
 must_change_password TINYINT(1) NOT NULL DEFAULT 1,
 totp_secret_encrypted VARBINARY(512) DEFAULT NULL,
 totp_enabled TINYINT(1) NOT NULL DEFAULT 0,
 session_version INT UNSIGNED NOT NULL DEFAULT 1,
 failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
 locked_until DATETIME DEFAULT NULL,
 last_login_at DATETIME DEFAULT NULL,
 last_activity_at DATETIME DEFAULT NULL,
 theme ENUM('dark','light') NOT NULL DEFAULT 'dark',
 timezone VARCHAR(64) NOT NULL DEFAULT 'Europe/Kyiv',
 quiet_from TIME DEFAULT '23:00:00',
 quiet_to TIME DEFAULT '09:00:00',
 notes_owner TEXT DEFAULT NULL,
 created_by INT UNSIGNED DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_last_activity ON users(last_activity_at);

CREATE TABLE user_backup_codes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 code_hash VARCHAR(255) NOT NULL,
 used_at DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_bcodes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL UNIQUE,
 expires_at DATETIME NOT NULL,
 used_at DATETIME DEFAULT NULL,
 ip_hash CHAR(64) DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_presets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_presets_exp ON password_resets(expires_at);

CREATE TABLE sessions_meta (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 session_id_hash CHAR(64) NOT NULL UNIQUE,
 ip_hash CHAR(64) DEFAULT NULL,
 user_agent VARCHAR(255) DEFAULT NULL,
 fingerprint CHAR(64) DEFAULT NULL,
 session_version INT UNSIGNED NOT NULL,
 is_impersonation TINYINT(1) NOT NULL DEFAULT 0,
 last_seen_at DATETIME NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_sessions_user ON sessions_meta(user_id);

-- ============ КАТЕГОРІЇ ЗАНЯТЬ ============
CREATE TABLE categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 public_id CHAR(12) NOT NULL UNIQUE,
 slug VARCHAR(48) NOT NULL UNIQUE,
 title VARCHAR(64) NOT NULL,
 description TEXT DEFAULT NULL,
 duty_text TEXT DEFAULT NULL,
 icon VARCHAR(16) DEFAULT NULL,
 color CHAR(7) NOT NULL DEFAULT '#9db4d0',
 visibility ENUM('team','owner_only') NOT NULL DEFAULT 'team',
 sort_order INT NOT NULL DEFAULT 0,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE category_user (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 category_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 role_in_category ENUM('lead','member','backup') NOT NULL DEFAULT 'member',
 responsibilities TEXT DEFAULT NULL,
 workload TINYINT UNSIGNED NOT NULL DEFAULT 2,
 is_urgent_channel TINYINT(1) NOT NULL DEFAULT 1,
 notes_owner TEXT DEFAULT NULL,
 assigned_by INT UNSIGNED DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_cat_user (category_id, user_id),
 CONSTRAINT fk_cu_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
 CONSTRAINT fk_cu_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_cu_user ON category_user(user_id);

-- ============ ЗАВДАННЯ ============
CREATE TABLE tasks (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 public_id CHAR(12) NOT NULL UNIQUE,
 code VARCHAR(16) NOT NULL UNIQUE,
 parent_id INT UNSIGNED DEFAULT NULL,
 title VARCHAR(200) NOT NULL,
 description MEDIUMTEXT DEFAULT NULL,
 type ENUM('primary','urgent','additional','routine') NOT NULL DEFAULT 'additional',
 priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
 status ENUM('new','in_progress','on_hold','review','done','cancelled') NOT NULL DEFAULT 'new',
 assign_mode ENUM('single','each','shared') NOT NULL DEFAULT 'single',
 category_id INT UNSIGNED DEFAULT NULL,
 created_by INT UNSIGNED NOT NULL,
 progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
 due_at DATETIME DEFAULT NULL,
 started_at DATETIME DEFAULT NULL,
 completed_at DATETIME DEFAULT NULL,
 estimate_minutes SMALLINT UNSIGNED DEFAULT NULL,
 tags JSON DEFAULT NULL,
 recurrence_id INT UNSIGNED DEFAULT NULL,
 period_key VARCHAR(32) DEFAULT NULL,
 require_review TINYINT(1) NOT NULL DEFAULT 1,
 is_pinned TINYINT(1) NOT NULL DEFAULT 0,
 deleted_at DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_tasks_parent FOREIGN KEY (parent_id) REFERENCES tasks(id) ON DELETE SET NULL,
 CONSTRAINT fk_tasks_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
 CONSTRAINT fk_tasks_author FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_tasks_status ON tasks(status, deleted_at);
CREATE INDEX idx_tasks_due ON tasks(due_at);
CREATE INDEX idx_tasks_cat ON tasks(category_id);
CREATE INDEX idx_tasks_type_prio ON tasks(type, priority);
CREATE FULLTEXT INDEX ft_tasks ON tasks(title, description);

CREATE TABLE task_assignments (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 task_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 status ENUM('new','in_progress','on_hold','review','done','cancelled') NOT NULL DEFAULT 'new',
 progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
 due_at DATETIME DEFAULT NULL,
 assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 accepted_at DATETIME DEFAULT NULL,
 done_at DATETIME DEFAULT NULL,
 seen_at DATETIME DEFAULT NULL,
 report_note TEXT DEFAULT NULL,
 rejection_reason VARCHAR(255) DEFAULT NULL,
 reviewed_by INT UNSIGNED DEFAULT NULL,
 UNIQUE KEY uq_task_user (task_id, user_id),
 CONSTRAINT fk_ta_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
 CONSTRAINT fk_ta_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_ta_user_status ON task_assignments(user_id, status);
CREATE INDEX idx_ta_due ON task_assignments(due_at);

CREATE TABLE task_checklist (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 task_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED DEFAULT NULL,
 title VARCHAR(200) NOT NULL,
 position SMALLINT NOT NULL DEFAULT 0,
 is_done TINYINT(1) NOT NULL DEFAULT 0,
 done_at DATETIME DEFAULT NULL,
 CONSTRAINT fk_tc_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_tc_task ON task_checklist(task_id);

CREATE TABLE task_comments (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 task_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 body TEXT NOT NULL,
 mentions JSON DEFAULT NULL,
 is_system TINYINT(1) NOT NULL DEFAULT 0,
 deleted_at DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_tcom_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
 CONSTRAINT fk_tcom_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_tcom_task ON task_comments(task_id, created_at);

CREATE TABLE task_status_history (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 task_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 from_status VARCHAR(20) DEFAULT NULL,
 to_status VARCHAR(20) NOT NULL,
 comment VARCHAR(255) DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_tsh_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_tsh_task ON task_status_history(task_id, created_at);

CREATE TABLE task_recurrence (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(200) NOT NULL,
 description MEDIUMTEXT DEFAULT NULL,
 type ENUM('primary','urgent','additional','routine') NOT NULL DEFAULT 'routine',
 priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
 category_id INT UNSIGNED DEFAULT NULL,
 target_kind ENUM('users','category_all','category_leads') NOT NULL DEFAULT 'users',
 target_ids JSON DEFAULT NULL,
 assign_mode ENUM('each','shared') NOT NULL DEFAULT 'each',
 freq ENUM('daily','weekly','monthly') NOT NULL DEFAULT 'weekly',
 interval_num TINYINT UNSIGNED NOT NULL DEFAULT 1,
 weekdays JSON DEFAULT NULL,
 month_day TINYINT UNSIGNED DEFAULT NULL,
 due_hour TINYINT UNSIGNED NOT NULL DEFAULT 20,
 require_review TINYINT(1) NOT NULL DEFAULT 1,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 last_generated_key VARCHAR(32) DEFAULT NULL,
 created_by INT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_tr_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE task_templates (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 title VARCHAR(200) NOT NULL,
 description MEDIUMTEXT DEFAULT NULL,
 type ENUM('primary','urgent','additional','routine') NOT NULL DEFAULT 'routine',
 priority ENUM('low','normal','high','critical') NOT NULL DEFAULT 'normal',
 category_id INT UNSIGNED DEFAULT NULL,
 checklist JSON DEFAULT NULL,
 estimate_minutes SMALLINT UNSIGNED DEFAULT NULL,
 require_review TINYINT(1) NOT NULL DEFAULT 1,
 usage_count INT UNSIGNED NOT NULL DEFAULT 0,
 created_by INT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_tt_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ ФАЙЛИ ============
CREATE TABLE attachments (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 entity_type ENUM('task','comment','kb_article','user') NOT NULL DEFAULT 'task',
 entity_id INT UNSIGNED NOT NULL,
 uploaded_by INT UNSIGNED NOT NULL,
 original_name VARCHAR(200) NOT NULL,
 stored_name CHAR(40) NOT NULL UNIQUE,
 mime VARCHAR(100) NOT NULL,
 size_bytes INT UNSIGNED NOT NULL,
 token CHAR(40) NOT NULL UNIQUE,
 downloads INT UNSIGNED NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_att_user FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_att_entity ON attachments(entity_type, entity_id);

-- ============ БАЗА ЗНАНЬ ============
CREATE TABLE kb_sections (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 parent_id INT UNSIGNED DEFAULT NULL,
 title VARCHAR(80) NOT NULL,
 slug VARCHAR(96) NOT NULL UNIQUE,
 icon VARCHAR(16) DEFAULT NULL,
 sort_order INT NOT NULL DEFAULT 0,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT fk_kbs_parent FOREIGN KEY (parent_id) REFERENCES kb_sections(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kb_articles (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 public_id CHAR(12) NOT NULL UNIQUE,
 section_id INT UNSIGNED DEFAULT NULL,
 category_id INT UNSIGNED DEFAULT NULL,
 title VARCHAR(160) NOT NULL,
 slug VARCHAR(190) NOT NULL UNIQUE,
 body MEDIUMTEXT NOT NULL,
 excerpt VARCHAR(300) DEFAULT NULL,
 tags JSON DEFAULT NULL,
 visibility ENUM('all','category','owner_only') NOT NULL DEFAULT 'all',
 is_pinned TINYINT(1) NOT NULL DEFAULT 0,
 views INT UNSIGNED NOT NULL DEFAULT 0,
 helpful INT UNSIGNED NOT NULL DEFAULT 0,
 author_id INT UNSIGNED NOT NULL,
 last_checked_at DATETIME DEFAULT NULL,
 last_checked_by INT UNSIGNED DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_kba_section FOREIGN KEY (section_id) REFERENCES kb_sections(id) ON DELETE SET NULL,
 CONSTRAINT fk_kba_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
 CONSTRAINT fk_kba_author FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_kba_vis ON kb_articles(visibility, is_pinned);
CREATE FULLTEXT INDEX ft_kba ON kb_articles(title, body);

CREATE TABLE kb_article_revisions (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 article_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 body MEDIUMTEXT NOT NULL,
 change_note VARCHAR(255) DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_kbar_article FOREIGN KEY (article_id) REFERENCES kb_articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kb_links (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 group_name VARCHAR(64) NOT NULL,
 title VARCHAR(120) NOT NULL,
 url VARCHAR(600) NOT NULL,
 note VARCHAR(255) DEFAULT NULL,
 icon VARCHAR(16) DEFAULT NULL,
 category_id INT UNSIGNED DEFAULT NULL,
 is_secret TINYINT(1) NOT NULL DEFAULT 0,
 clicks INT UNSIGNED NOT NULL DEFAULT 0,
 sort_order INT NOT NULL DEFAULT 0,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_by INT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_kbl_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_kbl_group ON kb_links(group_name, sort_order);

CREATE TABLE kb_secret_requests (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 link_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 reason VARCHAR(255) DEFAULT NULL,
 status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
 handled_by INT UNSIGNED DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_ksr_link FOREIGN KEY (link_id) REFERENCES kb_links(id) ON DELETE CASCADE,
 CONSTRAINT fk_ksr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============ СЕРВЕРИ ТА МОНІТОРИНГ ============
CREATE TABLE servers (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 public_id CHAR(12) NOT NULL UNIQUE,
 name VARCHAR(80) NOT NULL,
 game ENUM('cs2','csgo','unturned','minecraft','rust','other') NOT NULL DEFAULT 'other',
 protocol ENUM('source','minecraft','http') NOT NULL DEFAULT 'source',
 host VARCHAR(120) NOT NULL,
 port SMALLINT UNSIGNED NOT NULL,
 query_port SMALLINT UNSIGNED DEFAULT NULL,
 rcon_port SMALLINT UNSIGNED DEFAULT NULL,
 rcon_password_encrypted VARBINARY(512) DEFAULT NULL,
 category_id INT UNSIGNED DEFAULT NULL,
 is_public_visible TINYINT(1) NOT NULL DEFAULT 0,
 monitor_enabled TINYINT(1) NOT NULL DEFAULT 1,
 last_status ENUM('online','offline','unknown') NOT NULL DEFAULT 'unknown',
 last_checked_at DATETIME DEFAULT NULL,
 sort_order INT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_srv_cat FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE server_stats (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 server_id INT UNSIGNED NOT NULL,
 is_online TINYINT(1) NOT NULL,
 players_online SMALLINT UNSIGNED DEFAULT NULL,
 players_max SMALLINT UNSIGNED DEFAULT NULL,
 map_name VARCHAR(80) DEFAULT NULL,
 version VARCHAR(64) DEFAULT NULL,
 ping_ms SMALLINT UNSIGNED DEFAULT NULL,
 error_text VARCHAR(255) DEFAULT NULL,
 checked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_ss_server FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_ss_server_time ON server_stats(server_id, checked_at);

CREATE TABLE server_incidents (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 server_id INT UNSIGNED NOT NULL,
 kind ENUM('down','up','high_ping','low_players') NOT NULL,
 message VARCHAR(255) DEFAULT NULL,
 started_at DATETIME NOT NULL,
 ended_at DATETIME DEFAULT NULL,
 notified TINYINT(1) NOT NULL DEFAULT 0,
 CONSTRAINT fk_si_server FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_si_server ON server_incidents(server_id, started_at);

-- ============ СПОВІЩЕННЯ ============
CREATE TABLE notifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 type VARCHAR(48) NOT NULL,
 title VARCHAR(160) NOT NULL,
 body VARCHAR(600) DEFAULT NULL,
 url VARCHAR(255) DEFAULT NULL,
 severity ENUM('info','warning','critical') NOT NULL DEFAULT 'info',
 entity_type VARCHAR(32) DEFAULT NULL,
 entity_id INT UNSIGNED DEFAULT NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 read_at DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_notif_user_read ON notifications(user_id, is_read, created_at);

CREATE TABLE notification_queue (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED DEFAULT NULL,
 channel ENUM('telegram','discord','email') NOT NULL,
 payload JSON NOT NULL,
 status ENUM('pending','sent','failed','skipped') NOT NULL DEFAULT 'pending',
 attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
 last_error VARCHAR(255) DEFAULT NULL,
 scheduled_at DATETIME NOT NULL,
 sent_at DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_nq_status ON notification_queue(status, scheduled_at);

CREATE TABLE reminders (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 task_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 remind_at DATETIME NOT NULL,
 offset_minutes INT NOT NULL DEFAULT 1440,
 is_sent TINYINT(1) NOT NULL DEFAULT 0,
 CONSTRAINT fk_rem_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
 CONSTRAINT fk_rem_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_rem_due ON reminders(remind_at, is_sent);

-- ============ ШІ-ПОМІЧНИК ============
CREATE TABLE ai_conversations (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 title VARCHAR(120) NOT NULL DEFAULT 'Новый диалог',
 model VARCHAR(64) DEFAULT NULL,
 message_count INT UNSIGNED NOT NULL DEFAULT 0,
 total_tokens INT UNSIGNED NOT NULL DEFAULT 0,
 is_pinned TINYINT(1) NOT NULL DEFAULT 0,
 last_message_at DATETIME DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_aic_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_aic_user ON ai_conversations(user_id, last_message_at);

CREATE TABLE ai_messages (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 conversation_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 role ENUM('system','user','assistant') NOT NULL,
 content MEDIUMTEXT NOT NULL,
 prompt_tokens INT UNSIGNED DEFAULT NULL,
 completion_tokens INT UNSIGNED DEFAULT NULL,
 is_error TINYINT(1) NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_aim_conv FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_aim_conv ON ai_messages(conversation_id, id);
CREATE INDEX idx_aim_user_time ON ai_messages(user_id, created_at);

-- ============ НАЛАШТУВАННЯ, АУДИТ, СЛУЖОВЕ ============
CREATE TABLE settings (
 setting_key VARCHAR(64) PRIMARY KEY,
 setting_value MEDIUMTEXT DEFAULT NULL,
 is_secret TINYINT(1) NOT NULL DEFAULT 0,
 value_type ENUM('string','int','bool','json') NOT NULL DEFAULT 'string',
 group_name VARCHAR(32) NOT NULL DEFAULT 'general',
 updated_by INT UNSIGNED DEFAULT NULL,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED DEFAULT NULL,
 action VARCHAR(64) NOT NULL,
 entity_type VARCHAR(32) DEFAULT NULL,
 entity_id INT UNSIGNED DEFAULT NULL,
 entity_label VARCHAR(160) DEFAULT NULL,
 before_data JSON DEFAULT NULL,
 after_data JSON DEFAULT NULL,
 ip_hash CHAR(64) DEFAULT NULL,
 user_agent VARCHAR(255) DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_audit_user ON audit_logs(user_id, created_at);
CREATE INDEX idx_audit_entity ON audit_logs(entity_type, entity_id);
CREATE INDEX idx_audit_time ON audit_logs(created_at);

CREATE TABLE login_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED DEFAULT NULL,
 login_attempted VARCHAR(32) DEFAULT NULL,
 ip_hash CHAR(64) DEFAULT NULL,
 user_agent VARCHAR(255) DEFAULT NULL,
 success TINYINT(1) NOT NULL DEFAULT 0,
 reason VARCHAR(48) DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_ll_time ON login_logs(created_at);
CREATE INDEX idx_ll_ip ON login_logs(ip_hash, created_at);

CREATE TABLE rate_limits (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 bucket VARCHAR(80) NOT NULL,
 hits INT UNSIGNED NOT NULL DEFAULT 1,
 window_start DATETIME NOT NULL,
 UNIQUE KEY uq_bucket (bucket),
 INDEX idx_rl_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_daily (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 day DATE NOT NULL,
 tasks_created SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 tasks_done SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 tasks_overdue SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 comments SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 logins SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 UNIQUE KEY uq_user_day (user_id, day),
 CONSTRAINT fk_ad_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE app_meta (
 meta_key VARCHAR(48) PRIMARY KEY,
 meta_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
