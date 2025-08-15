## Notification System Implementation Guide

Audience: AI coding agent (and developers) implementing full notification stack (in-app, email, web push) for Umuganda Digital.

Goal: End-to-end, idempotent, testable system enabling creation, queuing, delivery, read-tracking and user preference management of notifications.

---

## Notification Use Cases (Scenarios Catalogue)

Grouped by functional category. Each scenario maps to a `(category, type)` pair and suggested default channels (InApp always on; others per preference unless marked Critical).

1. Registration & Onboarding

- (system, user_registered): Successful account registration (Channels: inapp,email)
- (system, registration_pending_approval): Awaiting admin approval (inapp,email)
- (system, registration_approved): Account approved (inapp,email,push)
- (system, registration_rejected): Account rejected with reason (inapp,email)
- (system, email_verification_required): Prompt to verify email (email only, Critical until verified)
- (system, welcome_message): Post-verification welcome (inapp,email)

2. Account & Security

- (system, password_changed): Password successfully changed (inapp,email)
- (system, password_change_failed): Attempt failed / mismatch (inapp)
- (system, suspicious_login): Login from new device / location (email,push Critical)
- (system, two_factor_enabled): 2FA enabled confirmation (inapp,email)
- (system, two_factor_disabled): 2FA disabled (email)
- (system, profile_updated): Profile details updated (inapp)

3. Attendance & Umuganda Events

- (attendance, attendance_recorded): Individual attendance marked (inapp,push,email)
- (attendance, attendance_correction): Attendance corrected / updated (inapp,email)
- (attendance, attendance_missed): Missed required attendance (inapp,email)
- (attendance, monthly_attendance_summary): Monthly participation summary (inapp,email)
- (event, event_created): New Umuganda event published in user area (inapp,push,email)
- (event, event_updated): Date/time/location changed (inapp,push,email)
- (event, event_cancelled): Event cancelled (inapp,push,email Critical)
- (event, event_reminder_24h): 24-hour reminder (inapp,push,email optional)
- (event, event_reminder_1h): 1-hour reminder (push only default)
- (event, event_started): Event start notification (push)
- (event, event_feedback_request): Post-event feedback survey (inapp,email)

4. Fines & Payments

- (fine, fine_issued): New fine issued (inapp,email,push)
- (fine, fine_updated): Fine amount or status updated (inapp,email)
- (fine, fine_overdue_reminder): Overdue fine reminder (inapp,email,push)
- (fine, fine_waived): Fine waived / cancelled (inapp,email)
- (payment, payment_initiated): Payment process started (inapp)
- (payment, payment_success): Payment successful (inapp,email,push)
- (payment, payment_failed): Payment failure (inapp,email,push)
- (payment, payment_refunded): Refund issued (inapp,email)
- (payment, payment_receipt): Receipt available / downloadable (email,inapp)

5. Notices & Announcements

- (announcement, new_notice): New community notice (inapp,push,email)
- (announcement, notice_updated): Notice edited (inapp)
- (announcement, notice_expiring_soon): Notice near expiry (inapp)
- (announcement, system_announcement): Platform-wide announcement (inapp,email,push optional)
- (announcement, emergency_alert): Urgent community alert (inapp,push,email Critical)

6. Location & Administration

- (system, location_reassigned): User moved to new cell/sector/district (inapp,email)
- (system, new_local_admin_assigned): New local admin for user’s location (inapp)
- (system, admin_role_granted): Elevated privileges granted (inapp,email)
- (system, admin_role_revoked): Admin privileges revoked (email)

7. QR Code & Identity

- (system, qr_code_generated): QR code first generated (inapp)
- (system, qr_code_regenerated): QR code re-generated (inapp)

8. Data & Reports

- (report, monthly_report_ready): Monthly report available (inapp,email)
- (report, export_ready): Requested data export ready for download (inapp,email,push)
- (report, export_failed): Export failed (inapp,email)

9. Preferences & Communication

