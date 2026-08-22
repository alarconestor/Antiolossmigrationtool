# Antiloss Migration Tool

Open-source web tool for migrating Traccar users, vehicles, and user-vehicle relationships between independent Traccar servers using the Traccar API.

Antiloss Migration Tool is designed as a migration assistant that guides an administrator through the process of moving Traccar accounts and their GPS devices from one server to another without requiring direct access to the Traccar databases.

The project is designed to be modular, allowing additional migration and administration stages to be added over time.

---

# What Can It Do?

The current version supports:

* Source Traccar server connection
* Destination Traccar server connection
* User selection
* User migration
* Vehicle/device migration
* User ↔ vehicle relationships
* Duplicate user detection
* Duplicate vehicle detection
* Real-time migration progress
* Migration logs
* JSON migration packages
* Email notifications to migrated users
* Temporary passwords for newly created users

The administrator can migrate one user, multiple users, or all available users from the source server.

---

# How It Works

Instead of copying the Traccar database, Antiloss Migration Tool communicates with the source and destination servers through the Traccar REST API.

```text
SOURCE TRACCAR
      |
      | API
      v
+----------------------+
| Antiloss Migration   |
| Tool                 |
+----------------------+
      |
      | API
      v
DESTINATION TRACCAR
```

This approach allows the source and destination servers to remain independent.

The database engine used by each server does not need to be the same because the migration is performed through the Traccar API.

---

# Migration Workflow

The current workflow is:

```text
1. CONNECT TO SOURCE
          |
          v
2. SELECT USERS
          |
          v
3. CONNECT TO DESTINATION
          |
          v
4. MIGRATE USERS
          |
          v
5. MIGRATE VEHICLES
          |
          v
6. CREATE RELATIONSHIPS
          |
          v
7. NOTIFY USERS BY EMAIL
          |
          v
8. GENERATE MIGRATION PACKAGE
```

The architecture is designed so that additional migration stages can be inserted into this workflow.

---

# Source Server

The administrator connects the application to the source Traccar server using:

* Traccar API URL
* Administrator username
* Administrator password

The tool validates the connection before continuing.

The authenticated Traccar session is maintained while the migration wizard is active.

---

# User Selection

The tool retrieves users from the source Traccar server through the API.

The administrator can:

* Select a single user
* Select multiple users
* Select all users
* View vehicles associated with each user

This makes it possible to migrate only the accounts that are required.

---

# Destination Server

The administrator connects the application to the destination Traccar server using:

* Destination API URL
* Administrator username
* Administrator password

The destination credentials are validated before the migration begins.

---

# Phase 1 — User Migration

The tool checks whether each selected user already exists on the destination server.

Users are primarily matched using their email address.

If the user does not exist, the tool creates the account through the Traccar API.

```http
POST /api/users
```

If the user already exists, the existing destination account is reused instead of creating a duplicate.

---

# User Passwords

The Traccar API does not provide access to the original user's password.

Therefore, the original password cannot be migrated.

When a new user is created on the destination server, the migration process assigns a temporary password.

The temporary password can then be communicated to the user through the email notification process.

Users should change the temporary password after accessing the new platform.

The tool does not attempt to recover, copy, or reconstruct the original Traccar password.

---

# Phase 2 — Vehicle Migration

After processing the users, the tool retrieves their associated vehicles.

Vehicles are identified using their Traccar:

```text
uniqueId
```

If the vehicle already exists on the destination server, it is reused.

If it does not exist, the tool creates the vehicle through the Traccar API.

```http
POST /api/devices
```

This prevents unnecessary duplication when the destination server already contains some of the devices.

---

# Phase 3 — User ↔ Vehicle Relationships

Once the user and vehicle exist on the destination server, the corresponding relationship is created.

The tool uses the Traccar permissions API:

```http
POST /api/permissions
```

Example:

```json
{
    "userId": 31,
    "deviceId": 5
}
```

This gives the migrated user access to the corresponding vehicle on the destination server.

---

# Duplicate Detection

Antiloss Migration Tool checks existing entities before creating new ones.

## Users

Users are primarily matched using:

```text
email
```

## Vehicles

Vehicles are matched using:

```text
uniqueId
```

This allows migrations to be performed against destination servers that already contain some users or devices.

---

# Email Notifications

Antiloss Migration Tool can send email notifications to migrated users after the migration process.

This allows the administrator to notify users automatically instead of contacting every migrated user manually.

The email can communicate information such as:

* Migration to the new Traccar server
* New platform/server address
* User account information
* Temporary password
* Instructions for accessing the new platform

The email notification can be integrated into the migration workflow after the user has been successfully processed.

```text
USER MIGRATION
      |
      v
ACCOUNT CREATED
      |
      v
TEMPORARY PASSWORD
      |
      v
EMAIL NOTIFICATION
      |
      v
USER ACCESS TO NEW SERVER
```

This makes the migration process more complete by combining data migration with user communication.

---

# Migration Progress

The web interface provides real-time feedback while the migration is running.

Examples:

```text
User created
User already exists
Vehicle created
Vehicle already exists
Relationship created
Email sent
Error creating vehicle
Error sending email
```

This allows the administrator to identify successful operations and errors during the migration.

---

# Migration Packages

After the migration is completed, the tool generates a JSON package containing structured information about the operation.

Example:

