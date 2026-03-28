-- MindBloom PostgreSQL Schema
-- Run this once to set up all required tables

CREATE TABLE IF NOT EXISTS users (
    id           SERIAL PRIMARY KEY,
    username     VARCHAR(100) NOT NULL UNIQUE,
    email        VARCHAR(255) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    last_login   TIMESTAMP,
    created_at   TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS moods (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    mood       VARCHAR(50) NOT NULL,
    note       TEXT DEFAULT '',
    intensity  INTEGER DEFAULT 5,
    triggers   TEXT DEFAULT '',
    activities TEXT DEFAULT '',
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS journal (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title      VARCHAR(255) NOT NULL,
    content    TEXT NOT NULL,
    mood       VARCHAR(50),
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS settings (
    id               SERIAL PRIMARY KEY,
    user_id          INTEGER NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    daily_reminders  BOOLEAN DEFAULT FALSE,
    reminder_time    TIME DEFAULT '09:00:00',
    journal_prompts  BOOLEAN DEFAULT FALSE,
    theme            VARCHAR(20) DEFAULT 'light',
    font_size        VARCHAR(20) DEFAULT 'medium',
    updated_at       TIMESTAMP DEFAULT NOW()
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id         SERIAL PRIMARY KEY,
    user_id    INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type       VARCHAR(50) NOT NULL DEFAULT 'reminder',  -- reminder, tip, alert
    title      VARCHAR(255) NOT NULL,
    message    TEXT NOT NULL,
    is_read    BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Email settings per user
ALTER TABLE settings ADD COLUMN IF NOT EXISTS email_reminders BOOLEAN DEFAULT FALSE;
ALTER TABLE settings ADD COLUMN IF NOT EXISTS email_reminder_time TIME DEFAULT '09:00:00';
