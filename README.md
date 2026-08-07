# ChatApp — Real-time Chat with Voice & Video Calls

A full-featured real-time messaging application built with **Laravel 12**, **Inertia.js**, **Vue 3**, **Tailwind CSS**, and **TypeScript**. It supports direct and group conversations, file/image attachments, real-time notifications, and WebRTC-based **voice & video calls** with WebSocket signaling via **Laravel Reverb**.

---

## ✨ Features

- **Direct Messages** — One-to-one conversations with real-time delivery.
- **Group Chats** — Create groups with multiple participants; group messaging and notifications.
- **Real-time Updates** — Messages and calls are delivered instantly via WebSockets (Laravel Reverb + Laravel Echo).
- **File & Image Attachments** — Send images, PDFs, DOC/DOCX, ZIP, and more (stored in the public disk).
- **Voice Calls** — WebRTC audio calls with mute/unmute and speaker controls.
- **Video Calls** — WebRTC video calls with a picture-in-picture local preview, camera toggle, and remote full-screen video.
- **Call UI** — Incoming call popup, ringing tone (Web Audio API), ringback tone, call duration timer, and accept/reject/end controls.
- **Notifications** — In-app notifications for new messages.
- **User Search** — Search for users and start conversations.
- **Authentication** — Laravel Breeze (register, login, email verification, password reset, profile management).

---

## 🧰 Tech Stack

| Layer      | Technology |
|------------|------------|
| Backend    | PHP 8.2+, Laravel 12 |
| Frontend   | Vue 3, Inertia.js, Tailwind CSS 3, TypeScript |
| Real-time  | Laravel Reverb, Laravel Echo, Pusher protocol |
| WebRTC     | Native browser WebRTC (`RTCPeerConnection`, `getUserMedia`) |
| Database   | MySQL / SQLite (default) |
| Auth       | Laravel Breeze |
| Build      | Vite 7, `vue-tsc` |

---

## 📁 Project Structure

```
├── app/
│   ├── Events/
│   │   ├── CallEvent.php          # WebSocket signal for calls (offer/answer/ICE/end)
│   │   └── MessageSent.php        # Broadcasts new messages
│   ├── Http/Controllers/
│   │   ├── ChatController.php     # Conversations, groups, messages, media
│   │   ├── CallController.php     # WebRTC signaling endpoint
│   │   ├── SearchController.php   # User search
│   │   ├── NotificationController.php
│   │   └── UserController.php     # Public user profiles
│   ├── Models/
│   │   ├── Conversation.php       # Direct & group conversations
│   │   ├── Message.php            # Messages with attachments
│   │   └── User.php
│   └── Notifications/
│       └── NewChatMessage.php
├── resources/js/
│   ├── Components/
│   │   └── CallPopup.vue          # Incoming/active call UI (voice & video)
│   ├── composables/
│   │   └── useWebRTC.ts           # WebRTC logic: streams, signaling, timers
│   ├── Pages/
│   │   ├── Chat/
│   │   │   ├── Index.vue          # Conversation list + search
│   │   │   └── Show.vue           # Chat window with call buttons
│   │   └── User/Show.vue          # User profile
│   ├── echo.ts                    # Laravel Echo (Reverb) config
│   └── app.ts
├── routes/
│   ├── web.php                    # Chat/call/notification routes
│   └── channels.php               # Private channel authorization
└── config/reverb.php              # Reverb WebSocket server config
```

---

## 🚀 Installation

### Prerequisites
- **PHP** 8.2+
- **Composer**
- **Node.js** 18+ and npm
- A database (MySQL recommended; SQLite works out of the box)

### 1. Clone & Install Dependencies

```bash
git clone <your-repo-url> ChatApp
cd ChatApp

composer install
npm install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatapp
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Configure Reverb (WebSockets)

Set the Reverb credentials in `.env`. The app key/secret/scheme must match between the server and the frontend:

```
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME=http
```

Frontend env vars (`resources/js/echo.ts` reads these). In `.env` (or a `.env.local` for Vite):

```
VITE_REVERB_APP_KEY=your-app-key
VITE_REVERB_HOST="127.0.0.1"
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

