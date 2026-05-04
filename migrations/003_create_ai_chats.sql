-- Migration: Create AI chat history table
-- Date: 2026-05-04
-- Description: Stores user conversations with the NutriVert AI assistant.

CREATE TABLE IF NOT EXISTS ai_chats (
    id_chat INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    user_message TEXT NOT NULL,
    ai_response MEDIUMTEXT NOT NULL,
    model VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ai_chats_user
        FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateurs(id_utilisateur)
        ON DELETE CASCADE
);
