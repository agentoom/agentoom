# Agentoom Community Edition

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4.svg)](https://php.net)
[![Docker Ready](https://img.shields.io/badge/Docker-Ready-2496ED.svg)](docker-compose.yml)
[![Ecosystem](https://img.shields.io/badge/Agentoom-v0.9.0-6366F1.svg)](https://packagist.org/packages/agentoom/core-main)

**Agentoom** is an enterprise-grade AI operating system for creating, orchestrating, and securing autonomous AI agents. 

This repository (`agentoom/agentoom`) is the **official open-source community distribution**. It packages all public Agentoom modules into a turn-key Laravel application ready to evaluate or deploy in production with a single command.

---

## ⚡ Quickstart with Docker (Recommended)

You can launch a complete, self-contained Agentoom instance with SQLite, database queues, and pre-built frontend in under a minute:

```bash
# 1. Clone the repository
git clone https://github.com/agentoom/agentoom.git
cd agentoom

# 2. Start the container
docker compose up -d
```

Once running, navigate to:
👉 **[http://localhost:8000](http://localhost:8000)**

### First-Time Access & Onboarding
On initial boot, the container automatically migrates the database, discovers all AI tools, and creates your default administrator:
- **Default Email**: `superadmin@unusualdope.com`
- **Default Password**: `password` *(Change this immediately in settings or via the onboarding wizard)*

---

## 🌟 What's Included in Community Edition?

Agentoom Community is built upon 8 modular, decoupled Composer packages:

| Package | Version | Description |
|---|---|---|
| [`agentoom/core-main`](https://packagist.org/packages/agentoom/core-main) | `^0.9.0` | **Core Runtime**: Agent lifecycle, pipelines, multi-provider LLMs, MCP runtime, and credit accounting. |
| [`agentoom/core-chat-widget`](https://packagist.org/packages/agentoom/core-chat-widget) | `^0.8.0` | **Chat Widget**: Drop-in embeddable real-time streaming web chat and conversational forms. |
| [`agentoom/core-accessibility-widget`](https://packagist.org/packages/agentoom/core-accessibility-widget) | `^0.8.0` | **Accessibility**: WCAG 2.1 / ADA compliant toolbar (contrast, fonts, cursor aids). |
| [`agentoom/core-sentinel`](https://packagist.org/packages/agentoom/core-sentinel) | `^0.8.0` | **Security Buffer**: Human-in-the-loop authorization gates and Email OTP verification for risky tools. |
| [`agentoom/core-compliance`](https://packagist.org/packages/agentoom/core-compliance) | `^0.8.0` | **EU AI Act Engine**: Automated risk assessments, audit logs, and emergency shutdown switches. |
| [`agentoom/core-privacy`](https://packagist.org/packages/agentoom/core-privacy) | `^0.8.0` | **PII Protection**: Deterministic regex detection and reversible token pseudonymization. |
| [`agentoom/core-instant-messaging`](https://packagist.org/packages/agentoom/core-instant-messaging) | `^0.8.0` | **Messaging Bridge**: Native Telegram bot connector for multi-channel conversations. |
| [`agentoom/core-openapi-v1`](https://packagist.org/packages/agentoom/core-openapi-v1) | `^0.8.0` | **OpenAI Gateway**: OpenAI-compatible `/v1/chat/completions` API proxy for any SDK. |

---

## 🛠️ Composer Installation Profiles

This repository ships with two tailored Composer configurations:

### 1. Discovery / Developer Profile (`composer.json` / `composer.dev.json`)
The default profile. Ideal for developers testing tools, running benchmarks, or contributing:
- Includes testing suites (`pestphp/pest`, `mockery`), interactive CLI debugging (`laravel/pail`, `laravel/sail`), code analysis (`larastan`), and code style tools (`pint`).
- Supports hot-reload dev servers via `composer run dev`.

### 2. Production / Robust Profile (`composer.prod.json`)
Designed for operators, system administrators, and teams who want Agentoom in production without developer tooling:
- Stripped of all development dependencies.
- Pinned to exact stable versions.
- Optimized with authoritative classmaps and bytecode caching (`optimize-autoloader`, `classmap-authoritative`).

#### How to Switch Profiles:
```bash
# Switch to Production Profile:
composer run use-prod
composer install --no-dev

# Switch back to Discovery/Dev Profile:
composer run use-dev
composer install
```

---

## 💻 Manual Installation (Local PHP)

If you prefer to run directly on your host machine without Docker:

### Requirements
- **PHP 8.3 or 8.4** (with `pdo_sqlite` or `pdo_pgsql`, `intl`, `bcmath`, `curl`)
- **Composer 2.2+**
- **Node.js 20+** & npm

### Installation Steps
```bash
# 1. Clone and enter directory
git clone https://github.com/agentoom/agentoom.git
cd agentoom

# 2. Automated setup script
composer run setup

# 3. Start development server
composer run dev
```

---

## ⚙️ Environment Configuration

Copy `.env.example` to `.env` and configure your AI providers:

```dotenv
# Primary LLM API Keys
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-ant-...
GEMINI_API_KEY=AIza...
GROQ_API_KEY=gsk_...
DEEPSEEK_API_KEY=sk-...

# Local Ollama instance
OLLAMA_BASE_URL=http://localhost:11434

# Realtime WebSocket Streaming (Laravel Reverb)
REVERB_APP_ID=agentoom
REVERB_APP_KEY=agentoom-key
REVERB_APP_SECRET=agentoom-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

---

## 🐳 Pre-Built Docker Images

Pre-built Docker images are published automatically to the GitHub Container Registry on every release:

```bash
docker pull ghcr.io/agentoom/community-core:latest
```

---

## 📄 License

Agentoom Community Edition is open-source software licensed under the **[MIT License](LICENSE)**.
