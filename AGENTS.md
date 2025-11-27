# Repository Guidelines

## Project Structure & Module Organization
- Root entrypoints: `index.php` (router + `view()`), `config.php` (PDO + env), `auth.php` (session/auth middleware), `login.php`, `home.php`.
- Feature folders:
  - `inmuebles/` and `negocios/`: CRUD views/handlers (`list.php`, `form.php`).
  - `partials/`: shared layout (`header.php`, `footer.php`).
  - `uploads/`: user files (ensure writable; see Security).
- Views are plain PHP. Use `view()` (in `index.php`) to wrap pages with partials.

## Build, Test, and Development Commands
- Requirements: PHP 8+, MySQL 5.7+/8. Composer not used.
- Configure DB via env vars (preferred): `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
  - PowerShell: `$env:DB_NAME="catastro"; $env:DB_USER="root"`
  - Bash: `export DB_NAME=catastro DB_USER=root`
- Run locally from repo root:
  - `php -S localhost:8000` then open `http://localhost:8000/index.php?a=home`.
- Quick smoke test (example): `curl "http://localhost:8000/index.php?a=inmuebles&q=centro"`

## Coding Style & Naming Conventions
- PHP style: PSR-12-ish, 2-space indent, UTF-8, short array syntax `[]`, strict comparisons when possible.
- Escape output with `h()` from `config.php` in all templates.
- Filenames: lowercase, kebab-case (`recuperar-password.php`). Routes/actions (`?a=`): lower_snake (`inmueble_new`).
- DB columns: snake_case. PHP variables: lowerCamelCase or meaningful nouns (`$recentNegocios`, `$inmuebleId`).

## Testing Guidelines
- No formal test suite yet. Provide manual steps in PRs covering: login, `inmuebles` list/create/edit/delete, `negocios` flows, and redirects.
- Use browser devtools and `error_log()` for debugging. Keep functions small for future PHPUnit adoption.
- Include sample `curl`/URL checks in PR descriptions.

## Commit & Pull Request Guidelines
- Commits: imperative mood with scope prefix. Examples:
  - `inmuebles: validate required fields`
  - `auth: expire session on inactivity`
- PRs include: summary, screenshots for UI, DB schema/migration notes (tables: `inmuebles`, `negocios`, `operadores`), manual test steps, and linked issue.

## Security & Configuration Tips
- Never commit secrets. Prefer env vars over hardcoding. Do not echo raw DB errors to users.
- Always protect private pages with `require_once 'auth.php';` and redirect before output.
- Ensure `uploads/` is writable and treat uploads as untrusted (validate type/size; avoid executing files).

## Comunicación del Agente
- Responder siempre en español, salvo que el usuario solicite explícitamente otro idioma.
- Mantener un tono conciso, directo y amable, centrado en acciones.
- Preambulos, planes, mensajes finales y comentarios de PR también en español.
- El código, nombres de archivos y rutas se mantienen en el idioma/nomenclatura del proyecto; solo cambia el texto explicativo.
