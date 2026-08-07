# TODO - Fix: Missing message input + chat not loading

## Root Causes
1. `ChatController::showConversation()` doesn't pass the `conversations` prop to `Chat/Show`, so the sidebar conversation list is empty and the chat page appears broken/not loaded.
2. The `Chat/Show` page root uses `h-full` inside a flex container, which can fail to resolve and causes the message input to be clipped (pushed below the visible area) by `<main>`'s `overflow-hidden`.
3. `resources/js/Pages/Chat/Show.vue` had been truncated/corrupted — the message input form, attachment preview, message body display, and CallPopup component were missing.

## Steps
- [x] In `app/Http/Controllers/ChatController.php`: pass `'conversations' => $this->getAuthUserConversations()` in `showConversation()`.
- [x] In `resources/js/Pages/Chat/Show.vue`: change the root div from `h-full` to `flex-1 min-h-0` so it fills the flex layout correctly.
- [x] In `resources/js/Pages/Chat/Show.vue`: add `min-h-0` to the messages container so it shrinks correctly and the input stays visible.
- [x] Restore the truncated `Chat/Show.vue`: message body display, attachment preview bar, message input form, and CallPopup component.
- [x] Rebuild and test.
