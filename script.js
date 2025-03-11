const entityTypeId = 1054;
const API_BASE_URL = "https://mondus.group/rest/1/c3iwswykiqvhzc71";
const ITEMS_PER_PAGE = 50;

class ItemTable {
  constructor() {
    this.currentPage = 1;
    this.totalItems = 0;
    this.init();
  }

  async init() {
    await this.fetchItems();
    this.setupEventListeners();
  }

  showLoading(show) {
    document.getElementById("loadingOverlay").classList.toggle("hidden", !show);
  }

  showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `px-4 py-2 rounded-md text-sm ${
      type === "success"
        ? "bg-green-100 text-green-800"
        : "bg-red-100 text-red-800"
    }`;
    toast.textContent = message;
    document.getElementById("toastContainer").appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  }

  async fetchItems() {
    try {
      this.showLoading(true);
      const response = await fetch(`${API_BASE_URL}/crm.item.list`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          entityTypeId,
          start: (this.currentPage - 1) * ITEMS_PER_PAGE,
          limit: ITEMS_PER_PAGE,
        }),
      });
      const data = await response.json();

      this.totalItems = data.total || 0;
      this.renderTable(data.result.items || []);
      this.renderPagination();
    } catch (error) {
      this.showToast("Failed to load items", "error");
      console.error(error);
    } finally {
      this.showLoading(false);
    }
  }

  mapEmirate(emirateId = 0) {
    const typeMap = {
      73: "Dubai",
      74: "Abu Dhabi",
      75: "Sharjah",
      76: "Ras Al Khaimah",
      77: "Fujairah",
      78: "Ajman",
    };

    return typeMap[emirateId] || "";
  }

  mapListingType(listingTypeId = 0) {
    const typeMap = {
      86: "Off-Plan",
      87: "Leasing",
      88: "Secondary",
    };

    return typeMap[listingTypeId] || "";
  }

  mapStatus(statusId = 0) {
    const typeMap = {
      91: "Vacant",
      92: "Rented"
    };

    return typeMap[statusId] || "";
  }

  renderTable(items) {
    const tbody = document.getElementById("itemsTableBody");
    tbody.innerHTML = "";

    items.forEach((item) => {
      const tr = document.createElement("tr");
      tr.className = "bg-white border-b hover:bg-gray-50";
      tr.innerHTML = `
                <td class="px-6 py-4">${item.id}</td>
                <td class="px-6 py-4">${
                  this.mapEmirate(item.ufCrm8_1741421077583) || ""
                }</td>
                <td class="px-6 py-4">${item.ufCrm8_1741422864945 || ""}</td>
                <td class="px-6 py-4">${item.ufCrm8_1741422879710 || ""}</td>
                <td class="px-6 py-4">${item.ufCrm8_1741425149011 || ""}</td>
                <td class="px-6 py-4">${this.mapListingType(item.ufCrm8_1741425358726) || ""}</td>
                <td class="px-6 py-4">${this.mapStatus(item.ufCrm8_1741425465206) || ""}</td>
                <td class="px-6 py-4">${item.ufCrm8_1741425522751 || ""}</td>
                <td class="px-6 py-4 relative text-right">
                    <button class="action-btn text-gray-600 hover:text-gray-900" data-id="${
                      item.id
                    }">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <div class="action-menu hidden absolute right-6 z-10 mt-1 w-32 rounded-md bg-white shadow-lg border">
                        <button class="download-btn block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100" data-id="${
                          item.id
                        }">
                            Download PDF
                        </button>
                        <button class="delete-btn block w-full text-left px-3 py-2 text-sm text-red-700 hover:bg-gray-100" data-id="${
                          item.id
                        }">
                            Delete
                        </button>
                    </div>
                </td>
            `;
      tbody.appendChild(tr);
    });
  }

  renderPagination() {
    const totalPages = Math.ceil(this.totalItems / ITEMS_PER_PAGE);
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = `
            <div class="text-sm text-gray-700">
                Showing ${(this.currentPage - 1) * ITEMS_PER_PAGE + 1} to 
                ${Math.min(
                  this.currentPage * ITEMS_PER_PAGE,
                  this.totalItems
                )} of 
                ${this.totalItems} items
            </div>
            <div class="flex gap-2">
                <button id="prevPage" class="px-3 py-1 rounded-md border ${
                  this.currentPage === 1
                    ? "text-gray-400 cursor-not-allowed"
                    : "hover:bg-gray-100"
                }">Prev</button>
                <button id="nextPage" class="px-3 py-1 rounded-md border ${
                  this.currentPage === totalPages
                    ? "text-gray-400 cursor-not-allowed"
                    : "hover:bg-gray-100"
                }">Next</button>
            </div>
        `;
  }

  setupEventListeners() {
    document.addEventListener("click", async (e) => {
      // Action menu toggle
      if (e.target.closest(".action-btn")) {
        const menu = e.target.closest("td").querySelector(".action-menu");
        menu.classList.toggle("hidden");
        return;
      }

      // Close all menus if clicking outside
      document.querySelectorAll(".action-menu").forEach((menu) => {
        if (!e.target.closest(".action-menu")) menu.classList.add("hidden");
      });

      // Pagination
      if (e.target.id === "prevPage" && this.currentPage > 1) {
        this.currentPage--;
        await this.fetchItems();
      }
      if (
        e.target.id === "nextPage" &&
        this.currentPage < Math.ceil(this.totalItems / ITEMS_PER_PAGE)
      ) {
        this.currentPage++;
        await this.fetchItems();
      }

      // Actions
      const deleteBtn = e.target.closest(".delete-btn");
      const downloadBtn = e.target.closest(".download-btn");

      if (deleteBtn) {
        const id = deleteBtn.dataset.id;
        if (confirm("Are you sure you want to delete this item?")) {
          await this.deleteItem(id);
        }
      }

      if (downloadBtn) {
        const id = downloadBtn.dataset.id;
        this.downloadPDF(id);
      }
    });
  }

  async deleteItem(id) {
    try {
      this.showLoading(true);
      await fetch(`${API_BASE_URL}/crm.item.delete`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ entityTypeId, id }),
      });
      this.showToast("Item deleted successfully");
      await this.fetchItems();
    } catch (error) {
      this.showToast("Failed to delete item", "error");
      console.error(error);
    } finally {
      this.showLoading(false);
    }
  }

  async downloadPDF(id) {
    // Implement PDF download logic here
    // This is a placeholder - you'll need to adjust based on your Bitrix API capabilities
    this.showToast("PDF download not implemented yet");
  }
}

document.addEventListener("DOMContentLoaded", () => new ItemTable());