```text
packages/
└── migration_2026-08-21_10-30-00.json
```

The package can contain information such as:

* Source server
* Destination server
* Processed users
* Source user IDs
* Destination user IDs
* Processed vehicles
* Source vehicle IDs
* Destination vehicle IDs
* Created relationships
* Email notification results
* Migration statistics
* Errors encountered during the migration

Migration packages can be useful for:

* Migration records
* Troubleshooting
* Audit purposes
* Future report generation
* Future import/export functionality

---

# Why Use the Traccar API?

Antiloss Migration Tool deliberately avoids direct database manipulation.

The migration is performed through the Traccar API.

Advantages include:

* No direct database credentials are required
* No database dump is required
* Source and destination can use different database engines
* Only selected data needs to be migrated
* Source and destination installations remain independent
* Additional migration stages can be added progressively

The tool acts as an intermediary between two independent Traccar installations.

---

# Web Wizard

The application is designed as a step-by-step web wizard.

```text
+-------------------------+
| Source Server           |
+------------+------------+
             |
             v
+-------------------------+
| Select Users            |
+------------+------------+
             |
             v
+-------------------------+
| Destination Server      |
+------------+------------+
             |
             v
+-------------------------+
| Migration               |
|                         |
| Users                   |
| Vehicles                |
| Relationships           |
| Email Notifications     |
+------------+------------+
             |
             v
+-------------------------+
| Migration Package       |
+-------------------------+
```

The goal is to make the migration process manageable without requiring the administrator to work directly with the Traccar database.

---

# Requirements

* PHP 8.x
* PHP cURL extension
* Apache, Nginx, or another PHP-compatible web server
* Network access to the source Traccar server
* Network access to the destination Traccar server
* Valid Traccar administrator credentials
* Sufficient permissions to read and create the required Traccar entities
* SMTP/email configuration for email notifications

---

# Installation

Copy or clone the project into a PHP-compatible web server.

Example:

```text
/var/www/html/antiloss-migration/
```

Verify that PHP cURL is enabled:

```bash
php -m | grep curl
```

Configure the application according to the included configuration files.

Then access the application through your web server.

Example:

```text
https://your-server/migration/
```

The exact URL depends on the web server configuration.

---

# Project Structure

The project intentionally uses a simple structure to make maintenance and future development easier.

```text
/
├── index.php
│
├── api/
│   ├── conectar_origen.php
│   ├── conectar_destino.php
│   └── migrar.php
│
├── services/
│   └── traccar.php
│
├── wizard/
│   ├── usuarios.php
│   ├── destino.php
│   └── migracion.php
│
├── assets/
│   └── css/
│       └── app.css
│
└── packages/
    └── migration_YYYY-MM-DD_HH-MM-SS.json
```

The structure may evolve as additional migration modules are introduced.

---

# Security

Antiloss Migration Tool does not require direct database access.

Administrator credentials should nevertheless be handled carefully.

Recommended practices:

* Use HTTPS
* Do not share administrator credentials
* Run the tool on a trusted server
* Protect the migration application from unauthorized access
* Protect the `packages/` directory
* Do not expose generated migration packages publicly
* Remove migration packages when they are no longer required
* Change temporary user passwords after migration
* Use secure SMTP credentials for email delivery

---

# Current Scope

The current migration flow includes:

```text
Users
   +
Vehicles
   +
User ↔ Vehicle Relationships
   +
Temporary Passwords
   +
Email Notifications
   +
Migration Package
```

The project is being developed as a modular migration assistant rather than a simple database-copy utility.

---

# Roadmap

The long-term goal is to develop Antiloss Migration Tool into a complete Traccar migration assistant.

Potential migration stages include:

```text
Migration
   |
   +-- Users
   |
   +-- Vehicles
   |
   +-- Relationships
   |
   +-- Last Position
   |
   +-- Odometer
   |
   +-- Historical Positions
   |
   +-- Notifications
   |
   +-- Geofences
   |
   +-- Groups
   |
   +-- Calendars
   |
   +-- Commands
   |
   +-- Additional Permissions
   |
   +-- Device Settings
   |
   +-- Email Notifications
   |
   +-- SMS Commands
   |
   +-- Migration Reports
```

Some of these capabilities may be implemented as optional migration stages in future versions.

---

# Project Philosophy

Antiloss Migration Tool is based on a simple principle:

> Migrate Traccar data through the API, not by copying the database.

The tool is intended to make Traccar migrations more practical when:

* Moving between independent Traccar servers
* Moving between different database engines
* Moving between different server configurations
* Migrating selected customers
* Migrating large numbers of users and vehicles
* Automating communication with migrated users

The goal is to make the migration process repeatable, transparent, and easier to manage.

---

# Project Status

Current status: Functional

The current version supports:

* Source Traccar connection
* Destination Traccar connection
* User selection
* User migration
* Vehicle migration
* User ↔ vehicle relationships
* Duplicate detection
* Temporary passwords
* Email notifications
* Real-time migration progress
* Migration logs
* JSON migration packages

The architecture is designed to allow additional migration stages and automation features to be added progressively.

---

# License

Antiloss Migration Tool is intended to be released as open-source software.

The final license will be defined before the official release.

---

# Credits

Developed by Antiloss Technologies.

## Antiloss Migration Tool

A lightweight, API-based migration assistant for Traccar.
