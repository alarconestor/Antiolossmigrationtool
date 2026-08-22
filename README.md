# Antiloss Migration Tool

Open-source web tool for migrating Traccar users, vehicles, and user-vehicle relationships between independent Traccar servers using the Traccar API.

Antiloss Migration Tool allows an administrator to migrate selected users and their GPS devices from one Traccar installation to another without requiring direct access to either server's database.

The tool works as a web-based wizard that guides the administrator through the migration process.

---

## What Can It Do?

The current version can migrate:

* Traccar users
* Vehicles / devices
* User ↔ vehicle relationships
* Existing-user detection
* Existing-device detection
* Migration statistics
* Migration logs
* JSON migration packages

The administrator can migrate one user, multiple users, or all available users from the source server.

---

## How It Works

Instead of copying the Traccar database, Antiloss Migration Tool uses the Traccar REST API to reconstruct the required data on the destination server.

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

This makes the migration independent from the database engine.

The source and destination servers can therefore use different database configurations.

---

## Current Features

### Source Server

The administrator connects the tool to the source Traccar server using:

* Traccar API URL
* Administrator username
* Administrator password

The tool validates the connection and maintains the authenticated session while the migration wizard is active.

### User Selection

The tool retrieves users from the source Traccar server through the API.

The administrator can:

* Select one user
* Select multiple users
* Select all users
* View the vehicles associated with each user

This allows selective migrations instead of requiring an entire Traccar installation to be migrated.

### Destination Server

The administrator connects the tool to the destination Traccar server.

The destination connection is independently authenticated and validated before the migration begins.

---

# Migration Process

The current migration process consists of the following stages:

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
7. GENERATE MIGRATION PACKAGE
```

---

## Phase 1 — Users

The tool first checks whether each selected user already exists on the destination server.

Users are primarily matched using their email address.

If the user does not exist, the tool creates the user through the Traccar API.

```http
POST /api/users
```

If the user already exists, the existing destination user is reused instead of creating a duplicate.

---

## Phase 2 — Vehicles

After the users have been processed, the tool migrates their associated vehicles.

Vehicles are identified using their:

```text
uniqueId
```

If the vehicle already exists on the destination server, it is not duplicated.

If it does not exist, the tool creates it through the Traccar API.

```http
POST /api/devices
```

---

## Phase 3 — User ↔ Vehicle Relationships

Once the user and vehicle exist on the destination server, the corresponding relationship is created.

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

# Passwords

The Traccar API does not provide access to the original user's password.

Therefore, the original password cannot be migrated.

Newly created users receive a temporary password defined by the migration process.

The administrator is informed of this during the migration.

Users should change the temporary password after the migration is completed.

The tool does not attempt to copy, recover, or reconstruct the original password.

---

# Duplicate Detection

Antiloss Migration Tool is designed to avoid unnecessary duplicates.

### Users

Users are primarily matched using:

```text
email
```

### Vehicles

Vehicles are matched using:

```text
uniqueId
```

This allows the destination server to already contain some users or vehicles without creating unnecessary duplicates.

---

# Migration Progress

The migration interface provides real-time feedback while the process is running.

Example:

```text
User created
User already exists
Vehicle created
Vehicle already exists
Relationship created
Error creating vehicle
```

The administrator can see the progress of the operation while the migration is running.

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

This provides several advantages:

* No direct database credentials are required
* No database dump is required
* Source and destination can use different database engines
* Only selected data needs to be migrated
* The destination server remains independent
* Additional migration stages can be added later

The tool acts as a migration assistant between two independent Traccar installations.

---

# Web Wizard

The application is designed as a step-by-step wizard.

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
+------------+------------+
             |
             v
+-------------------------+
| Migration Package       |
+-------------------------+
```

The goal is to make the migration process understandable without requiring the administrator to work directly with the Traccar database.

---

# Requirements

* PHP 8.x
* PHP cURL extension
* Apache, Nginx, or another PHP-compatible web server
* Network access to the source Traccar server
* Network access to the destination Traccar server
* Valid Traccar administrator credentials
* Sufficient permissions to read and create the required Traccar entities

---

# Installation

Copy or clone the project into a PHP-compatible web server.

Example:

```text
/var/www/html/antiloss-migration/
```

Verify that the PHP cURL extension is enabled:

```bash
php -m | grep curl
```

Then access the application through your web server.

Example:

```text
https://your-server/migration/
```

The exact URL depends on your web server configuration.

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
* Remove migration packages when they are no longer required
* Change temporary user passwords after migration

---

# Current Scope

The current migration flow focuses on:

```text
Users
   +
Vehicles
   +
User ↔ Vehicle Relationships
```

The project is intentionally being developed as a modular migration assistant.

Additional migration stages can be added without redesigning the entire application.

---

# Future Migration Modules

Potential future migration stages include:

* Notifications
* Geofences
* Groups
* Calendars
* Commands
* Additional permissions
* POIs
* Additional user attributes
* Device settings
* Last position
* Odometer
* Historical positions
* Reports
* Password reset
* Migration history
* PDF migration reports
* Importing previously generated migration packages

---

# Roadmap

The long-term goal is to develop Antiloss Migration Tool into a complete Traccar migration assistant.

Possible migration workflow:

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
   +-- History
   |
   +-- Notifications
   |
   +-- Geofences
   |
   +-- Commands
   |
   +-- Password Reset
   |
   +-- Migration Report
```

Additional stages can be added as required.

---

# Project Philosophy

Antiloss Migration Tool is based on a simple principle:

> Migrate Traccar data through the API, not by copying the database.

This approach is useful when migrating:

* Between independent Traccar servers
* Between different database engines
* Between different server configurations
* Selected customers instead of an entire installation
* Large numbers of users and vehicles

The objective is to make the migration process repeatable, transparent, and easier to manage.

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
* Migration progress
* Migration logs
* JSON migration packages

The architecture is designed to allow additional migration stages to be added progressively.

---

# License

Antiloss Migration Tool is intended to be released as open-source software.

The final license will be defined before the official release.

---

# Credits

Developed by Antiloss Technologies.

## Antiloss Migration Tool

A lightweight, API-based migration assistant for Traccar.