- (system, preferences_updated): Notification preferences changed (inapp)
- (system, channel_disabled_due_to_errors): Email/push auto-disabled after repeated failures (inapp,email)
- (system, reenable_channel_confirmation): Channel re-enabled (inapp)
- (system, unsubscribe_confirmation): Email unsubscribe processed (email)

10. Push Subscription Lifecycle

- (system, push_subscription_confirmed): Subscription stored successfully (inapp)
- (system, push_subscription_expired): Subscription expired / must renew (inapp,push attempted)

11. Maintenance & System Health

- (system, scheduled_maintenance): Upcoming maintenance window (inapp,email)
- (system, maintenance_started): Maintenance in progress (inapp)
- (system, maintenance_completed): Maintenance completed (inapp)
- (system, feature_update): New feature / release notes (inapp,email optional)

12. Delivery & Reliability

- (system, notification_delivery_failed): Optional aggregated digest to admins of failed notifications (email)
- (system, high_failure_rate_alert): Alert to admin when channel failure threshold exceeded (email,push)

13. Feedback & Engagement

- (system, feedback_request): General feedback survey invitation (inapp,email)
- (system, feedback_thanks): Acknowledgment of submitted feedback (inapp)

14. Misc / Other

- (other, generic_info): Generic informational message (inapp)
- (other, action_required): User action required (inapp,email,push) – treat as high priority

Notes:

- Categories: attendance, event, fine, payment, announcement, system, report, other.
- Critical types bypass some user channel preferences only if legally required or platform-critical (define allowlist: emergency_alert, suspicious_login, event_cancelled).
- Each `(category,type)` pair will be enumerated in a constant map in `NotificationService` for validation and default channel suggestions.

---

## 0. Architectural Overview

Components:

1. Data layer (tables + indices)
2. Domain layer (NotificationService, Channel senders)
3. Delivery workers (cron/CLI script) – pulls pending channel jobs
4. API endpoints (subscribe push, list notifications, mark read, preferences)
5. Front-end integration (service worker, subscription bootstrap, UI listing, toasts)
6. Email templates (basic HTML wrapper)
7. User preferences management UI
8. Logging & retry logic

Channels supported: inapp (DB only), email (PHPMailer), push (Web Push / VAPID).

---

## 1. Prerequisites & Conventions

Language: PHP (no framework). Autoload via Composer once introduced.

Add Composer if absent:

```
composer init  # if composer.json not yet present
composer require phpmailer/phpmailer minishlink/web-push
```

Directory conventions to add:

```
src/
  services/NotificationService.php
  channels/EmailChannel.php
  channels/PushChannel.php
  channels/InAppChannel.php
  repositories/NotificationRepository.php
  repositories/PreferenceRepository.php
  helpers/NotificationHelpers.php
public/
  sw.js                # service worker
  cron/send_notifications.php
  api/notifications/*.php  # endpoints
  api/push/*.php           # push endpoints
resources/emails/layout.php
resources/emails/<template>.php
```

Environment variables file: `.env` (if not present, create) loaded early in bootstrap.

Required env keys:

```
SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
MAIL_FROM=notifications@example.com
MAIL_FROM_NAME=Umuganda Digital

VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
VAPID_SUBJECT=mailto:admin@example.com
```

Generate VAPID pair (one-off): use WebPush library helper script (add a /scripts/generate_vapid.php or run externally) then store keys in .env.

---

## 2. Database Schema Additions

Check the database schema: `C:\Users\user\Desktop\Apps\App_work\umuganda-digital\database_schema_for_umuganda_digital.sql`. Everything in there is in the database including the below additional tables

SQL (idempotent):

