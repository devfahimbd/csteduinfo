-- Migration: Add file_name column to resources table
-- Run this SQL if you already have the database set up
-- This allows storing the original uploaded filename for proper download naming

ALTER TABLE resources ADD COLUMN file_name VARCHAR(255) AFTER file_path;

-- improved documentation clarity
