-- SQL script that creates the necessary database and tables.
CREATE DATABASE IF NOT EXISTS board;

USE board;

CREATE TABLE users (
    user_id int NOT NULL AUTO_INCREMENT,
    username varchar(255),
    password varchar(255),
    profile_image varchar(50) DEFAULT "defaultprofile.svg",
    last_login BIGINT DEFAULT 0,
    username_color varchar(64) DEFAULT '#000000',
    PRIMARY KEY (user_id)
);

CREATE TABLE messages (
    message_id int NOT NULL AUTO_INCREMENT,
    user_id int,
    message varchar(500) DEFAULT (NULL),
    file varchar(50) DEFAULT (NULL),
    date varchar(64),
    time varchar(64),
    edited int NULL DEFAULT (NULL),
    notif_time BIGINT DEFAULT 0,
    reply int DEFAULT 0,
    PRIMARY KEY (message_id)
);

CREATE TABLE groupchats(
    user_id int,
    tablename varchar(64)
);

CREATE TABLE groupchat_settings(
    tablename varchar(64),
    groupchat_name varchar(300),
    groupchat_image varchar(128) DEFAULT "defaultgroupchat.svg",
    groupchat_background_image varchar(128) DEFAULT (NULL)
);