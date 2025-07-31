/**
 * Location Hierarchy Manager
 * Handles cascading dropdowns for Rwanda's administrative hierarchy
 */

class LocationHierarchy {
  constructor(options = {}) {
    this.apiEndpoint = options.apiEndpoint || "/api/locations.php";
    this.provinceSelect = options.provinceSelect || "#province";
    this.districtSelect = options.districtSelect || "#district";
    this.sectorSelect = options.sectorSelect || "#sector";
    this.cellSelect = options.cellSelect || "#cell";

    this.onLocationChange = options.onLocationChange || null;
    this.showLoadingText = options.showLoadingText !== false;

    this.init();
  }

  init() {
    this.loadProvinces();
    this.bindEvents();
  }

  bindEvents() {
    const self = this;

    // Province change event
    $(this.provinceSelect).on("change", function () {
      const provinceId = $(this).val();
      self.clearDistricts();
      self.clearSectors();
      self.clearCells();

      if (provinceId) {
        self.loadDistricts(provinceId);
      }

      self.triggerLocationChange();
    });

    // District change event
    $(this.districtSelect).on("change", function () {
      const districtId = $(this).val();
      self.clearSectors();
      self.clearCells();

      if (districtId) {
        self.loadSectors(districtId);
      }

      self.triggerLocationChange();
    });

    // Sector change event
    $(this.sectorSelect).on("change", function () {
      const sectorId = $(this).val();
      self.clearCells();

      if (sectorId) {
        self.loadCells(sectorId);
      }

      self.triggerLocationChange();
    });

    // Cell change event
    $(this.cellSelect).on("change", function () {
      self.triggerLocationChange();
    });
  }

  async loadProvinces() {
    try {
      this.setLoading(this.provinceSelect, true);
      const response = await this.apiCall("provinces");

      if (response.success) {
        this.populateSelect(
          this.provinceSelect,
          response.data,
          "Select Province"
        );
      } else {
        this.showError("Failed to load provinces");
      }
    } catch (error) {
      this.showError("Error loading provinces: " + error.message);
    } finally {
      this.setLoading(this.provinceSelect, false);
    }
  }

  async loadDistricts(provinceId) {
    try {
      this.setLoading(this.districtSelect, true);
      const response = await this.apiCall("districts", {
        province_id: provinceId,
      });

      if (response.success) {
        this.populateSelect(
          this.districtSelect,
          response.data,
          "Select District"
        );
        $(this.districtSelect).prop("disabled", false);
      } else {
        this.showError("Failed to load districts");
      }
    } catch (error) {
      this.showError("Error loading districts: " + error.message);
    } finally {
      this.setLoading(this.districtSelect, false);
    }
  }

  async loadSectors(districtId) {
    try {
      this.setLoading(this.sectorSelect, true);
      const response = await this.apiCall("sectors", {
        district_id: districtId,
      });

      if (response.success) {
        this.populateSelect(this.sectorSelect, response.data, "Select Sector");
        $(this.sectorSelect).prop("disabled", false);
      } else {
        this.showError("Failed to load sectors");
      }
    } catch (error) {
      this.showError("Error loading sectors: " + error.message);
    } finally {
      this.setLoading(this.sectorSelect, false);
    }
  }

  async loadCells(sectorId) {
    try {
      this.setLoading(this.cellSelect, true);
      const response = await this.apiCall("cells", { sector_id: sectorId });

      if (response.success) {
        this.populateSelect(this.cellSelect, response.data, "Select Cell");
        $(this.cellSelect).prop("disabled", false);
      } else {
        this.showError("Failed to load cells");
      }
    } catch (error) {
      this.showError("Error loading cells: " + error.message);
    } finally {
      this.setLoading(this.cellSelect, false);
    }
  }

  populateSelect(selector, data, placeholder = "Select...") {
    const $select = $(selector);
    $select.empty();

    // Add placeholder option
    $select.append(`<option value="">${placeholder}</option>`);

    // Add data options
    data.forEach((item) => {
      $select.append(`<option value="${item.id}">${item.name}</option>`);
    });
  }

  clearDistricts() {
    $(this.districtSelect)
      .empty()
      .append('<option value="">Select District</option>')
      .prop("disabled", true);
  }

  clearSectors() {
    $(this.sectorSelect)
      .empty()
      .append('<option value="">Select Sector</option>')
      .prop("disabled", true);
  }

  clearCells() {
    $(this.cellSelect)
      .empty()
      .append('<option value="">Select Cell</option>')
      .prop("disabled", true);
  }

  setLoading(selector, isLoading) {
    const $select = $(selector);
    if (isLoading && this.showLoadingText) {
      $select
        .empty()
        .append('<option value="">Loading...</option>')
        .prop("disabled", true);
    }
  }

  async apiCall(action, params = {}) {
    const url = new URL(this.apiEndpoint, window.location.origin);
    url.searchParams.append("action", action);

    Object.keys(params).forEach((key) => {
      url.searchParams.append(key, params[key]);
    });

    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
  }

  triggerLocationChange() {
    if (this.onLocationChange && typeof this.onLocationChange === "function") {
      const location = this.getCurrentLocation();
      this.onLocationChange(location);
    }
  }