```sql
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  title VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  type VARCHAR(50) NOT NULL,
  category ENUM('attendance','event','fine','announcement','system','other') DEFAULT 'other',
  priority ENUM('low','normal','high','critical') DEFAULT 'normal',
  data LONGTEXT NULL CHECK (JSON_VALID(data)),
  status ENUM('pending','queued','sent','failed') DEFAULT 'pending',
  error_message TEXT NULL,
  sent_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_notifications_user (user_id, created_at),
  INDEX idx_notifications_type (type),
  INDEX idx_notifications_status (status),
  INDEX idx_notifications_category (category),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notification_channels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notification_id INT NOT NULL,
  channel ENUM('email','push','inapp') NOT NULL,
  status ENUM('pending','sent','failed','skipped') DEFAULT 'pending',
  attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  last_error TEXT NULL,
  attempted_at TIMESTAMP NULL DEFAULT NULL,
  sent_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_nc_status (status),
  INDEX idx_nc_channel (channel),
  CONSTRAINT fk_nc_notification FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
  UNIQUE KEY unique_notification_channel (notification_id, channel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_notification_preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  attendance_email TINYINT(1) DEFAULT 1,
  attendance_push TINYINT(1) DEFAULT 1,
  attendance_inapp TINYINT(1) DEFAULT 1,
  event_email TINYINT(1) DEFAULT 1,
  event_push TINYINT(1) DEFAULT 1,
  event_inapp TINYINT(1) DEFAULT 1,
  fine_email TINYINT(1) DEFAULT 1,
  fine_push TINYINT(1) DEFAULT 1,
  fine_inapp TINYINT(1) DEFAULT 1,
  announcement_email TINYINT(1) DEFAULT 1,
  announcement_push TINYINT(1) DEFAULT 1,
  announcement_inapp TINYINT(1) DEFAULT 1,
  system_email TINYINT(1) DEFAULT 1,
  system_push TINYINT(1) DEFAULT 1,
  system_inapp TINYINT(1) DEFAULT 1,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_unp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_unp_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS push_subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  endpoint TEXT NOT NULL,
  endpoint_hash CHAR(64) NOT NULL,
  p256dh VARCHAR(255) NOT NULL,
  auth VARCHAR(255) NOT NULL,
  user_agent VARCHAR(255) NULL,
  is_active TINYINT(1) DEFAULT 1,
  last_failure_at TIMESTAMP NULL DEFAULT NULL,
  revoked_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ps_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_endpoint_hash (endpoint_hash),
  INDEX idx_ps_user (user_id, is_active),
  INDEX idx_ps_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notification_reads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  notification_id INT NOT NULL,
  user_id INT NOT NULL,
  read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_notification_user (notification_id, user_id),
  INDEX idx_nr_user (user_id, read_at),
  CONSTRAINT fk_nr_notification FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
  CONSTRAINT fk_nr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Backfill preferences for existing users (one-off SQL):

```sql
INSERT INTO user_notification_preferences (user_id)
SELECT u.id FROM users u
LEFT JOIN user_notification_preferences p ON p.user_id = u.id
WHERE p.user_id IS NULL;
```

---

## 3. Domain Model & Classes

Interfaces (conceptual):

```
ChannelSender {
  send(array $notification, array $channelRow): ChannelResult
}
```

ChannelResult fields: `success (bool)`, `error (string|null)`.

Classes:

1. `NotificationRepository` – CRUD & fetch pending channel jobs.
2. `PreferenceRepository` – read/update user preferences.
3. `NotificationService` – create notifications, resolve channels by preferences, enqueue channel rows, mark read.
4. `EmailChannel`, `PushChannel`, `InAppChannel` – implement sending logic.
5. `NotificationHelpers` – utility (JSON encode safe, etc.).

Resolution logic mapping category+channel flags from `user_notification_preferences`.

---

## 4. Creating a Notification (Flow)

1. Business event occurs (e.g., attendance marked).
2. Call `NotificationService::notifyUser($userId, $category, $type, $title, $body, $data, $priority)`.
3. Service writes `notifications` row (status=pending).
4. Service determines enabled channels based on preferences & priority (e.g., critical ignores opt-out except legal categories—configurable constant array).
5. Insert rows in `notification_channels` for each channel (pending).
6. Optionally set `notifications.status='queued'` after channel rows inserted.

Broadcast: pass `$userId = null`; service selects all users (or chunked) building separate notifications OR a single `notifications` row with `user_id NULL` plus channel rows and then the worker fans out (choose approach A below).

Simpler Approach A (initial): create per-user notifications for broadcast by chunking (avoid complicated fan-out logic). Keep memory low by batching.

---

## 5. Worker / Dispatcher Script

File: `public/cron/send_notifications.php` (executable via CLI / Task Scheduler).

Algorithm:

1. Acquire small advisory lock (e.g., create a `.lock` file or GET_LOCK()) to prevent overlap.
2. Fetch up to N (e.g., 100) `notification_channels` with status='pending'. ORDER BY id ASC.
3. For each: load parent notification.
4. Instantiate appropriate channel sender; attempt send.
5. Update `notification_channels` (status sent|failed, attempts++, last_error, attempted_at, sent_at).
6. If all channel rows for a notification are final (sent/failed/skipped) update parent `notifications.status` = 'sent' if any success else 'failed'. If at least one success and one failed, still treat as 'sent' (error_message aggregated optional).
7. Retry policy: if failed and attempts < MAX_ATTEMPTS (e.g., 3) leave status 'pending' after small delay logic (store attempts + schedule by ignoring until NOW() > created_at + attempt_backoff(attempts)). Minimal initial implementation: straight retries each run.

---

## 6. Channel Senders

EmailChannel:

- Use PHPMailer; configure SMTP from env.
- Input: notification row.
- Template resolution: choose template by category or default.
- Fallback plain text if HTML fails.

PushChannel:

- Use Minishlink/WebPush.
- Fetch all active subscriptions for user (if none -> mark skipped success?).
- Payload JSON: `{title, body, data:{notification_id, type, url?}}` size < 3800 bytes.
- Send; handle expired endpoints (410/404) -> mark subscription inactive.

InAppChannel:

- No external action; success immediate.

---

## 7. API Endpoints

Base Path: `public/api/notifications/` and `public/api/push/`.

1. `GET /api/notifications/list.php` – params: `status=unread|all`, pagination (page, per_page). Returns JSON list with read flag.
2. `POST /api/notifications/mark-read.php` – body: JSON {ids:[...]} or {all:true}.
3. `GET /api/notifications/unread-count.php` – returns count for badge.
4. `POST /api/push/subscribe.php` – body: subscription JSON from `PushManager.subscribe()`. Store (hash endpoint) upsert.
5. `POST /api/push/unsubscribe.php` – mark subscription revoked (requires endpoint hash or id).
6. `GET /api/push/vapid-key.php` – returns `{publicKey: <base64Key>}`.
7. `GET /api/preferences/notification.php` – returns preference flags.
8. `POST /api/preferences/notification.php` – updates flags (whitelisted keys only).

Security: ensure user session; sanitize; CSRF token (optional initial skip if session only and POST via AJAX same-site).

---

## 8. Front-End Integration

Service Worker `public/sw.js`:

- push event -> showNotification
- notificationclick -> focus or open dashboard

Initialization Script (e.g., `public/assets/js/push-init.js`):

- On DOM ready, call `initPush()` (permissions and subscription logic) if user enabled push in preferences (or default).
- Provide `unsubscribePush()`.

Notifications UI:

- Dropdown/badge in top nav (poll `unread-count` every 60s until push fully used to update in real time; push event handler also updates count via client focus message).
- Listing page with pagination, mark read button.
- Toast: when push payload or fallback polling returns new items, display ephemeral toast (title, snippet) linking to relevant page.

---

## 9. User Preferences UI

Location: `public/dashboard/resident/notification-preferences.php` (and admin variant if needed).

Display grouped checkboxes matrix: categories vs channels.

POST updates via API endpoint or direct form handler calling repository update function.

---

## 10. Email Templates

Base layout `resources/emails/layout.php` containing header/footer.
Per-category templates optional; else dynamic substitution into layout.
Placeholders: `{{title}}`, `{{body}}`, `{{footer_manage_link}}`.

Manage preferences link -> preferences page.

---

## 11. Utility & Helper Details

Environment Loader (if not existing) – parse `.env` into `$_ENV` or global config array early (e.g., in `config/bootstrap.php`).

Function `env($key, $default = null)` helper recommended.

Function `db()` returns mysqli connection (already exists via `config/db.php`). Reuse.

JSON safety: use `json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)`.

---

## 12. Error Handling & Logging

Add log file: `storage/logs/notifications.log` (create directory if needed) – append lines with timestamp, channel, notification_id, status, message.

Worker updates `notification_channels.last_error` on exceptions.

Aggregate severe errors (>= N failures) -> optional admin report later.

---

## 13. Testing Strategy (Minimal)

Add a lightweight PHP script `scripts/test_notification_flow.php` that:

1. Creates a dummy user (or uses existing id=1)
2. Calls NotificationService to enqueue a test notification
3. Runs the worker once (include worker script function rather than spawning separate process)
4. Asserts DB rows updated to sent for inapp channel

Manual tests:

- Email: verify message received (use mailhog or real SMTP)
- Push: open devtools > Application > Service Workers – send test push (simulate by creating notification, run worker)

---

## 14. Step-by-Step Execution Plan (Phased Tasks)

Phase 1 (Schema & Infrastructure):
[ ] Add migration SQL file and run it.
[ ] Backfill user preferences.
[ ] Commit.

Phase 2 (Composer & Autoload):
[ ] Add composer dependencies.
[ ] Ensure `vendor/autoload.php` included in global bootstrap.

Phase 3 (Domain Classes):
[ ] Implement repositories.
[ ] Implement NotificationService with `notifyUser` and `notifyMultiple(array $userIds, ...)`.
[ ] Basic InAppChannel.

Phase 4 (Worker):
[ ] Implement `send_notifications.php` script with lock & loop.
[ ] Schedule via OS (Task Scheduler every minute) – doc in README.

Phase 5 (Email Channel):
[ ] Implement EmailChannel using PHPMailer and HTML template.
[ ] Add preference enforcement.

Phase 6 (Push Channel):
[ ] Add VAPID keys (.env).
[ ] Implement PushChannel (batch flush).
[ ] Create subscription endpoints + sw.js + front-end init.

Phase 7 (API Endpoints & UI):
[ ] Implement notifications list, mark-read, unread-count.
[ ] Build preferences page + form.
[ ] Add nav badge & dropdown.

Phase 8 (Enhancements):
[ ] Add toast UI & polling fallback.
[ ] Add retry/backoff logic.
[ ] Add metrics (counts grouped by category per day).

Phase 9 (Cleanup & Docs):
[ ] Document environment variables.
[ ] Add developer usage section to main README.

---

## 15. Data Access Queries (Reference Snippets)

Fetch pending channels:

```sql
SELECT nc.*, n.* FROM notification_channels nc
JOIN notifications n ON n.id = nc.notification_id
WHERE nc.status='pending'
ORDER BY nc.id ASC
LIMIT 100;
```

Mark channel result:

```sql
UPDATE notification_channels
SET status=?, attempts=attempts+1, last_error=?, attempted_at=NOW(), sent_at=IF(?='sent', NOW(), sent_at)
WHERE id=?;
```

Mark notification aggregated:

```sql
UPDATE notifications n
JOIN (
  SELECT notification_id,
    SUM(status='sent') sent_count,
    SUM(status='failed') failed_count,
    COUNT(*) total
  FROM notification_channels
  WHERE notification_id=?
  GROUP BY notification_id
) agg ON n.id = agg.notification_id
SET n.status = CASE WHEN agg.sent_count>0 THEN 'sent' WHEN agg.failed_count=agg.total THEN 'failed' ELSE n.status END,
    n.error_message = CASE WHEN agg.failed_count>0 AND agg.sent_count=0 THEN 'All channel attempts failed' ELSE n.error_message END,
    n.sent_at = CASE WHEN agg.sent_count>0 AND n.sent_at IS NULL THEN NOW() ELSE n.sent_at END
