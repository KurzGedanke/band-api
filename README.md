# Band API

A small Symfony JSON API that answers one question: **when does a band play at a festival?**

Manage festivals, stages, bands and time slots through an [EasyAdmin](https://symfony.com/bundles/EasyAdmin) back office, and serve the schedule over a read-only REST API — handy for festival apps, info screens, or an "up next" ticker.

- 🎤 Festivals, stages, bands and time slots with full relations
- 🔌 Read-only JSON API (see [API reference](#api-reference))
- 🛠️ EasyAdmin UI for editing data at `/admin`
- 📖 Interactive API docs (Swagger UI) at `/api/doc`
- 🗄️ Zero-config SQLite database — no DB server required

## Tech stack

| | |
|---|---|
| Framework | Symfony 7.4 |
| Language | PHP ≥ 8.2 |
| Database | SQLite (Doctrine ORM) |
| Admin | EasyAdmin 4 |
| API docs | NelmioApiDocBundle + Swagger UI |

## Requirements

- PHP 8.2 or higher
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download) (optional, for `symfony server:start`)

## Getting started

```sh
# 1. Install dependencies
composer install

# 2. Create the SQLite database and schema
php bin/console doctrine:database:create
php bin/console doctrine:schema:create

# 3. (optional) Seed the Dong Open Air 2026 line-up
php bin/console app:seed:dong-open-air

# 4. Create an admin user for the back office
php bin/console app:create-admin admin@example.com secret

# 5. Start the dev server
symfony server:start          # or: php -S 127.0.0.1:8000 -t public
```

The app is now available at **http://localhost:8000**:

- `/` — login
- `/admin` — EasyAdmin back office
- `/api/doc` — interactive API documentation
- `/api/...` — the JSON API

> **Database config.** The connection is set in `.env` via `DATABASE_URL`, pointing at
> `var/data.db` (SQLite) by default. Override it in `.env.local` to use MySQL/PostgreSQL instead.

## API reference

All endpoints are read-only (`GET`) and return JSON. `{festivalSlug}` is the festival **slug**
(e.g. `dong-open-air-2026`), `{bandSlug}` is a band slug, and `{stageSlug}` is a stage slug.
Slugs are auto-generated from the name (lower-case ASCII, e.g. `Hauptbühne` → `hauptbuhne`) and
returned by the list endpoints.

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/festivals` | List all festivals |
| `GET` | `/api/festivals/{festivalSlug}/bands` | Bands playing a festival |
| `GET` | `/api/festivals/{festivalSlug}/{bandSlug}` | A band's detail + its slot at the festival |
| `GET` | `/api/festivals/{festivalSlug}/stages` | Stages of a festival |
| `GET` | `/api/festivals/{festivalSlug}/stages/{stageSlug}` | A single stage |
| `GET` | `/api/festivals/{festivalSlug}/stages/{stageSlug}/timeslots` | A stage's time slots (ordered) |
| `GET` | `/api/festivals/{festivalSlug}/stages/{stageSlug}/upnext` | The next slot by current server time |

**Example**

```sh
curl "http://localhost:8000/api/festivals/dong-open-air-2026/bands"
```

### Interactive docs

A full OpenAPI 3.0 definition lives in [`openapi.yaml`](openapi.yaml) and is served as
Swagger UI at **`/api/doc`** (raw spec at `/api/doc.json`). To lint it:

```sh
npx @redocly/cli lint openapi.yaml
```

## Console commands

| Command | Description |
|---------|-------------|
| `app:create-admin <email> <password>` | Create or update an admin user |
| `app:seed:dong-open-air [--no-images]` | Seed the Dong Open Air 2026 festival, stages, bands and time slots |
| `app:seed:band-details [--overwrite]` | Fill genre, social links and descriptions for the Dong Open Air 2026 bands |
| `app:backfill-slugs` | Generate slugs for existing festivals and stages (run once after adding the slug columns) |
| `send:band-reminder` | Find time slots starting in the next 5 minutes |

## Project structure

```
src/
  Command/          Console commands (admin creation, seeding, reminders)
  Controller/
    API/            JSON API controllers
    Admin/          EasyAdmin CRUD controllers + dashboard
  Entity/           Festival, Stage, Band, TimeSlot, User
  Repository/       Doctrine repositories
  Services/         SerializerService (circular-reference-safe JSON)
config/             Symfony & bundle configuration
migrations/         Doctrine migrations (legacy, MySQL-specific)
openapi.yaml        OpenAPI 3.0 definition for the JSON API
```

> **Note on migrations.** The files in `migrations/` were generated for MySQL and don't run
> on SQLite. For a fresh database use `doctrine:schema:create` (step 2 above), which builds
> the schema from the entity mappings regardless of the database driver.

## Deployment

1. Set `APP_ENV=prod` and a strong `APP_SECRET` (use [Symfony secrets](https://symfony.com/doc/current/configuration/secrets.html) for production).
2. Configure `DATABASE_URL` for your production database.
3. Install optimized dependencies and warm the cache:

   ```sh
   composer install --no-dev --optimize-autoloader
   php bin/console doctrine:schema:create   # or run your migrations
   php bin/console cache:clear
   php bin/console assets:install public
   ```

4. Serve `public/` behind a web server (nginx/Apache + PHP-FPM).
