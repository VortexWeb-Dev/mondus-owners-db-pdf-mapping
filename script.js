const entityTypeId = 1058;
const API_BASE_URL = "https://mondus.group/rest/1/dw9gd4xauhctd7ha";
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
          order: { id: "desc" },
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
      37: "Dubai",
      38: "Abu Dhabi",
      39: "Sharjah",
      40: "Ras Al Khaimah",
      41: "Fujairah",
      42: "Ajman",
    };

    return typeMap[emirateId] || "";
  }

  mapListingType(listingTypeId = 0) {
    const typeMap = {
      50: "Off-Plan",
      51: "Leasing",
      52: "Secondary",
    };

    return typeMap[listingTypeId] || "";
  }

  mapStatus(statusId = 0) {
    const typeMap = {
      55: "Vacant",
      56: "Rented",
    };

    return typeMap[statusId] || "";
  }

  renderTable(items) {
    const tbody = document.getElementById("itemsTableBody");
    tbody.innerHTML = "";

    items.forEach((item, index) => {
      const tr = document.createElement("tr");
      tr.className = "bg-white border-b hover:bg-gray-50";
      tr.innerHTML = `
                <td class="px-6 py-4">${index + 1}</td>
                <td class="px-6 py-4">${item.title}</td>
                <td class="px-6 py-4">${
                  this.mapEmirate(item.ufCrm7_1743829019488) || ""
                }</td>
                <td class="px-6 py-4">${item.ufCrm7_1743829187045 || ""}</td>
                <td class="px-6 py-4">${item.ufCrm7_1743829195831 || ""}</td>
                <td class="px-6 py-4">${item.ufCrm7_1743829247734 || ""}</td>
                <td class="px-6 py-4">${
                  this.mapListingType(item.ufCrm7_1743829448289) || ""
                }</td>
                <td class="px-6 py-4"><span class="px-2 py-1 rounded-md ${
                  item.ufCrm7_1743829543608 == 55
                    ? "text-green-600 bg-green-100"
                    : item.ufCrm7_1743829543608 == 56
                    ? "text-red-600 bg-red-100"
                    : ""
                }">${
        this.mapStatus(item.ufCrm7_1743829543608) || ""
      }</span></td>
                <td class="px-6 py-4">${item.ufCrm7_1743829576957 || ""}</td>
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
                        <button class="share-link-btn block w-full text-left px-3 py-2 text-sm text-blue-700 hover:bg-gray-100" data-id="${
                          item.id
                        }">
                            Copy Share Link
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
      if (e.target.closest(".action-btn")) {
        const menu = e.target.closest("td").querySelector(".action-menu");
        menu.classList.toggle("hidden");
        return;
      }

      document.querySelectorAll(".action-menu").forEach((menu) => {
        if (!e.target.closest(".action-menu")) menu.classList.add("hidden");
      });

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

      const deleteBtn = e.target.closest(".delete-btn");
      const downloadBtn = e.target.closest(".download-btn");
      const shareBtn = e.target.closest(".share-link-btn");

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

      if (shareBtn) {
        const id = shareBtn.dataset.id;
        const link = this.generateShareableLink(id);

        navigator.clipboard
          .writeText(link)
          .then(() => this.showToast("Link copied to clipboard"))
          .catch(() => this.showToast("Failed to copy link", "error"));
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
    try {
      const response = await fetch(
        `${API_BASE_URL}/crm.item.get?id=${id}&entityTypeId=${entityTypeId}`
      );
      const result = await response.json();

      if (!result.result || !result.result.item) {
        this.showToast("Item not found");
        return;
      }

      const item = result.result.item;

      if (typeof window.jspdf === "undefined") {
        this.showToast("PDF library not loaded", "error");
        return;
      }

      const doc = new window.jspdf.jsPDF();
      let y = 10;
      const pageHeight = doc.internal.pageSize.height;

      const checkPageBreak = () => {
        if (y + 10 > pageHeight - 20) {
          doc.addPage();
          y = 10;
        }
      };

      // === First large image (main preview image) ===
      if (item.ufCrm7_1743856030?.[0]?.urlMachine) {
        try {
          const imgData = await this.getBase64ImageFromURL(
            item.ufCrm7_1743856030[0].urlMachine
          );
          doc.addImage(imgData, "JPEG", 10, y, 190, 140);
          y += 150;
          checkPageBreak();
        } catch (err) {
          console.warn("Error loading preview image:", err);
        }
      }

      // === Headers ===
      doc.setFontSize(18);
      doc.text("Property Details", 105, y, { align: "center" });
      y += 10;
      checkPageBreak();

      doc.setFontSize(12);
      doc.text("MONDUS GROUP", 105, y, { align: "center" });
      y += 10;
      checkPageBreak();

      // === PROPERTY FEATURES ===
      doc.setFontSize(11);
      doc.setDrawColor(0);
      doc.setFillColor(240, 240, 240);
      doc.rect(14, y, 182, 8, "F");
      doc.setFont(undefined, "bold");
      doc.text("PROPERTY FEATURES", 16, y + 5);
      y += 15;
      checkPageBreak();

      const propertyType = item.ufCrm7_1743829247734 || "N/A";
      const bedrooms = item.ufCrm7_1743829267783 || "N/A";
      const bathrooms = item.ufCrm7_1743829278192 || "N/A";
      const price = item.ufCrm7_1743829576957 || "N/A";
      const sqft = item.ufCrm7_1743829315467 || "N/A";

      doc.setFont(undefined, "normal");
      doc.text(
        `Sq Ft: ${sqft}       Beds: ${bedrooms}       Baths: ${bathrooms}`,
        16,
        y
      );
      y += 8;
      checkPageBreak();
      doc.text(`Price: AED ${price}`, 16, y);
      y += 8;
      checkPageBreak();

      doc.setDrawColor(0);
      doc.line(16, y, 190, y);
      y += 8;
      checkPageBreak();

      // === PROPERTY INFORMATION ===
      doc.setFillColor(240, 240, 240);
      doc.rect(14, y, 182, 8, "F");
      doc.setFont(undefined, "bold");
      doc.text("PROPERTY INFORMATION", 16, y + 5);
      y += 15;
      checkPageBreak();

      doc.setFont(undefined, "normal");
      doc.text(`ID: ${item.id}`, 16, y);
      y += 8;
      checkPageBreak();
      doc.text(`Title: ${item.title || "N/A"}`, 16, y);
      y += 8;
      checkPageBreak();
      doc.text(
        `Emirate: ${this.mapEmirate(item.ufCrm7_1743829019488) || "N/A"}`,
        16,
        y
      );
      y += 8;
      checkPageBreak();
      doc.text(`Building: ${item.ufCrm7_1743829187045 || "N/A"}`, 16, y);
      y += 8;
      checkPageBreak();
      doc.text(`Address: ${item.ufCrm7_1743829195831 || "N/A"}`, 16, y);
      y += 8;
      checkPageBreak();
      doc.text(`Property Type: ${propertyType}`, 16, y);
      y += 8;
      checkPageBreak();
      doc.text(
        `Listing Type: ${
          this.mapListingType(item.ufCrm7_1743829448289) || "N/A"
        }`,
        16,
        y
      );
      y += 8;
      checkPageBreak();
      doc.text(
        `Status: ${this.mapStatus(item.ufCrm7_1743829543608) || "N/A"}`,
        16,
        y
      );
      y += 8;
      checkPageBreak();
      doc.text(`Price: AED ${price}`, 16, y);
      y += 16;
      checkPageBreak();

      // === PROPERTY IMAGES ===
      if (item.ufCrm7_1743856030 && item.ufCrm7_1743856030.length > 0) {
        doc.setFillColor(240, 240, 240);
        doc.rect(14, y, 182, 8, "F");
        doc.setFont(undefined, "bold");
        doc.text("PROPERTY IMAGES", 16, y + 5);
        y += 15;
        checkPageBreak();

        let col = 0;
        let imgSize = 50;
        let x = 16;

        for (const imageObj of item.ufCrm7_1743856030) {
          if (!imageObj.urlMachine) continue;

          try {
            const imgData = await this.getBase64ImageFromURL(
              imageObj.urlMachine
            );
            doc.addImage(imgData, "JPEG", x, y, imgSize, imgSize);
          } catch (error) {
            console.warn("Failed to load image:", imageObj.urlMachine, error);
          }

          col++;
          x += imgSize + 10;

          if (col >= 3) {
            col = 0;
            x = 16;
            y += imgSize + 10;
            checkPageBreak();
          }
        }
        y += imgSize + 10;
      }

      // === Footer ===
      doc.setFontSize(8);
      doc.text(
        "Generated on " + new Date().toLocaleString(),
        105,
        pageHeight - 10,
        {
          align: "center",
        }
      );

      doc.save(`Mondus_Property_${id}_${new Date().toISOString()}.pdf`);
      this.showToast("PDF downloaded successfully");
    } catch (error) {
      console.error("Error generating PDF:", error);
      this.showToast("Failed to download PDF");
    }
  }

  async getBase64ImageFromURL(url) {
    const proxyUrl = "https://apps.mondus.group/proxy/?url=";
    const response = await fetch(proxyUrl + encodeURIComponent(url));
    const blob = await response.blob();

    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onloadend = () => resolve(reader.result);
      reader.onerror = reject;
      reader.readAsDataURL(blob);
    });
  }

  generateShareableLink(id) {
    const baseUrl = window.location.origin;
    let path = "/owners-db-pdf-mapping/";

    if (baseUrl.includes("localhost")) {
      path = "/projects/mondus/owners-db-pdf-mapping/";
    }

    return `${baseUrl}${path}download-pdf.php?id=${id}`;
  }
}

document.addEventListener("DOMContentLoaded", () => new ItemTable());
