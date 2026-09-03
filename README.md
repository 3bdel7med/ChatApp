# Nexus - Real-Time Communication & AI-Powered Messaging Ecosystem

**Nexus** is an enterprise-grade, event-driven real-time chat, group collaboration, and media-streaming application built using **Laravel 11**, **Laravel Reverb (WebSockets)**, **Redis Queues**, **WebRTC**, and the **Gemini AI SDK**. Engineered with modern backend best practices, Nexus handles asynchronous background jobs, real-time event broadcasting, low-latency signaling, and intelligent automated response generation.

---

## 🚀 Key Features & Architecture Overview

| Feature Category | Technical Implementation | Key Components |
| :--- | :--- | :--- |
| **Authentication & Presence** | Secure multi-device authentication with online/offline status tracking and dynamic presence broadcasting upon login/logout. | Laravel Sanctum / Session Guards, Broadcast Presence Channels |
| **1-on-1 & Group Chats** | Direct messaging alongside multi-user group channels with dynamic role/permission handling, dynamic badges, and instant typing indicators. | Private/Presence Channels, Database Transactions |
| **Real-Time Notifications** | Asynchronous event firing pushing low-latency updates (unread counts, message delivery, status updates) straight to WebSocket clients. | Laravel Reverb, Pusher Protocol, Echo, Redis Pub/Sub |
| **Background Jobs & Queues** | Decoupled long-running processes (media processing, AI queries, notification delivery) handled by asynchronous queue workers. | Laravel Queues, Redis, Horizon Execution |
| **Audio & Video Calling** | Low-latency P2P audio and video calls with custom WebSocket signaling for SDP offer/answer exchanges and ICE candidates. | WebRTC Native APIs, Custom WebSocket Signaling Events |
| **Media & Attachments** | Secure file upload pipelines with automatic image optimization, chunked uploads, and web-friendly audio recording conversions. | Laravel Storage (S3 / Local), FFmpeg, File System Events |
| **AI Integration (Gemini)** | Automated AI response agents integrated seamlessly into chat threads via background queue workers to keep request cycles instant. | Google Gemini PHP SDK, Asynchronous AI Jobs |

---

## 🏗️ System Architecture & Event-Driven Workflow

1. **Request Execution Flow:** Incoming user actions (e.g., sending a voice note or requesting an AI reply) execute controllers that write to the database and immediately trigger Laravel Events.
2. **Asynchronous Job Queueing:** Events dispatch queued jobs (e.g., `ProcessVoiceNoteJob`, `GenerateGeminiAiReplyJob`) directly to Redis.
3. **WebSocket Broadcasting:** Upon job completion, broadcasting events push payload updates to WebSocket channels via **Laravel Reverb** without slowing down HTTP responses.
4. **WebRTC Peer Connection:** Calls execute initial handshake signals via WebSocket channels before establishing direct P2P media streams between clients.

---

## ⚡ Prerequisites & Requirements

- **PHP**: >= 8.2 with extensions: `mbstring`, `pdo`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- **Composer**: 2.x
- **Node.js**: >= 18.x & NPM / Yarn
- **Database**: MySQL 8.x / PostgreSQL
- **In-Memory Store**: Redis Server (for Queues, Caching, and Session management)

---

## 🛠️ Installation & Setup Guide

### 1. Clone the Repository & Install Dependencies
```bash
git clone [https://github.com/your-username/nexus-chat.git](https://github.com/your-username/nexus-chat.git)
cd nexus-chat

# Install Backend Dependencies
composer install

# Install Frontend Dependencies
npm install