> **Important:** For video calls to work, the app must be served over **HTTPS** (or `localhost`). Browsers block camera/microphone access on insecure origins. Use a tool like `ngrok`, `Laravel Herd`, or a local HTTPS proxy when testing with a real camera.

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Storage Link (for file attachments)

```bash
php artisan storage:link
```

### 6. Build the Frontend

```bash
npm run build
```

---

## ▶️ Running the App

### Development (recommended)

Run the server, the Reverb WebSocket server, the queue worker, and Vite together:

```bash
composer run dev
```

This starts (via `concurrently`):
- `php artisan serve` — Laravel HTTP server (`http://localhost:8000`)
- `php artisan queue:listen` — queue worker for notifications
- `php artisan pail` — live logs
- `npm run dev` — Vite dev server with HMR

### Running processes individually

```bash
# Terminal 1 — Laravel server
php artisan serve

# Terminal 2 — Reverb WebSocket server
php artisan reverb:start

# Terminal 3 — Queue worker (notifications)
php artisan queue:listen --tries=1 --timeout=0

# Terminal 4 — Vite dev server
npm run dev
```

> **Note:** Reverb is required for real-time messaging and call signaling. If Reverb isn't running, messages won't broadcast and calls won't connect.

---

## 📞 How the Calls Work

1. **Caller** clicks the voice/video call button → `startCall(video)` in `useWebRTC.ts` requests camera/mic via `getUserMedia`, creates a `RTCPeerConnection`, and sends an **offer** (with a `video` flag) through the `CallController` to the Reverb channel.
2. **Receiver** gets the offer via `CallEvent` → an incoming call popup appears with a ringing tone.
3. **Receiver accepts** → `acceptCall()` requests media, creates an answer, and sends it back. The call timer starts on both sides when the answer is exchanged.
4. **ICE candidates** are exchanged over the same channel to establish the peer connection.
5. Once connected, each side assigns the incoming track to `remoteStream` and displays it in `CallPopup.vue`.

### Key files
- `resources/js/composables/useWebRTC.ts` — WebRTC state machine, signaling, timers, mute/camera toggles.
- `resources/js/Components/CallPopup.vue` — Call UI (incoming, active voice, active video).
- `app/Http/Controllers/CallController.php` — Validates and broadcasts signaling events.
- `app/Events/CallEvent.php` — WebSocket event for call signals.

---

## 🔌 API / Routes

### Chat
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/chat` | Conversation list |
| POST | `/chat/start` | Create or get a direct conversation |
| POST | `/chat/group` | Create a group |
| GET | `/chat/{conversation}` | Show a conversation & messages |
| POST | `/chat/{conversation}/messages` | Send a message (with optional file) |
| GET | `/chat/users/search` | Search users |
| GET | `/chat/users/list` | List users for group creation |

### Calls
| Method | URI | Description |
|--------|-----|-------------|
| POST | `/chat/{conversation}/call/signal` | Send WebRTC signaling (offer/answer/ice-candidate/end-call) |

### Notifications
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/notifications` | List notifications |
| POST | `/notifications/{notification}/read` | Mark one as read |
| POST | `/notifications/read-all` | Mark all as read |

---

## 🗄️ Database Schema

- **users** — app users (Breeze default).
- **conversations** — `type` (`direct`/`group`), `name` (groups), `sender_id`, `receiver_id`, `last_message_at`.
- **conversation_user** — pivot table linking users to group conversations.
- **messages** — `body`, `file_path`, `file_name`, `file_type`, `read_at`; linked to conversations/users.
- **notifications** — Laravel's notification table.

---

## 🧪 Testing

```bash
composer test
```

---

## 🛠️ Common Commands

```bash
# Build frontend (type-check + bundle)
npm run build

# Vite dev server with HMR
npm run dev

# Start Reverb WebSocket server
php artisan reverb:start

# Format PHP code
vendor/bin/pint
```

---

## 📄 License

This project is open-sourced under the [MIT license](https://opensource.org/licenses/MIT).
