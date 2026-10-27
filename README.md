# Agentoom Community Edition

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/Database-PostgreSQL%2017-336791.svg)](compose.prod.yaml)
[![Docker Ready](https://img.shields.io/badge/Docker-Compose%20v2-2496ED.svg)](compose.yaml)
[![Ecosystem](https://img.shields.io/badge/Agentoom-v0.9.0-6366F1.svg)](https://packagist.org/packages/agentoom/core-main)
[![OCI Image](https://img.shields.io/badge/GHCR-community--core-2ea44f.svg)](https://github.com/agentoom/agentoom/pkgs/container/community-core)

**Agentoom** is an enterprise-grade AI operating system for creating, orchestrating, and securing autonomous AI agents.

This repository (`agentoom/agentoom`) is the **official open-source community distribution**. It packages all public Agentoom modules into a turn-key application ready to evaluate, test, or deploy in production.

---

## 🤖 Primary AI Engine: OpenRouter

> [!IMPORTANT]
> **OpenRouter is the primary, battle-tested LLM engine for Agentoom.**  
> While Agentoom supports direct integrations with OpenAI, Anthropic, Google Gemini, DeepSeek, Groq, and local Ollama instances, **OpenRouter is by far the most extensively tested provider**, and several core features are natively built around it:

- **Performance & Cost Suffixes**: Natively supports `:nitro` (ultra-low latency routing) and `:floor` (cost-minimized routing) across all agents and pipeline steps.
- **Granular Prompt Caching**: Automatic context caching controls that slash recurring token costs on repetitive agent workflows.
- **Dynamic Catalog & Pricing Sync**: Run `php artisan providers:sync-models openrouter` to automatically ingest new models, context windows, and real-time per-token pricing into your database.
- **Credit Accounting & Multipliers**: Precise token tracking and custom billing multipliers mapped directly to OpenRouter usage rates.

Other providers (DeepSeek, OpenAI, Anthropic, Gemini, Groq, Ollama) can be used alongside or as fallbacks, but OpenRouter is strongly recommended for complete feature coverage and stability.

---

## ⚡ Quickstart with Docker Compose

Agentoom adopts the modern Docker Compose specification (`compose.yaml`) with dedicated setups for production (PostgreSQL) and development (SQLite).

### Option 1: Production Deployment (PostgreSQL + Pre-built Image)
Recommended for live servers and operators who want a stable setup without building from source:

```bash
# Clone the repository
git clone https://github.com/agentoom/agentoom.git
cd agentoom

# Launch with PostgreSQL 17 and pre-built GHCR image
docker compose -f compose.prod.yaml up -d
```
- **Web App**: `http://localhost` (or `${APP_PORT:-80}`)
- **Database**: Automated PostgreSQL 17 with persistent volume (`agentoom-postgres-data`).
- **WebSockets**: Laravel Reverb streaming on port `8080`.

---

### Option 2: Default Production Stack (PostgreSQL + Local Build)
Builds the container locally and spins up PostgreSQL 17:

```bash
docker compose up -d
```
- Access at 👉 **[http://localhost:8000](http://localhost:8000)**.

---

### Option 3: Developer & Testing Profile (SQLite + Live Reload)
Designed for local development, testing tools, and writing custom extensions:

```bash
docker compose -f compose.dev.yaml up
```
- **Live Code Reload**: Host codebase is mounted directly into the container.
- **Zero-Dependency Database**: Embedded SQLite for rapid local iteration.
- **Vite Hot-Reload**: Exposes port `5173`.
- **Debug Mode**: `APP_DEBUG=true` and verbose logging enabled.

---

## 🔐 Secure Initial Administrator Provisioning

> [!CAUTION]
> **No public or hardcoded credentials are ever shipped with Agentoom.**

When the production container initializes for the first time:

1. **Auto-Generated Secure Password (Default)**:  
   If no password is provided in your environment, Agentoom automatically generates a **cryptographically secure 20-character password** for `admin@agentoom.local` (or your defined `ADMIN_EMAIL`) and prints the credentials **once** to the container stdout logs:
   ```bash
   docker compose logs agentoom
   ```
   ```text
   ================================================================================
   🔐 AGENTOOM SUPERADMIN CREDENTIALS GENERATED (INITIAL SETUP)
   ================================================================================
    Email:    admin@agentoom.local
    Password: <generated_random_password>
   --------------------------------------------------------------------------------
   ⚠️  Save these credentials immediately! This banner is displayed only once.
   ================================================================================
   ```

2. **Custom Environment Variables**:  
   You can optionally define your own administrator credentials in `.env` before booting:
   ```dotenv
   ADMIN_EMAIL=admin@yourcompany.com
   ADMIN_PASSWORD=YourStrongPasswordHere
   ```

3. **Interactive CLI Administrator Management**:  
   You can create additional administrators or reset an existing password anytime via the Artisan CLI:
   ```bash
   # Interactive prompt (name, email, password)
   docker compose exec agentoom php artisan agentoom:create-admin

   # Or via CLI flags
   docker compose exec agentoom php artisan agentoom:create-admin \
     --name="Security Admin" \
     --email="admin@yourcompany.com" \
     --password="SuperSecretPassword!" \
     --role="superadmin"
   ```

Subsequent container restarts will **never overwrite or reset** existing administrator passwords.

---

## 🔑 AI Provider Configuration (In-App)

> [!NOTE]
> **Zero LLM keys in `.env`!**  
> Agentoom stores and manages provider API keys dynamically inside the application database.

To configure OpenRouter and other providers:
1. Log in with your administrator credentials.
2. Navigate to **Admin &rarr; Configs &rarr; Providers**.
3. Enable **OpenRouter** and enter your API key (`sk-or-...`).
4. (Optional) Run model synchronization to pull all available models:
   ```bash
   docker compose exec agentoom php artisan providers:sync-models openrouter
   ```
5. Configure fallback providers (OpenAI, DeepSeek, Groq, Anthropic, Gemini, Ollama) as desired.

All API credentials are encrypted at rest using **AES-256-GCM**.

---

## 🌟 What's Included in Community Edition?

Agentoom Community packages 7 decoupled open-source packages:

| Package | Version | Description |
|---|---|---|
| [`agentoom/core-main`](https://packagist.org/packages/agentoom/core-main) | `^0.9.0` | **Core Runtime**: Agent lifecycle, pipelines, multi-provider LLMs, MCP runtime, credit accounting, and tool engine. |
| [`agentoom/core-chat-widget`](https://packagist.org/packages/agentoom/core-chat-widget) | `^0.8.0` | **Chat Widget**: Drop-in embeddable real-time streaming web chat and conversational forms. |
| [`agentoom/core-sentinel`](https://packagist.org/packages/agentoom/core-sentinel) | `^0.8.0` | **Security Buffer**: Human-in-the-loop authorization gates and Email OTP verification for high-risk actions. |
| [`agentoom/core-compliance`](https://packagist.org/packages/agentoom/core-compliance) | `^0.8.0` | **EU AI Act Engine**: Automated risk classification, auditable decision logs, and emergency shutdown switches. |
| [`agentoom/core-privacy`](https://packagist.org/packages/agentoom/core-privacy) | `^0.8.0` | **PII Protection**: Deterministic regex detection and reversible token pseudonymization. |
| [`agentoom/core-instant-messaging`](https://packagist.org/packages/agentoom/core-instant-messaging) | `^0.8.0` | **Messaging Bridge**: Native Telegram bot connector for multi-channel autonomous conversations. |
| [`agentoom/core-openapi-v1`](https://packagist.org/packages/agentoom/core-openapi-v1) | `^0.8.0` | **OpenAI Gateway**: OpenAI-compatible `/v1/chat/completions` API proxy for integration with standard SDKs. |

---

## 🛠️ Composer Profiles (Dev vs. Production)

This repository includes two Composer configurations:

### 1. Discovery / Developer Profile (`composer.json` / `composer.dev.json`)
Active by default. Includes testing and developer suites:
- Pest PHP testing framework (`pestphp/pest`, `mockery`)
- Interactive CLI tools (`laravel/pail`, `laravel/sail`)
- Code style and static analysis (`pint`, `larastan`)

### 2. Production Profile (`composer.prod.json`)
Designed for lean, hardened production environments:
- Zero development dependencies
- Pinned stable package versions
- Authoritative classmap optimization (`optimize-autoloader`, `classmap-authoritative`)

```bash
# Switch to Production Profile:
composer run use-prod
composer install --no-dev

# Switch back to Discovery/Dev Profile:
composer run use-dev
composer install
```

---

## 🐳 Container Images

Official container images are built and published to the GitHub Container Registry on every release:

- `ghcr.io/agentoom/community-core:latest`
- `ghcr.io/agentoom/agentoom:latest`

```bash
docker pull ghcr.io/agentoom/community-core:latest
```

---

## 📄 License

Agentoom Community Edition is open-source software licensed under the **[MIT License](LICENSE)**.
