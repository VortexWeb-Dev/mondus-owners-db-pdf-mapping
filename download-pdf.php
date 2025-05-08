<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mondus Property PDF Download</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
      background-color: #f9fafb;
    }

    .container {
      text-align: center;
      background-color: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      max-width: 500px;
      width: 100%;
    }

    .loading {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin: 2rem 0;
    }

    .spinner {
      border: 4px solid rgba(0, 0, 0, 0.1);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border-left-color: #4f46e5;
      animation: spin 1s linear infinite;
      margin-bottom: 1rem;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .error {
      color: #ef4444;
      margin: 1rem 0;
    }

    .btn {
      padding: 0.75rem 1.5rem;
      background-color: #4f46e5;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1rem;
      margin-top: 1rem;
    }

    .btn:hover {
      background-color: #4338ca;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Mondus Property PDF</h1>
    <div id="status">
      <div class="loading">
        <div class="spinner"></div>
        <p>Generating PDF...</p>
      </div>
    </div>
  </div>

  <script>
    // Constants
    const entityTypeId = 1058;
    const API_BASE_URL = "https://mondus.group/rest/1/dw9gd4xauhctd7ha";

    // Utility functions
    function mapEmirate(emirateId = 0) {
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

    function mapListingType(listingTypeId = 0) {
      const typeMap = {
        50: "Off-Plan",
        51: "Leasing",
        52: "Secondary",
      };
      return typeMap[listingTypeId] || "";
    }

    function mapStatus(statusId = 0) {
      const typeMap = {
        55: "Vacant",
        56: "Rented",
      };
      return typeMap[statusId] || "";
    }

    async function getBase64ImageFromURL(url) {
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

    async function generatePDF(id) {
      try {
        const response = await fetch(
          `${API_BASE_URL}/crm.item.get?id=${id}&entityTypeId=${entityTypeId}`
        );
        const result = await response.json();

        if (!result.result || !result.result.item) {
          showError("Property not found");
          return;
        }

        const item = result.result.item;

        if (typeof window.jspdf === "undefined") {
          showToast("PDF library not loaded", "error");
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

        // === Headers ===
        if (item.ufCrm7_1743856030?.[0]?.urlMachine) {
          try {
            const imgData = await getBase64ImageFromURL(
              "https://apps.mondus.group/assets/mondus-header.png"
            );
            doc.addImage(imgData, "JPEG", 10, y, 190, 140);
            y += 150;
            checkPageBreak();
          } catch (err) {
            console.warn("Error loading preview image:", err);
          }
        }

        const title = item.ufCrm7_1746709619520 || "N/A";
        const propertyType = item.ufCrm7_1743829247734 || "N/A";
        const bedrooms = item.ufCrm7_1743829267783 || "N/A";
        const bathrooms = item.ufCrm7_1743829278192 || "N/A";
        const price = item.ufCrm7_1743829576957 || "N/A";
        const sqft = item.ufCrm7_1743829315467 || "N/A";
        const description = item.ufCrm7_1746461192 || "N/A";
        const privateAmenities = item.ufCrm7_1746461204 || [];

        // === Title Section ===
        doc.setFont(undefined, "bold");
        doc.setFontSize(14);
        doc.text(title, 16, y);
        y += 10;
        checkPageBreak();

        // Add location
        doc.setFontSize(12);
        const location = `${mapEmirate(item.ufCrm7_1743829019488) || ""} - ${
        item.ufCrm7_1743829187045 || ""
      } - ${cleanAddress(item.ufCrm7_1743829195831) || ""}`;
        doc.text(`${location}`, 16, y);
        y += 8;
        checkPageBreak();

        // Add price
        doc.setFont(undefined, "bold");
        doc.text(`AED ${parseFloat(price)}`, 16, y);
        y += 16;
        checkPageBreak();

        // === Property Information ===
        doc.setFont(undefined, "normal");
        doc.setFontSize(10);
        doc.text(`ID: ${item.id}`, 16, y);
        y += 8;
        checkPageBreak();

        doc.text(`Property Type: ${propertyType}`, 16, y);
        y += 8;
        checkPageBreak();

        doc.text(
          `Listing Type: ${
          mapListingType(item.ufCrm7_1743829448289) || "N/A"
        }`,
          16,
          y
        );
        y += 8;
        checkPageBreak();

        doc.text(
          `Status: ${mapStatus(item.ufCrm7_1743829543608) || "N/A"}`,
          16,
          y
        );
        y += 16;
        checkPageBreak();

        // === Property Features ===
        doc.setFontSize(11);
        doc.setDrawColor(0);
        doc.setFillColor(240, 240, 240);
        doc.rect(14, y, 182, 8, "F");
        doc.setFont(undefined, "bold");
        doc.text("PROPERTY FEATURES", 16, y + 5);
        y += 15;
        checkPageBreak();

        doc.setFont(undefined, "normal");
        doc.text(
          `Sq Ft: ${sqft}       Beds: ${bedrooms}       Baths: ${bathrooms}`,
          16,
          y
        );
        y += 8;
        checkPageBreak();
        doc.text(`Price: AED ${parseFloat(price)}`, 16, y);
        y += 8;
        checkPageBreak();

        // === Description ===
        doc.setFillColor(240, 240, 240);
        doc.rect(14, y, 182, 8, "F");
        doc.setFont(undefined, "bold");
        doc.text("DESCRIPTION", 16, y + 5);
        y += 15;
        checkPageBreak();

        doc.setFont(undefined, "normal");
        doc.setFontSize(10);
        const descriptionLines = doc.splitTextToSize(description, 180);
        doc.text(descriptionLines, 16, y);
        y += descriptionLines.length * 7 + 8;
        checkPageBreak();

        // === Private Amenities ===
        doc.setFillColor(240, 240, 240);
        doc.rect(14, y, 182, 8, "F");
        doc.setFont(undefined, "bold");
        doc.text("PRIVATE AMENITIES", 16, y + 5);
        y += 15;
        checkPageBreak();

        doc.setFont(undefined, "normal");

        const pillPaddingX = 4;
        const pillPaddingY = 3;
        const pillMarginX = 4;
        const pillHeight = 8;

        let startX = 16;

        privateAmenities.forEach((amenity) => {
          const textWidth = doc.getTextWidth(amenity);
          const pillWidth = textWidth + pillPaddingX * 2;

          // If pill exceeds page width, wrap to next line
          if (startX + pillWidth > 190) {
            startX = 16;
            y += pillHeight + pillMarginX;
            checkPageBreak();
          }

          // Draw pill background
          doc.setFillColor(220, 220, 220); // light gray
          doc.roundedRect(startX, y, pillWidth, pillHeight, 3, 3, "F");

          // Draw pill text
          doc.setTextColor(50, 50, 50);
          doc.text(amenity, startX + pillPaddingX, y + 6);

          startX += pillWidth + pillMarginX;
        });

        // Add extra spacing after pills
        y += pillHeight + 8;

        // === Property Images ===
        if (item.ufCrm7_1743856030 && item.ufCrm7_1743856030.length > 0) {
          doc.setFillColor(240, 240, 240);
          doc.rect(14, y, 182, 8, "F");
          doc.setFont(undefined, "bold");
          doc.text("PROPERTY IMAGES", 16, y + 5);
          y += 15;
          checkPageBreak();

          let col = 0;
          let maxWidth = 52;
          let maxHeight = 50;
          let x = 16;

          for (const imageObj of item.ufCrm7_1743856030) {
            if (!imageObj.urlMachine) continue;

            try {
              const imgData = await getBase64ImageFromURL(
                imageObj.urlMachine
              );

              const tempImg = new Image();
              tempImg.src = imgData;

              let imgWidth = maxWidth;
              let imgHeight = maxHeight;

              if (tempImg.width > 0 && tempImg.height > 0) {
                const ratio = Math.min(
                  maxWidth / tempImg.width,
                  maxHeight / tempImg.height
                );
                imgWidth = tempImg.width * ratio;
                imgHeight = tempImg.height * ratio;
              }

              const xOffset = x + (maxWidth - imgWidth) / 2;
              const yOffset = y + (maxHeight - imgHeight) / 2;

              doc.addImage(
                imgData,
                "JPEG",
                xOffset,
                yOffset,
                imgWidth,
                imgHeight
              );
            } catch (error) {
              console.warn("Failed to load image:", imageObj.urlMachine, error);
            }

            col++;
            x += maxWidth + 10;

            if (col >= 3) {
              col = 0;
              x = 16;
              y += maxHeight + 10;
              checkPageBreak();
            }
          }
          y += maxHeight + 10;
        }

        // === Contact Information ===
        doc.setFillColor(240, 240, 240);
        doc.rect(14, y, 182, 8, "F");
        doc.setFont(undefined, "bold");
        doc.text("CONTACT INFORMATION", 16, y + 5);
        y += 15;
        checkPageBreak();

        doc.setFont(undefined, "normal");
        doc.setFontSize(10);
        doc.text(
          "For viewing and more information, please contact our property specialist:",
          16,
          y
        );
        y += 7;
        doc.text("Mohammed Fayaz", 16, y);
        y += 7;
        doc.text("m: 971509701507", 16, y);
        y += 7;
        doc.text("e: fayaz@mondusgroup.com", 16, y);
        y += 7;
        doc.text("Mondus Properties | https://mondusproperties.ae/", 16, y);
        y += 8;
        checkPageBreak();

        // === About Mondus Properties ===
        doc.setFillColor(240, 240, 240);
        doc.rect(14, y, 182, 8, "F");
        doc.setFont(undefined, "bold");
        doc.text("ABOUT MONDUS PROPERTIES", 16, y + 5);
        y += 15;
        checkPageBreak();

        doc.setFont(undefined, "normal");
        const aboutText =
          "Mondus Properties is a leading property brokerage, investment, and consultancy firm with a dedicated team of international experts. We provide tailored property solutions across commercial, residential, retail, and offplan sectors, backed by expertise in market trends, negotiation, and management.";
        const aboutLines = doc.splitTextToSize(aboutText, 180);
        doc.text(aboutLines, 16, y);
        y += aboutLines.length * 7 + 8;
        checkPageBreak();

        doc.text("Mondus Properties Real Estate Brokers LLC", 16, y);
        y += 7;
        doc.text("RERA ORN: 123456", 16, y);
        y += 7;
        doc.text(
          "Address: 2402 Mondus Group Iris Bay - Business Bay - Dubai",
          16,
          y
        );
        y += 7;
        doc.text("Phone No: +971521110794", 16, y);
        y += 7;
        doc.text("Web: www.mondusproperties.ae", 16, y);
        y += 8;
        checkPageBreak();

        // === Disclaimer ===
        doc.setFontSize(8);
        doc.text(
          "Disclaimer: Prices May Vary Based on Unit Location, Size, and Availability.",
          16,
          y
        );
        y += 8;
        checkPageBreak();

        // === Footer ===
        doc.setFontSize(8);
        doc.text(
          "Generated on " + new Date().toLocaleString(),
          105,
          pageHeight - 10, {
            align: "center",
          }
        );

        doc.save(`Mondus_Property_${id}_${new Date().toISOString()}.pdf`);
        // showToast("PDF downloaded successfully");

      } catch (error) {
        console.error("Error generating PDF:", error);
        // showToast("Failed to download PDF");

      }
    }

    function cleanAddress(input) {
      if (!input) {
        return "";
      }

      let address = input.split(/[|;]/)[0].trim();

      address = address.replace(/\d+$/, "").trim();

      return address;
    }

    function showError(message) {
      const statusDiv = document.getElementById("status");
      statusDiv.innerHTML = `
                <div class="error">
                    <p>${message}</p>
                    <button class="btn" onclick="history.back()">Close</button>
                </div>
            `;
    }

    function showSuccess(message) {
      const statusDiv = document.getElementById("status");
      statusDiv.innerHTML = `
                <div>
                    <p>${message}</p>
                    <button class="btn" onclick="history.back()">Close</button>
                </div>
            `;
    }

    // Main execution
    document.addEventListener("DOMContentLoaded", () => {
      // Get property ID from URL
      const urlParams = new URLSearchParams(window.location.search);
      const id = urlParams.get("id");

      if (!id) {
        showError("No property ID provided");
        return;
      }

      // Generate and download PDF
      generatePDF(id);
    });
  </script>
</body>

</html>