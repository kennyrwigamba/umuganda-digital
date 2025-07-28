/**
 * AJAX Helper Functions
 * Utility functions for making API calls
 */

class UmugandaAPI {
  constructor() {
    this.baseURL = "/api";
    this.token = null;
  }

  /**
   * Make API request
   */
  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;

    const defaultOptions = {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    };

    if (this.token) {
      defaultOptions.headers["Authorization"] = `Bearer ${this.token}`;
    }

    const finalOptions = {
      ...defaultOptions,
      ...options,
      headers: {
        ...defaultOptions.headers,
        ...options.headers,
      },
    };

    try {
      const response = await fetch(url, finalOptions);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.error || `HTTP error! status: ${response.status}`);
      }

      return data;
    } catch (error) {
      console.error("API Request failed:", error);
      throw error;
    }
  }

  /**
   * GET request
   */
  async get(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${endpoint}?${queryString}` : endpoint;

    return this.request(url);
  }

  /**
   * POST request
   */
  async post(endpoint, data = {}) {
    return this.request(endpoint, {
      method: "POST",
      body: JSON.stringify(data),
    });
  }

  /**
   * PUT request
   */
  async put(endpoint, data = {}) {
    return this.request(endpoint, {
      method: "PUT",
      body: JSON.stringify(data),
    });
  }

  /**
   * DELETE request
   */
  async delete(endpoint) {
    return this.request(endpoint, {
      method: "DELETE",
    });
  }

  // Authentication methods
  async login(email, password, csrfToken) {
    return this.post("/auth/login", {
      email,
      password,
      csrf_token: csrfToken,
    });
  }

  async logout() {
    return this.post("/auth/logout");
  }

  async register(userData) {
    return this.post("/auth/register", userData);
  }

  async checkAuthStatus() {
    return this.get("/auth/status");
  }

  // Dashboard methods
  async getDashboardStats() {
    return this.get("/dashboard/stats");
  }

  async getRecentActivity() {
    return this.get("/dashboard/activity");
  }

  // Attendance methods
  async getAttendance(filters = {}) {
    return this.get("/attendance", filters);
  }

  async markAttendance(eventId, status) {
    return this.post("/attendance/mark", {
      event_id: eventId,
      status: status,
    });
  }

  async getAttendanceHistory(userId, filters = {}) {
    return this.get(`/attendance/history/${userId}`, filters);
  }

  // Fines methods
  async getFines(filters = {}) {
    return this.get("/fines", filters);
  }

  async payFine(fineId, paymentData) {
    return this.post(`/fines/${fineId}/pay`, paymentData);
  }

  async waiveFine(fineId, reason) {
    return this.post(`/fines/${fineId}/waive`, { reason });
  }

  // Events methods
  async getEvents(filters = {}) {
    return this.get("/events", filters);
  }

  async createEvent(eventData) {
    return this.post("/events", eventData);
  }

  async updateEvent(eventId, eventData) {
    return this.put(`/events/${eventId}`, eventData);
  }

  async deleteEvent(eventId) {
    return this.delete(`/events/${eventId}`);
  }

  // Residents methods (admin only)
  async getResidents(filters = {}) {
    return this.get("/residents", filters);
  }

  async createResident(residentData) {
    return this.post("/residents", residentData);
  }

  async updateResident(residentId, residentData) {
    return this.put(`/residents/${residentId}`, residentData);
  }

  async deleteResident(residentId) {
    return this.delete(`/residents/${residentId}`);
  }
}

// Utility functions
const Utils = {
  /**
   * Show alert message
   */
  showAlert(message, type = "info", container = "alert-container") {
    const alertContainer = document.getElementById(container);
    if (!alertContainer) return;

    const alertClass =
      {
        success: "bg-green-100 border-green-400 text-green-700",
        error: "bg-red-100 border-red-400 text-red-700",
        warning: "bg-yellow-100 border-yellow-400 text-yellow-700",
        info: "bg-blue-100 border-blue-400 text-blue-700",
      }[type] || "bg-blue-100 border-blue-400 text-blue-700";

    const iconClass =
      {
        success: "fa-check-circle",
        error: "fa-exclamation-circle",
        warning: "fa-exclamation-triangle",
        info: "fa-info-circle",
      }[type] || "fa-info-circle";

    alertContainer.innerHTML = `
            <div class="border px-4 py-3 rounded ${alertClass} flex items-center">
                <i class="fas ${iconClass} mr-2"></i>
                <span>${message}</span>
                <button class="ml-auto text-lg leading-none" onclick="this.parentElement.remove()">
                    &times;
                </button>
            </div>
        `;

    // Auto-hide after 5 seconds
    setTimeout(() => {
      if (alertContainer.firstElementChild) {
        alertContainer.firstElementChild.remove();
      }
    }, 5000);
  },

  /**
   * Format date for display
   */
  formatDate(dateString, options = {}) {
    if (!dateString) return "";

    const defaultOptions = {
      year: "numeric",
      month: "short",
      day: "numeric",
    };

    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", { ...defaultOptions, ...options });
  },

  /**
   * Format time for display
   */
  formatTime(timeString) {
    if (!timeString) return "";

    const [hours, minutes] = timeString.split(":");
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));

    return date.toLocaleTimeString("en-US", {
      hour: "numeric",
      minute: "2-digit",
      hour12: true,
    });
  },

  /**
   * Format currency (RWF)
   */
  formatCurrency(amount) {
    if (amount === null || amount === undefined) return "";

    return new Intl.NumberFormat("en-RW", {
      style: "currency",
      currency: "RWF",
      minimumFractionDigits: 0,
    }).format(amount);
  },

  /**
   * Debounce function
   */
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  /**
   * Get URL parameters
   */
  getUrlParams() {
    const params = new URLSearchParams(window.location.search);
    const result = {};
    for (const [key, value] of params) {
      result[key] = value;
    }
    return result;
  },

  /**
   * Set loading state for button
   */
  setButtonLoading(button, loading, loadingText = "Loading...") {
    if (loading) {
      button.disabled = true;
      button.dataset.originalText = button.textContent;
      button.innerHTML = `
                <i class="fas fa-spinner fa-spin mr-2"></i>
                ${loadingText}
            `;
    } else {
      button.disabled = false;
      button.textContent = button.dataset.originalText || "Submit";
    }
  },

  /**
   * Validate email format
   */
  isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  },

  /**
   * Validate phone number (Rwanda format)
   */
  isValidPhone(phone) {
    const re = /^(\+?25)?[0-9]{9,10}$/;
    return re.test(phone);
  },

  /**
   * Validate Rwanda National ID
   */
  isValidNationalId(nationalId) {
    const re = /^\d{16}$/;
    return re.test(nationalId);
  },

  /**
   * Calculate age from birthdate
   */
  calculateAge(birthdate) {
    const birth = new Date(birthdate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();

    if (
      monthDiff < 0 ||
      (monthDiff === 0 && today.getDate() < birth.getDate())
    ) {
      age--;
    }

    return age;
  },

  /**
   * Sanitize HTML content
   */
  sanitizeHTML(str) {
    const temp = document.createElement("div");
    temp.textContent = str;
    return temp.innerHTML;
  },
};

// Create global API instance
const api = new UmugandaAPI();

// Make utils available globally
window.UmugandaUtils = Utils;
