-- Run this SQL in phpMyAdmin or MySQL CLI to create the leads table

USE a1679hju_hoablgoa;

CREATE TABLE IF NOT EXISTS leads (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(120)  NOT NULL,
    phone        VARCHAR(30)   NOT NULL,
    email        VARCHAR(150)  DEFAULT '',
    project      VARCHAR(200)  NOT NULL,
    message      TEXT          DEFAULT '',
    ip_address   VARCHAR(50)   DEFAULT '',
    user_agent   VARCHAR(500)  DEFAULT '',
    source       VARCHAR(200)  DEFAULT 'Website',
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (created_at),
    INDEX (project(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
