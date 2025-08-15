/**
 * Service Worker for Umuganda Digital Push Notifications
 *
 * This service worker handles:
 * - Push notification reception
 * - Notification click handling
 * - Background sync for offline actions
 */

const CACHE_NAME = "umuganda-digital-v1";
const APP_URL = "http://localhost"; // Update for production

// Install event
self.addEventListener("install", (event) => {
  console.log("Service Worker installing...");
  self.skipWaiting();
});

// Activate event
self.addEventListener("activate", (event) => {
  console.log("Service Worker activating...");
  event.waitUntil(self.clients.claim());
});

// Push event - Handle incoming push notifications
self.addEventListener("push", (event) => {
  console.log("Push notification received:", event);

  let notificationData = {};

  try {
    notificationData = event.data ? event.data.json() : {};
  } catch (e) {
    console.error("Error parsing push data:", e);
    notificationData = {
      title: "New Notification",
      body: "You have a new notification from Umuganda Digital",
      icon: "/assets/images/icons/default-icon.png",
    };
  }

  const options = {
    body: notificationData.body,
    icon: notificationData.icon || "/assets/images/icons/default-icon.png",
    badge: notificationData.badge || "/assets/images/badge-96x96.png",
    image: notificationData.image,
    tag: notificationData.tag || "umuganda-notification",
    data: notificationData.data || {},
    actions: notificationData.actions || [
      { action: "view", title: "View" },
      { action: "dismiss", title: "Dismiss" },
    ],
    vibrate: notificationData.vibrate || [200],
    requireInteraction: notificationData.requireInteraction || false,
    timestamp: Date.now(),
  };

  event.waitUntil(
    self.registration.showNotification(notificationData.title, options)
  );
});

// Notification click event
self.addEventListener("notificationclick", (event) => {
  console.log("Notification clicked:", event);

  const notification = event.notification;
  const action = event.action;
  const data = notification.data || {};

  notification.close();

  // Handle notification actions
  if (action === "dismiss") {
    // Just close the notification
    return;
  }

  // Determine the URL to open
  let urlToOpen = data.url || APP_URL + "/notifications";

  // Handle specific actions
  switch (action) {
    case "view":
      urlToOpen = data.url || APP_URL + "/notifications";
      break;
    case "pay":
      urlToOpen = APP_URL + "/fines/" + (data.fine_id || "");
      break;
    case "acknowledge":
      // Mark as acknowledged via API
      markNotificationAsRead(data.notificationId);
      urlToOpen = data.url || APP_URL + "/notifications";
      break;
    default:
      // Default click (no action button)
      urlToOpen = data.url || APP_URL + "/notifications";
  }

  // Open the appropriate page
  event.waitUntil(openNotificationUrl(urlToOpen, data.notificationId));
});

// Helper function to open notification URL
async function openNotificationUrl(url, notificationId) {
  try {
    // Get all open windows
    const clientList = await self.clients.matchAll({
      type: "window",
      includeUncontrolled: true,
    });

    // Check if app is already open
    for (const client of clientList) {
      if (client.url.startsWith(APP_URL) && "focus" in client) {
        // Focus existing window and navigate to URL
        await client.focus();
        await client.navigate(url);

        // Mark notification as read
        if (notificationId) {
          markNotificationAsRead(notificationId);
        }
        return;
      }
    }

    // Open new window if app is not open
    const newClient = await self.clients.openWindow(url);

    // Mark notification as read
    if (notificationId) {
      markNotificationAsRead(notificationId);
    }
  } catch (error) {
    console.error("Error opening notification URL:", error);
  }
}

// Helper function to mark notification as read
function markNotificationAsRead(notificationId) {
  if (!notificationId) return;

  // Send API request to mark notification as read
  fetch(APP_URL + "/api/notifications/" + notificationId + "/read", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    credentials: "include",
  }).catch((error) => {
    console.error("Failed to mark notification as read:", error);
  });
}

// Background sync for offline actions
self.addEventListener("sync", (event) => {
  console.log("Background sync triggered:", event.tag);

  if (event.tag === "notification-actions") {
    event.waitUntil(syncNotificationActions());
  }
});

// Sync notification actions when back online
async function syncNotificationActions() {
  try {
    // Get pending actions from IndexedDB
    const pendingActions = await getPendingActions();

    for (const action of pendingActions) {
      try {
        await fetch(action.url, {
          method: action.method,
          headers: action.headers,
          body: action.body,
          credentials: "include",
        });

        // Remove from pending actions
        await removePendingAction(action.id);
      } catch (error) {
        console.error("Failed to sync action:", error);
      }
    }
  } catch (error) {
    console.error("Background sync failed:", error);
  }
}

// IndexedDB helpers for offline actions
async function getPendingActions() {
  // Simplified implementation - in real app, use IndexedDB
  return JSON.parse(localStorage.getItem("pendingNotificationActions") || "[]");
}

async function removePendingAction(actionId) {
  const actions = await getPendingActions();
  const filtered = actions.filter((action) => action.id !== actionId);
  localStorage.setItem("pendingNotificationActions", JSON.stringify(filtered));
}

// Message handling for communication with main app
self.addEventListener("message", (event) => {
  console.log("Service Worker received message:", event.data);

  const { type, payload } = event.data;

  switch (type) {
    case "SKIP_WAITING":
      self.skipWaiting();
      break;
    case "GET_VERSION":
      event.ports[0].postMessage({ version: CACHE_NAME });
      break;
    case "CLEAR_NOTIFICATIONS":
      clearAllNotifications();
      break;
  }
});

// Clear all notifications
async function clearAllNotifications() {
  try {
    const notifications = await self.registration.getNotifications();
    notifications.forEach((notification) => notification.close());
    console.log("All notifications cleared");
  } catch (error) {
    console.error("Failed to clear notifications:", error);
  }
}
