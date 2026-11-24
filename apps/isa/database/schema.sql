CREATE TABLE IF NOT EXISTS isa_profiles (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  display_name VARCHAR(120) NOT NULL DEFAULT '',
  learner_context TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT isa_profiles_single_row CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS isa_conversations (
  id CHAR(36) NOT NULL PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX isa_conversations_updated_idx (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS isa_messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  conversation_id CHAR(36) NOT NULL,
  message_order INT UNSIGNED NOT NULL,
  role ENUM('isa', 'user') NOT NULL,
  body TEXT NOT NULL,
  correction TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT isa_messages_conversation_fk FOREIGN KEY (conversation_id) REFERENCES isa_conversations(id) ON DELETE CASCADE,
  UNIQUE KEY isa_messages_order_unique (conversation_id, message_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
