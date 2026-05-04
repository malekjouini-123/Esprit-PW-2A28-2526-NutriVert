-- Migration: Add password reset functionality
-- Date: 2026-04-28
-- Description: Adds columns to support password reset functionality

-- Add reset_token and reset_token_expiry columns to utilisateurs table
ALTER TABLE utilisateurs 
ADD COLUMN reset_token VARCHAR(64) NULL AFTER mot_de_passe,
ADD COLUMN reset_token_expiry DATETIME NULL AFTER reset_token;

-- Create index on reset_token for faster lookups
CREATE INDEX idx_reset_token ON utilisateurs(reset_token);
