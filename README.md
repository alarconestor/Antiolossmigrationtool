# Traccar Migration Tool v2
Flujo: Origen -> Usuarios -> Destino -> Migración.
La migración se ejecuta en dos fases: primero todos los usuarios; después sus vehículos y relaciones.
Los usuarios nuevos reciben la contraseña temporal . Los duplicados de vehículos se detectan por uniqueId.
Cada ejecución genera un paquete JSON en packages/.

Antiloss Migration Tool

An open-source web tool for migrating Traccar users, vehicles, and user ↔ vehicle relationships between a Source Server (SA) and a Destination Server (SD) using the Traccar API.

The project is designed as a simple web wizard that guides the administrator through the migration process without requiring direct access to the Traccar database.

Features

Independent connection to a Source Traccar Server (SA).

Independent connection to a Destination Traccar Server (SD).

Temporary Traccar sessions maintained while the browser session is active.

Retrieve users from the source server.

Select individual users or select all users.

View the vehicles associated with each user.

Migrate users and their vehicles.

Create user ↔ vehicle relationships.

Two-phase migration process:

Users.

Vehicles and relationships.

Detect existing users and vehicles on the destination server.

Real-time migration progress.

Detailed migration log.

Universal temporary password for newly created users.

The original Traccar password is not migrated because the API does not provide it.

Generate a JSON migration package.

Download the migration package when the process is completed.

Wizard-style interface with progress indicators and real-time messages.

Migration Workflow

The tool follows this workflow:

┌──────────────────────┐
│ 1. Source Server     │
│       (SA)           │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ 2. Select Users      │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ 3. Destination       │
│    Server (SD)       │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ 4. Migration         │
│                      │
│   Phase 1: Users     │
│   Phase 2: Vehicles  │
│           +          │
│      Relationships   │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ JSON Package +       │
│ Download             │
└──────────────────────┘

Why two phases?

Although a user and their vehicles are considered part of the same migration, Traccar requires the user to exist before the relationship between the user and their devices can be created correctly.

For this reason, the tool first creates or locates all users and only then processes their vehicles and relationships.

User Passwords

The Traccar API does not provide access to the original user password.

Therefore, newly created users receive a temporary universal password:



The migration process reports this to the administrator.

Important: users should change the temporary password to a secure personal password after migration.

The tool does not attempt to copy or reconstruct the original password from the source server.

Project Structure

The project is intentionally kept simple to make maintenance and future development easier:

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

The structure may evolve as new migration features are added.

Technologies

PHP

HTML5

CSS3

JavaScript

cURL

Traccar REST API

JSON

PHP Sessions

The tool does not require a direct copy of the Traccar database.

Requirements

PHP 8.x recommended.

PHP cURL extension.

Apache, Nginx, or another PHP-compatible web server.

Network access to the Source Server.

Network access to the Destination Server.

Valid administrator credentials for both Traccar servers.

Sufficient Traccar permissions to read and create users, devices, and relationships.

Installation

Copy the project into your web server's public directory.

For example:

/var/www/html/antiloss-migration/

Verify that PHP has the cURL extension enabled:

php -m | grep curl

Then open the application through your web server:

https://your-server/migration/

The exact URL depends on your web server configuration.

Usage

1. Connect to the Source Server

Enter:

Traccar API URL.

Administrator username.

Administrator password.

The tool validates the connection and keeps the authenticated session available during the wizard.

2. Select Users

The tool queries:

GET /api/users

and displays the users available on the source server.

You can select:

A single user.

Multiple users.

All users.

You can also inspect the vehicles associated with a user before starting the migration.

3. Connect to the Destination Server

Enter:

Destination server URL.

Administrator username.

Administrator password.

The tool validates the credentials before starting the migration.

4. Run the Migration

The migration is performed in two phases.

Phase 1 — Users

The destination server is queried to determine whether each selected user already exists.

If the user does not exist:

POST /api/users

The available user data from the source server is used to create the destination user.

The original password is not migrated.

Phase 2 — Vehicles and Relationships

For each user, the tool queries:

GET /api/devices?userId=ID

Each vehicle is checked on the destination using its:

uniqueId

If it does not exist:

POST /api/devices

The relationship is then created using:

POST /api/permissions

Example:

{
  "userId": 31,
  "deviceId": 5
}

Duplicate Detection

Users are primarily matched using their email address.

Vehicles are identified using:

uniqueId

This prevents unnecessary duplication when users or devices already exist on the destination server.

Migration Package

When the migration finishes, the tool creates a JSON file inside:

packages/

Example:

migration_2026-08-18_16-30-00.json

The package contains structured information about the migration, including:

Source server.

Destination server.

Processed users.

Source user IDs.

Destination user IDs.

Processed vehicles.

Source vehicle IDs.

Destination vehicle IDs.

Created relationships.

Migration statistics.

Errors encountered during the process.

The package can later be used as:

Migration evidence.

Historical records.

A source for generating reports.

A foundation for future import/export functionality.

Error Logging

The migration interface displays real-time messages such as:

✓ User created
→ User already exists
✓ Vehicle created
✓ Relationship created
✗ Error creating vehicle

The migration package also stores structured information about the operation.

This makes it possible to investigate partial migrations and errors after the process has completed.

Security

The tool works through Traccar authentication sessions and does not require direct access to the Traccar database.

Recommendations:

Use HTTPS.

Do not share administrator credentials.

Run the tool only on a trusted server.

Remove migration packages when they are no longer required.

Change temporary passwords assigned to migrated users.

Do not expose the packages/ directory publicly in production without additional protection.

Current Limitations

The first version deliberately focuses on the basic migration structure:

User
  +
Vehicles
  +
User ↔ Vehicle Relationship

It does not currently attempt to migrate every possible Traccar entity or configuration.

Potential future migration phases include:

Notifications.

Geofences.

Groups.

Calendars.

Commands.

Additional permissions.

POIs.

Additional user/device settings.

Importing previously generated JSON packages.

Internal database support for stored migrations.

PDF migration reports.

Migration history.

Project Philosophy

Antiloss Migration Tool is not intended to directly copy the Traccar database.

Instead, it uses the Traccar API to progressively reconstruct the required entities and relationships on the destination server.

This makes the approach useful when:

The destination server uses a different database engine.

Source and destination installations have different configurations.

An account needs to be moved between independent Traccar installations.

Only selected users need to be migrated.

Project Status

The current version is functional for migrating:

Users
  +
Vehicles
  +
User ↔ Vehicle Relationships

The project is designed to be extended with additional migration phases over time.

License

This project is intended to be released as open-source software.

The specific license can be defined before publishing the repository.

Credits

Developed by Antiloss Technologies.

Antiloss Migration Tool

A lightweight tool for migrating Traccar users, vehicles, and relationships through the Traccar API.
