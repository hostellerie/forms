<?php

$_SQL[] = "CREATE TABLE {$_TABLES['forms_definitions']} (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  slug varchar(64) NOT NULL,
  title varchar(255) NOT NULL,
  description text NULL,
  success_message text NULL,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  allow_anonymous tinyint(1) NOT NULL DEFAULT 1,
  store_results tinyint(1) NOT NULL DEFAULT 1,
  email_results tinyint(1) NOT NULL DEFAULT 0,
  recipient varchar(255) NOT NULL DEFAULT '',
  created int(11) unsigned NOT NULL DEFAULT 0,
  modified int(11) unsigned NOT NULL DEFAULT 0,
  owner_id mediumint(8) unsigned NOT NULL DEFAULT 2,
  PRIMARY KEY (id),
  UNIQUE KEY slug (slug)
) ENGINE=MyISAM";

$_SQL[] = "CREATE TABLE {$_TABLES['forms_fields']} (
  id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  form_id mediumint(8) unsigned NOT NULL,
  field_order mediumint(8) unsigned NOT NULL DEFAULT 0,
  name varchar(64) NOT NULL,
  label varchar(255) NOT NULL,
  type varchar(32) NOT NULL DEFAULT 'text',
  options text NULL,
  placeholder varchar(255) NOT NULL DEFAULT '',
  help_text varchar(255) NOT NULL DEFAULT '',
  is_required tinyint(1) NOT NULL DEFAULT 0,
  is_active tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY form_id (form_id),
  KEY field_order (field_order)
) ENGINE=MyISAM";

$_SQL[] = "CREATE TABLE {$_TABLES['forms_submissions']} (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  form_id mediumint(8) unsigned NOT NULL,
  uid mediumint(8) unsigned NOT NULL DEFAULT 1,
  submitted int(11) unsigned NOT NULL DEFAULT 0,
  ip_hash char(64) NOT NULL DEFAULT '',
  user_agent varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY form_id (form_id),
  KEY submitted (submitted)
) ENGINE=MyISAM";

$_SQL[] = "CREATE TABLE {$_TABLES['forms_submission_values']} (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  submission_id int(11) unsigned NOT NULL,
  field_id mediumint(8) unsigned NOT NULL,
  field_name varchar(64) NOT NULL,
  field_value mediumtext NULL,
  PRIMARY KEY (id),
  KEY submission_id (submission_id),
  KEY field_id (field_id)
) ENGINE=MyISAM";