  getCurrentLocation() {
    return {
      province_id: $(this.provinceSelect).val(),
      district_id: $(this.districtSelect).val(),
      sector_id: $(this.sectorSelect).val(),
      cell_id: $(this.cellSelect).val(),
      province_name: $(this.provinceSelect).find("option:selected").text(),
      district_name: $(this.districtSelect).find("option:selected").text(),
      sector_name: $(this.sectorSelect).find("option:selected").text(),
      cell_name: $(this.cellSelect).find("option:selected").text(),
    };
  }

  setLocation(provinceId, districtId = null, sectorId = null, cellId = null) {
    $(this.provinceSelect).val(provinceId).trigger("change");

    if (districtId) {
      setTimeout(() => {
        $(this.districtSelect).val(districtId).trigger("change");

        if (sectorId) {
          setTimeout(() => {
            $(this.sectorSelect).val(sectorId).trigger("change");

            if (cellId) {
              setTimeout(() => {
                $(this.cellSelect).val(cellId).trigger("change");
              }, 500);
            }
          }, 500);
        }
      }, 500);
    }
  }

  async getLocationPath(cellId) {
    try {
      const response = await this.apiCall("location_path", { cell_id: cellId });
      return response.success ? response.data : null;
    } catch (error) {
      console.error("Error getting location path:", error);
      return null;
    }
  }

  async searchLocations(searchTerm, type = null) {
    try {
      const params = { q: searchTerm };
      if (type) params.type = type;

      const response = await this.apiCall("search", params);
      return response.success ? response.data : [];
    } catch (error) {
      console.error("Error searching locations:", error);
      return [];
    }
  }

  async validateHierarchy(cellId, sectorId, districtId, provinceId) {
    try {
      const response = await this.apiCall("validate_hierarchy", {
        cell_id: cellId,
        sector_id: sectorId,
        district_id: districtId,
        province_id: provinceId,
      });
      return response.success ? response.data.valid : false;
    } catch (error) {
      console.error("Error validating hierarchy:", error);
      return false;
    }
  }

  showError(message) {
    console.error(message);
    // You can customize this to show user-friendly error messages
    if (typeof window.showNotification === "function") {
      window.showNotification(message, "error");
    } else {
      alert(message);
    }
  }

  isValid() {
    const location = this.getCurrentLocation();
    return (
      location.cell_id &&
      location.sector_id &&
      location.district_id &&
      location.province_id
    );
  }

  reset() {
    $(this.provinceSelect).val("").trigger("change");
  }
}

// Utility functions for location management
const LocationUtils = {
  // Format location display
  formatLocationDisplay: function (location) {
    if (!location) return "Unknown Location";

    const parts = [];
    if (location.cell_name && location.cell_name !== "Select Cell")
      parts.push(location.cell_name);
    if (location.sector_name && location.sector_name !== "Select Sector")
      parts.push(location.sector_name);
    if (location.district_name && location.district_name !== "Select District")
      parts.push(location.district_name);
    if (location.province_name && location.province_name !== "Select Province")
      parts.push(location.province_name);

    return parts.length > 0 ? parts.join(", ") : "Incomplete Location";
  },

  // Get short location display (Cell, Sector)
  formatShortLocation: function (location) {
    if (!location) return "Unknown";

    const parts = [];
    if (location.cell_name && location.cell_name !== "Select Cell")
      parts.push(location.cell_name);
    if (location.sector_name && location.sector_name !== "Select Sector")
      parts.push(location.sector_name);

    return parts.length > 0 ? parts.join(", ") : "Unknown";
  },

  // Initialize location hierarchy for registration/profile forms
  initRegistrationForm: function (formSelector = "#registrationForm") {
    return new LocationHierarchy({
      provinceSelect: `${formSelector} #province`,
      districtSelect: `${formSelector} #district`,
      sectorSelect: `${formSelector} #sector`,
      cellSelect: `${formSelector} #cell`,
      onLocationChange: function (location) {
        // Update hidden fields if they exist
        $(`${formSelector} input[name="province_id"]`).val(
          location.province_id
        );
        $(`${formSelector} input[name="district_id"]`).val(
          location.district_id
        );
        $(`${formSelector} input[name="sector_id"]`).val(location.sector_id);
        $(`${formSelector} input[name="cell_id"]`).val(location.cell_id);

        // Trigger custom event
        $(formSelector).trigger("locationChanged", [location]);
      },
    });
  },

  // Initialize location filters for admin panels
  initLocationFilter: function (filterSelector = "#locationFilter") {
    return new LocationHierarchy({
      provinceSelect: `${filterSelector} #filterProvince`,
      districtSelect: `${filterSelector} #filterDistrict`,
      sectorSelect: `${filterSelector} #filterSector`,
      cellSelect: `${filterSelector} #filterCell`,
      onLocationChange: function (location) {
        // Trigger filter update
        $(filterSelector).trigger("filterChanged", [location]);
      },
    });
  },
};

// Export for module usage
if (typeof module !== "undefined" && module.exports) {
  module.exports = { LocationHierarchy, LocationUtils };
}
