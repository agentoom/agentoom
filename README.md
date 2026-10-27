# Agentoom Community Edition

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/Database-PostgreSQL%2017-336791.svg)](compose.prod.yaml)
[![Docker Ready](https://img.shields.io/badge/Docker-Compose%20v2-2496ED.svg)](compose.yaml)
[![Ecosystem](https://img.shields.io/badge/Agentoom-v0.9.41-6366F1.svg)](https://packagist.org/packages/agentoom/core-main)
[![OCI Image](https://img.shields.io/badge/GHCR-community--core-2ea44f.svg)](https://github.com/agentoom/agentoom/pkgs/container/community-core)

**Agentoom** is an enterprise-grade AI operating system for creating, orchestrating, and securing autonomous AI agents.

This repository (`agentoom/agentoom`) is the **official open-source community distribution**. It packages all public Agentoom modules into a turn-key application ready to evaluate, test, or deploy in production.

---

## 🤖 Multi-Provider AI Architecture

Agentoom is built from the ground up to be **model- and provider-agnostic**. You can seamlessly run your agents with your provider of choice:

- **Direct Native Providers**: Full, battle-tested support for **OpenAI** (GPT-4o, o1, o3-mini), **DeepSeek** (DeepSeek V3, DeepSeek R1), **Anthropic** (Claude 3.7 Sonnet), **Google Gemini** (Gemini 2.5 Flash / Pro), **Groq** (ultra-fast Llama inference), and local self-hosted **Ollama** models.
- **OpenRouter Integration**: OpenRouter is the primary daily driver and most heavily tested provider during Agentoom development. In addition to standard agent completions, it powers several specialized platform features:
  - **Dynamic Routing Suffixes**: Fine-tune routing per agent or pipeline step using `:nitro` (lowest latency) or `:floor` (cost-optimized).
  - **Prompt Caching Controls**: Granular toggles to take advantage of prompt caching on long recurring context windows.
  - **Automated Catalog & Pricing Sync**: Run `php artisan providers:sync-models openrouter` to automatically pull hundreds of models, context limits, and real-time token pricing into your local database.
  - **Credit Accounting Multipliers**: Granular margin and credit multipliers tailored to OpenRouter token rates.

Whether you connect directly to OpenAI, run DeepSeek, self-host via Ollama, or route through OpenRouter, all providers work smoothly across your autonomous agents and pipelines with multi-provider failover support.

---

## ⚡ Quickstart with Docker Compose

Agentoom adopts the modern Docker Compose specification with dedicated deployment profiles for production (PostgreSQL) and development (SQLite).

### Option 1: Production Deployment (PostgreSQL + Pre-built Image)
Recommended for live servers, cloud instances, and operators who want a stable setup without compiling from source:

```bash
# 1. Clone the repository
git clone https://github.com/agentoom/agentoom.git
cd agentoom

# 2. Launch PostgreSQL 17 and pre-built Community container
docker compose -f compose.prod.yaml up -d
```
- **Web App**: Access at [http://localhost](http://localhost) (or port `8000` / `${APP_PORT:-80}`).
- **Database**: Automated PostgreSQL 17 with persistent volume (`agentoom-postgres-data`).
- **WebSockets**: Laravel Reverb streaming on port `8080`.

---

### Option 2: Default Production Stack (PostgreSQL + Local Build)
Builds the container image locally from the Dockerfile and spins up PostgreSQL 17:

```bash
docker compose up -d
```
- Access at 👉 **[http://localhost:8000](http://localhost:8000)** (or [http://localhost](http://localhost)).

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

## 🚀 First-Time Launch & Onboarding Walkthrough

Follow these simple steps for a smooth initial setup:

1. **Retrieve Initial Superadmin Credentials**:
   On first boot, Agentoom automatically generates a secure 20-character password and outputs it to the container logs:
   ```bash
   docker compose -f compose.prod.yaml logs agentoom
   # or: docker logs agentoom-app
   ```
   *(Alternatively, define `ADMIN_EMAIL` and `ADMIN_PASSWORD` in your `.env` beforehand).*

2. **Log In**:
   Navigate to [http://localhost/login](http://localhost/login) (or [http://localhost:8000/login](http://localhost:8000/login)) and sign in with your administrator credentials. Public registration is disabled by default for security.

3. **Complete the Onboarding Wizard (`/onboarding`)**:
   Upon first login, the interactive setup wizard will guide you through:
   - **Step 1: AI Provider**: Select your primary provider (e.g. **OpenRouter**, **OpenAI**, **Anthropic**, **Groq**, **DeepSeek**, or local **Ollama**) and enter your API key.
   - **Step 2: Model Synchronization**: Click **Sync Models**. Agentoom pulls real-time model lists, context limits, and token pricing into your database.
   - **Step 3: System Agents & Transcription**: Assign default models to core platform agents (Task Orchestrator, Form Builder, etc.) and optionally select an audio transcription model (e.g., **OpenAI Whisper 1**, **Whisper Large V3**, **Gemini**, or **Voxtral**).
   - **Step 4: Credits & Governance**: Configure low-credit email alert thresholds and EU AI Act compliance toggles.

Once submitted, your platform is fully active and ready to orchestrate autonomous agents!

---

## 💡 Operational Insights for a Smooth Deployment

Based on production experience, keep the following architectural guidelines in mind:

### 1. Application Key Persistence (`APP_KEY`)
All sensitive provider credentials and API tokens are encrypted at rest using AES-256.
- **Persistent Volume Protection**: Agentoom automatically saves the generated application encryption key to `/app/storage/.app_key` inside the persistent `agentoom-storage` volume. On subsequent container restarts or rebuilds, this key is restored automatically.
- **Recommended Practice**: Provide a fixed `APP_KEY` in your `.env` file (e.g., generated with `php artisan key:generate --show`). Both `compose.prod.yaml` and `compose.yaml` forward `APP_KEY: "${APP_KEY:-}"` directly to the container, ensuring your database encryption MAC remains stable indefinitely.

### 2. Port Binding & Host Conflicts
- By default, the production profile maps both port `80` and port `8000` (`- "${APP_PORT:-80}:80"` and `- "8000:80"`).
- If your host machine already runs an HTTP server (such as Apache, Nginx, or `systemd-resolved` on port 53/80), configure `APP_PORT=8000` or access the application via **[http://localhost:8000](http://localhost:8000)**.
- FrankenPHP's internal Caddy server is configured with `SERVER_NAME=:80` to prevent unintended automatic HTTPS redirect loops when behind local proxies or non-TLS ports.

### 3. OpenRouter Model Sync & Whisper Transcription
- **Text & Chat LLMs**: OpenRouter's standard API catalog provides over 460+ general completion models.
- **Dedicated Audio & Whisper Models**: Speech-to-text models on OpenRouter (including `openai/whisper-1`, `openai/whisper-large-v3`, and `openai/whisper-large-v3-turbo`) are served through specialized endpoints. Agentoom's model synchronizer automatically queries both endpoints, giving you full access to Whisper and multimodal audio models during onboarding and in settings.
- **Transcription is Optional**: If you only run text-based agents, you can set the Transcription Model to **None** during onboarding.

### 4. Multi-Provider Flexibility Post-Onboarding
The onboarding wizard configures your primary provider. Once inside the dashboard:
- Navigate to **Admin &rarr; Configs &rarr; Providers** anytime to add secondary providers.
- You can mix and match providers freely—for example, using **OpenRouter** or **Anthropic** for agent cognitive reasoning, and a dedicated **Groq** or **OpenAI** connection for low-latency Whisper transcription.

### 5. Password Manager Compatibility
To prevent browser extensions (such as Bitwarden, 1Password, or LastPass) from aggressively auto-filling administrator emails into searchable dropdowns and listbox filters, Agentoom incorporates `data-bwignore`, `data-1p-ignore`, and dynamic element disabling. If using auto-fill, ensure your extension fills only the designated login inputs.

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

To configure your providers:
1. Log in with your administrator credentials.
2. Navigate to **Admin &rarr; Configs &rarr; Providers**.
3. Toggle and enter your API keys for your preferred provider(s): **OpenAI**, **DeepSeek**, **OpenRouter**, **Anthropic**, **Google Gemini**, **Groq**, or local **Ollama** endpoints.
4. (Optional) If using OpenRouter, you can synchronize the full real-time model catalog:
   ```bash
   docker compose exec agentoom php artisan providers:sync-models openrouter
   ```
5. Set your global default provider or assign specific providers/models per agent and pipeline step.

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
