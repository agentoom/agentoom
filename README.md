# Agentoom Community Edition

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4.svg)](https://php.net)
[![Docker Ready](https://img.shields.io/badge/Docker-Ready-2496ED.svg)](compose.yaml)
[![Ecosystem](https://img.shields.io/badge/Agentoom-v0.9.0-6366F1.svg)](https://packagist.org/packages/agentoom/core-main)
[![OCI Image](https://img.shields.io/badge/GHCR-community--core-2ea44f.svg)](https://github.com/agentoom/agentoom/pkgs/container/community-core)

**Agentoom** is an enterprise-grade AI operating system for creating, orchestrating, and securing autonomous AI agents.

This repository (`agentoom/agentoom`) is the **official open-source community distribution**. It packages all public Agentoom modules into a turn-key application ready to evaluate, customize, or run in production with a single command.

---

## ⚡ Quickstart with Docker Compose

Agentoom adopts the modern Docker Compose specification (`compose.yaml`) and provides dedicated profiles for testing/development and production.

### Option A: Standard Quickstart (`compose.yaml`)
Builds and boots a complete local instance with SQLite, background workers, and Reverb WebSocket streaming:

```bash
# Clone repository
git clone https://github.com/agentoom/agentoom.git
cd agentoom

# Start the stack
docker compose up -d
```
Access the application at 👉 **[http://localhost:8000](http://localhost:8000)**.

---

### Option B: Testing & Development Profile (`compose.dev.yaml`)
Designed for developers extending Agentoom or developing custom AI tools. Features **live code reloading**, debug logging enabled, and Vite hot-reloading port exposure:

```bash
docker compose -f compose.dev.yaml up
```
- **App URL**: `http://localhost:8000`
- **Vite Hot-Reload**: `http://localhost:5173`
- **Reverb WebSockets**: `ws://localhost:8080`
- Mounts the local repository inside the container so code changes reflect instantly.

---

### Option C: Production Deployment Profile (`compose.prod.yaml`)
Designed for production hosts, VPS, or cloud deployments. Pulls the pre-built, hardened multi-stage image from the GitHub Container Registry with **zero build time** and persistent volume management:

```bash
docker compose -f compose.prod.yaml up -d
```
- **Image**: `ghcr.io/agentoom/community-core:latest`
- **Port**: Serves directly on port `80` (or your customized `APP_PORT`).
- **Persistent Data**: SQLite database and user uploads stored in Docker named volumes (`agentoom-storage`, `agentoom-db`).

---

## 🔑 AI Provider Configuration

> [!NOTE]
> **No API keys in `.env` required!**  
> Agentoom manages LLM provider connections dynamically within the application database.

To connect your AI providers:
1. Log in to the Admin Dashboard (default: `superadmin@unusualdope.com` / `password`).
2. Navigate to **Admin &rarr; Configs &rarr; Providers**.
3. Toggle and enter your API keys for:
   - **OpenAI** (GPT-4o, GPT-4o-mini, o1, o3-mini)
   - **Anthropic** (Claude 3.7 Sonnet, Claude 3.5 Haiku)
   - **Google Gemini** (Gemini 2.5 Flash, Gemini 2.5 Pro)
   - **Groq** (Llama 3.3 70B, DeepSeek R1 Distill)
   - **DeepSeek** (DeepSeek V3, DeepSeek R1)
   - **Ollama / Local LLMs** (Any self-hosted model via local HTTP endpoint)

All credentials are encrypted at rest using AES-256-GCM. You can test connections, swap default models, and configure provider fallbacks directly in the UI without restarting your containers.

---

## 👤 First-Time Access & Onboarding

On initial boot, the application automatically runs database migrations, discovers all AI tools, and creates your default administrator:

- **Default Email**: `superadmin@unusualdope.com`
- **Default Password**: `password`

*(Please change the password immediately via Settings upon logging in.)*

---

## 🌟 What's Included in Community Edition?

Agentoom Community packages 8 modular, decoupled open-source packages:

| Package | Version | Description |
|---|---|---|
| [`agentoom/core-main`](https://packagist.org/packages/agentoom/core-main) | `^0.9.0` | **Core Runtime**: Agent lifecycle, pipelines, multi-provider LLMs, MCP runtime, credit accounting, and tool engine. |
| [`agentoom/core-chat-widget`](https://packagist.org/packages/agentoom/core-chat-widget) | `^0.8.0` | **Chat Widget**: Drop-in embeddable real-time streaming web chat and conversational forms. |
| [`agentoom/core-accessibility-widget`](https://packagist.org/packages/agentoom/core-accessibility-widget) | `^0.8.0` | **Accessibility**: WCAG 2.1 / ADA compliant accessibility toolbar (contrast, dyslexia fonts, cursor aids). |
| [`agentoom/core-sentinel`](https://packagist.org/packages/agentoom/core-sentinel) | `^0.8.0` | **Security Buffer**: Human-in-the-loop authorization gates and Email OTP verification for high-risk actions. |
| [`agentoom/core-compliance`](https://packagist.org/packages/agentoom/core-compliance) | `^0.8.0` | **EU AI Act Engine**: Automated risk classification, auditable decision logs, and emergency shutdown switches. |
| [`agentoom/core-privacy`](https://packagist.org/packages/agentoom/core-privacy) | `^0.8.0` | **PII Protection**: Deterministic regex detection and reversible token pseudonymization. |
| [`agentoom/core-instant-messaging`](https://packagist.org/packages/agentoom/core-instant-messaging) | `^0.8.0` | **Messaging Bridge**: Native Telegram bot connector for multi-channel autonomous conversations. |
| [`agentoom/core-openapi-v1`](https://packagist.org/packages/agentoom/core-openapi-v1) | `^0.8.0` | **OpenAI Gateway**: OpenAI-compatible `/v1/chat/completions` API proxy for integration with any standard SDK. |

---

## 🛠️ Composer Profiles (Dev vs. Production)

This repository includes two Composer configurations depending on your use case:

### 1. Discovery / Developer Profile (`composer.json` / `composer.dev.json`)
Active by default. Includes testing and developer tools:
- Test runner (`pestphp/pest`, `mockery`)
- Interactive CLI tools (`laravel/pail`, `laravel/sail`)
- Code style and static analysis (`pint`, `larastan`)

### 2. Production Profile (`composer.prod.json`)
Designed for operators who want a lightweight, rock-solid production environment:
- Zero development dependencies
- Pinned stable package versions
- Authoritative classmap optimization enabled (`optimize-autoloader`, `classmap-authoritative`)

#### Switching Profiles:
```bash
# Switch to Production Profile:
composer run use-prod
composer install --no-dev

# Switch back to Discovery/Dev Profile:
composer run use-dev
composer install
```

---

## 💻 Manual Installation (Host PHP)

If you prefer to run directly on your host machine without Docker:

### Prerequisites
- **PHP 8.3 or 8.4** (`pdo_sqlite` or `pdo_pgsql`, `intl`, `bcmath`, `curl`, `pcntl`)
- **Composer 2.2+**
- **Node.js 20+** & npm

### Setup Steps
```bash
# 1. Clone repository
git clone https://github.com/agentoom/agentoom.git
cd agentoom

# 2. Automated setup (installs dependencies, generates key, migrates DB)
composer run setup

# 3. Start development server
composer run dev
```

---

## 🐳 Container Images

Official container images are automatically built and published via GitHub Actions to the GitHub Container Registry:

- `ghcr.io/agentoom/community-core:latest`
- `ghcr.io/agentoom/agentoom:latest`

```bash
docker pull ghcr.io/agentoom/community-core:latest
```

---

## 📄 License

Agentoom Community Edition is open-source software licensed under the **[MIT License](LICENSE)**.
