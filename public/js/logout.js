/**
 * Logout functionality
 * Handles user logout via API
 */

async function logout() {
  try {
    // Show confirmation dialog
    if (!confirm("Are you sure you want to log out?")) {
      return;
    }

    // Show loading state
    const logoutBtn = document.querySelector("[data-logout-btn]");
    if (logoutBtn) {
      logoutBtn.disabled = true;
      logoutBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin mr-2"></i>Logging out...';
    }

    // Make logout request
    const response = await fetch("/api/auth/logout", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    const result = await response.json();

    if (result.success) {
      // Show success message briefly
      if (window.showAlert) {
        showAlert("Logged out successfully!", "success");
      }

      // Redirect to login page after short delay
      setTimeout(() => {
        window.location.href = "/public/login.php?message=logged_out";
      }, 1000);
    } else {
      throw new Error(result.error || "Logout failed");
    }
  } catch (error) {
    console.error("Logout error:", error);

    // Show error message
    if (window.showAlert) {
      showAlert("Logout failed. Redirecting anyway...", "error");
    }

    // Fallback: redirect to logout page
    setTimeout(() => {
      window.location.href = "/logout.php";
    }, 2000);
  }
}

// Add logout function to global scope
window.logout = logout;