WHERE n.id=?;
```

Unread list (with read status):

```sql
SELECT n.*, (nr.id IS NOT NULL) AS is_read
FROM notifications n
LEFT JOIN notification_reads nr ON nr.notification_id = n.id AND nr.user_id=?
WHERE (n.user_id=? OR n.user_id IS NULL)
ORDER BY n.created_at DESC
LIMIT ?, ?;
```

---

## 16. Service Worker Example (sw.js)

```js
self.addEventListener("push", (event) => {
  let data = {};
  try {
    data = event.data.json();
  } catch (e) {}
  const title = data.title || "Umuganda";
  const options = {
    body: data.body,
    data: data.data || {},
    icon: "/icons/icon-192.png",
    badge: "/icons/badge.png",
  };
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  const target = event.notification.data?.url || "/dashboard";
  event.waitUntil(
    clients
      .matchAll({ type: "window", includeUncontrolled: true })
      .then((list) => {
        for (const c of list) {
          if (c.url.includes(target) && "focus" in c) return c.focus();
        }
        return clients.openWindow(target);
      })
  );
});
```

---

## 17. Push Subscription Handling (Front-End Snippet)

```js
async function initPush() {
  if (!("serviceWorker" in navigator) || !("PushManager" in window)) return;
  const reg = await navigator.serviceWorker.register("/sw.js");
  const perm = await Notification.requestPermission();
  if (perm !== "granted") return;
  const keyResp = await fetch("/api/push/vapid-key.php");
  const { publicKey } = await keyResp.json();
  const subscription = await reg.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array(publicKey),
  });
  await fetch("/api/push/subscribe.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(subscription),
  });
}
```

Helper `urlBase64ToUint8Array` implement if not present.

---

## 18. Security Considerations

1. Validate session user for all notification & push endpoints.
2. Do not include sensitive PII in push payload (keep minimal; fetch full details on client if needed).
3. Rate limit preference updates & subscription endpoints (basic ip+user throttle optional).
4. Sanitize output (HTML-escape titles/bodies when rendering in-app; emails can trust internal content if curated).

---

## 19. Performance & Maintenance

1. Index review (already added indices for common filters).
2. Purge policy: archive or delete notifications older than X months (config constant). Scheduled script.
3. Batch push sending: collect notifications per run; reuse WebPush instance; flush once.
4. Limit worker run time (e.g., stop after 45s to prevent overlap).

---

## 20. Future Enhancements

1. Add template system (Blade-like or simple placeholders) with translation.
2. Add SMS channel (similar channel pattern).
3. Add event-driven queue (Redis) for real-time dispatch.
4. Add digest emails (daily summary) reducing email volume.
5. Add admin dashboard metrics page.

---

## 21. Completion Criteria Checklist

System considered MVP complete when:

- Tables exist & preferences backfilled
- NotificationService can enqueue multi-channel notifications
- Worker dispatches all three channel types (inapp immediate, push/email functioning)
- API: list, unread-count, mark-read, subscribe, vapid-key, preferences all return expected JSON
- Service worker displays push notifications
- UI shows unread count & listing; can mark read
- Preferences toggles affect subsequent notifications
- Basic tests script passes and manual email/push verified

---

## 22. Quick Start (Condensed Task Order)

1. Add migration & run
2. Composer require packages
3. Add env keys (SMTP + VAPID)
4. Implement service + repositories
5. Implement channels (start with InApp, then Email, then Push)
6. Add worker script & schedule
7. Add endpoints (vapid-key, subscribe, list, mark-read, unread-count, preferences)
8. Add service worker & front-end JS
9. Build preferences page
10. Integrate UI badge + polling fallback
11. Test end-to-end

---

## 23. Minimal Example (Pseudocode create & send)

```php
$service = new NotificationService();
$service->notifyUser($userId, 'attendance', 'attendance_confirmation', 'Attendance Recorded', 'Your attendance was recorded.', ['attendance_id' => 123]);
// Worker runs shortly and dispatches.
```

---

End of guide.
