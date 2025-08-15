/**
 * Push Notifications Manager for Umuganda Digital
 *
 * Handles:
 * - Service worker registration
 * - Push subscription management
 * - Notification permissions
 * - Communication with backend
 */

class PushNotificationManager {
  constructor() {
    this.vapidPublicKey =
      "BPKa9TZVI5iCL3Xx_rH2X3S0H-zs9jHPNh7VhPP2z8k9CjWOTFj4N8P-A5Zq6QEH8hGQlxLo3NjHy6Vx9F2rZMM";
    this.serviceWorkerUrl = "/sw.js";
    this.apiEndpoint = "/api/push-subscriptions";
    this.registration = null;
    this.subscription = null;
  }

  /**
   * Initialize push notifications
   */
  async init() {
    console.log("Initializing push notifications...");

    // Check if service workers are supported
    if (!("serviceWorker" in navigator)) {
      console.warn("Service workers not supported");
      return false;
    }

    // Check if push messaging is supported
    if (!("PushManager" in window)) {
      console.warn("Push messaging not supported");
      return false;
    }

    try {
      // Register service worker
      await this.registerServiceWorker();

      // Check current subscription status
      await this.checkSubscriptionStatus();

      console.log("Push notifications initialized successfully");
      return true;
    } catch (error) {
      console.error("Failed to initialize push notifications:", error);
      return false;
    }
  }

  /**
   * Register service worker
   */
  async registerServiceWorker() {
    try {
      this.registration = await navigator.serviceWorker.register(
        this.serviceWorkerUrl
      );
      console.log("Service worker registered:", this.registration);

      // Wait for service worker to be ready
      await navigator.serviceWorker.ready;
    } catch (error) {
      console.error("Service worker registration failed:", error);
      throw error;
    }
  }

  /**
   * Check current subscription status
   */
  async checkSubscriptionStatus() {
    if (!this.registration) return;

    try {
      this.subscription = await this.registration.pushManager.getSubscription();

      if (this.subscription) {
        console.log("Existing push subscription found");
        // Verify subscription with backend
        await this.verifySubscriptionWithBackend();
      } else {
        console.log("No existing push subscription");
      }
    } catch (error) {
      console.error("Failed to check subscription status:", error);
    }
  }

  /**
   * Request notification permission and subscribe
   */
  async requestPermissionAndSubscribe() {
    try {
      // Request permission
      const permission = await this.requestPermission();
      if (permission !== "granted") {
        throw new Error("Notification permission denied");
      }

      // Subscribe to push notifications
      await this.subscribeToPush();

      return true;
    } catch (error) {
      console.error("Failed to subscribe to push notifications:", error);
      throw error;
    }
  }

  /**
   * Request notification permission
   */
  async requestPermission() {
    if (!("Notification" in window)) {
      throw new Error("Notifications not supported");
    }

    let permission = Notification.permission;

    if (permission === "default") {
      permission = await Notification.requestPermission();
    }

    console.log("Notification permission:", permission);
    return permission;
  }

  /**
   * Subscribe to push notifications
   */
  async subscribeToPush() {
    if (!this.registration) {
      throw new Error("Service worker not registered");
    }

    try {
      // Convert VAPID key to Uint8Array
      const applicationServerKey = this.urlBase64ToUint8Array(
        this.vapidPublicKey
      );

      // Subscribe
      this.subscription = await this.registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey,
      });

      console.log("Push subscription successful:", this.subscription);

      // Send subscription to backend
      await this.sendSubscriptionToBackend();
    } catch (error) {
      console.error("Push subscription failed:", error);
      throw error;
    }
  }

  /**
   * Unsubscribe from push notifications
   */
  async unsubscribe() {
    if (!this.subscription) {
      console.log("No active subscription to unsubscribe");
      return true;
    }

    try {
      // Unsubscribe from push service
      await this.subscription.unsubscribe();
      console.log("Push subscription cancelled");

      // Remove subscription from backend
      await this.removeSubscriptionFromBackend();

      this.subscription = null;
      return true;
    } catch (error) {
      console.error("Failed to unsubscribe:", error);
      return false;
    }
  }

  /**
   * Send subscription to backend
   */
  async sendSubscriptionToBackend() {
    if (!this.subscription) {
      throw new Error("No subscription to send");
    }

    try {
      const subscriptionData = {
        endpoint: this.subscription.endpoint,
        keys: {
          p256dh: this.arrayBufferToBase64(this.subscription.getKey("p256dh")),
          auth: this.arrayBufferToBase64(this.subscription.getKey("auth")),
        },
      };

      const response = await fetch(this.apiEndpoint, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
        body: JSON.stringify(subscriptionData),
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const result = await response.json();
      console.log("Subscription sent to backend:", result);
    } catch (error) {
      console.error("Failed to send subscription to backend:", error);
      throw error;
    }
  }

  /**
   * Verify subscription with backend
   */
  async verifySubscriptionWithBackend() {
    // Implementation would check if subscription is still valid on backend
    console.log("Verifying subscription with backend...");
  }

  /**
   * Remove subscription from backend
   */
  async removeSubscriptionFromBackend() {
    try {
      const response = await fetch(this.apiEndpoint, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      console.log("Subscription removed from backend");
    } catch (error) {
      console.error("Failed to remove subscription from backend:", error);
      throw error;
    }
  }

  /**
   * Get current subscription status
   */
  getSubscriptionStatus() {
    return {
      supported: "serviceWorker" in navigator && "PushManager" in window,
      permission:
        "Notification" in window ? Notification.permission : "unsupported",
      subscribed: !!this.subscription,
      subscription: this.subscription,
    };
  }

  /**
   * Test notification (local)
   */
  async testNotification() {
    const permission = await this.requestPermission();
    if (permission !== "granted") {
      throw new Error("Permission required for test notification");
    }

    new Notification("Test Notification", {
      body: "This is a test notification from Umuganda Digital",
      icon: "/assets/images/icons/default-icon.png",
      badge: "/assets/images/badge-96x96.png",
    });
  }

  /**
   * Utility: Convert VAPID key to Uint8Array
   */
  urlBase64ToUint8Array(base64String) {
    const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
      .replace(/-/g, "+")
      .replace(/_/g, "/");

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }

  /**
   * Utility: Convert ArrayBuffer to Base64
   */
  arrayBufferToBase64(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = "";
    for (let i = 0; i < bytes.byteLength; i++) {
      binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
  }
}

// Export for use in other scripts
window.PushNotificationManager = PushNotificationManager;

// Auto-initialize if DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializePushNotifications);
} else {
  initializePushNotifications();
}

async function initializePushNotifications() {
  // Only initialize if user is logged in (check for auth token or similar)
  if (document.querySelector("[data-user-id]")) {
    const pushManager = new PushNotificationManager();
    window.pushManager = pushManager;

    try {
      await pushManager.init();
    } catch (error) {
      console.error("Push notification initialization failed:", error);
    }
  }
}
