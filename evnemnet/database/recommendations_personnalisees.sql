-- Table: recommendations_personnalisees
-- Create this table in your MySQL database (ex: projet_db)

CREATE TABLE IF NOT EXISTS recommendations_personnalisees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  categorie_preferee VARCHAR(100) NULL,
  budget_max DECIMAL(10,2) NULL,
  localisation VARCHAR(255) NULL,
  ai_suggestion LONGTEXT NULL,
  evenements_suggeres LONGTEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

