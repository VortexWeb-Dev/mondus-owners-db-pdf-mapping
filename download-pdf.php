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
        const response = await fetch(url);
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
            showError("PDF library not loaded");
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

          if (item.ufCrm7_1743856030 && item.ufCrm7_1743856030.length > 0) {
            try {
              const firstImageObj = item.ufCrm7_1743856030[0];
              if (firstImageObj.urlMachine) {
                const imgData = await getBase64ImageFromURL(
                  firstImageObj.urlMachine
                );
                doc.addImage(imgData, "JPEG", 10, y, 190, 140);
                y += 150;
                checkPageBreak();
              }
            } catch (error) {
              console.warn("Failed to load first image:", error);
            }
          }

          // Property Details Header
          doc.setFontSize(18);
          doc.text("Property Details", 105, y, { align: "center" });
          y += 10;
          checkPageBreak();

          doc.setFontSize(12);
          doc.text("MONDUS GROUP", 105, y, { align: "center" });
          y += 10;
          checkPageBreak();

          // PROPERTY FEATURES
          doc.setFontSize(11);
          doc.setDrawColor(0);
          doc.setFillColor(240, 240, 240);
          doc.rect(14, y, 182, 8, "F");
          doc.setFont(undefined, "bold");
          doc.text("PROPERTY FEATURES", 16, y + 5);
          y += 15;
          checkPageBreak();

          doc.setFont(undefined, "normal");
          const propertyType = item.ufCrm7_1743829247734 || "N/A";
          const bedrooms = item.ufCrm7_1743829267783 || "N/A";
          const bathrooms = item.ufCrm7_1743829278192 || "N/A";
          const price = item.ufCrm7_1743829576957 || "N/A";
          const sqft = item.ufCrm7_1743829315467 || "N/A";

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

          // PROPERTY INFORMATION
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

          const emirate = mapEmirate(item.ufCrm7_1743829019488);
          doc.text(`Emirate: ${emirate || "N/A"}`, 16, y);
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
          y += 8;
          checkPageBreak();
          doc.text(`Price: AED ${price}`, 16, y);
          y += 16;
          checkPageBreak();

          // PROPERTY IMAGES
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
                const imgData = await getBase64ImageFromURL(
                  imageObj.urlMachine
                );
                doc.addImage(imgData, "JPEG", x, y, imgSize, imgSize);
              } catch (error) {
                console.warn(
                  "Failed to load image:",
                  imageObj.urlMachine,
                  error
                );
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

          // Footer with timestamp
          doc.setFontSize(8);
          doc.text(
            "Generated on " + new Date().toLocaleString(),
            105,
            pageHeight - 10,
            { align: "center" }
          );

          // Save PDF
          const fileName = `Mondus_Property_${id}_${new Date().toISOString()}.pdf`;
          doc.save(fileName);

          showSuccess("PDF downloaded successfully");
        } catch (error) {
          console.error("Error generating PDF:", error);
          showError("Failed to download PDF");
        }
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
