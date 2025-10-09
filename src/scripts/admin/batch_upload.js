document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("addStudentBatchModal");
  const openModalBtn = document.getElementById("openBatchUploadBtn");
  const fileInput = document.getElementById("fileUpload");
  const previewTableBody = document.getElementById("previewTableBody");
  const uploadButton = document.getElementById("uploadButton");
  // use global showToast(message, type) from /src/scripts/toast.js

  let previewData = [];

  if (openModalBtn) {
    openModalBtn.addEventListener("click", () => {
      modal.classList.remove("hidden");
    });
  }

  if (fileInput) {
    fileInput.addEventListener("change", async (e) => {
      const file = e.target.files[0];
      if (!file) return;

      try {
        previewData = await readFileData(file);
        displayPreview(previewData);
        if (uploadButton) uploadButton.disabled = false;
      } catch (error) {
        console.error("Error reading file:", error);
        // show a friendly toast
        showToast("Error reading file. Please try again.", "error");
      }
    });
  }

  if (uploadButton) {
    uploadButton.addEventListener("click", async () => {
      if (previewData.length === 0) return;

      try {
        const formData = new FormData();
        formData.append("batch_file", fileInput.files[0]);

        const response = await fetch(
          "/faculty_evaluation/src/actions/BatchStudentUpload.php",
          {
            method: "POST",
            body: formData,
          }
        );

        const result = await response.json();

        if (result.success) {
          modal.classList.add("hidden");
          if (result.warnings && result.warnings.length > 0) {
            const msgs = result.warnings
              .map((w) => w.student_id + ": " + w.warnings.join(", "))
              .join(" | ");
            showToast("Upload completed with warnings: " + msgs, "error");
          } else {
            showToast("Students uploaded successfully", "success");
          }
        } else {
          // display server-provided error via toast (avoid alert/raw JSON)
          const msg = result && result.error ? result.error : "Upload failed";
          showToast(msg, "error");
        }
      } catch (error) {
        console.error("Error uploading:", error);
        showToast(
          error.message || "Error uploading file. Please try again.",
          "error"
        );
      }
    });
  }

  async function readFileData(file) {
    try {
      const formData = new FormData();
      formData.append("file", file);
      formData.append("preview", "true");

      const response = await fetch(
        "/faculty_evaluation/src/actions/BatchStudentUpload.php",
        {
          method: "POST",
          body: formData,
        }
      );

      const text = await response.text();
      let result;
      try {
        result = JSON.parse(text);
      } catch (err) {
        // don't display raw JSON — show friendly toast instead
        showToast(
          "Invalid server response during preview. Please try again.",
          "error"
        );
        throw new Error("Invalid server response");
      }

      if (result.error) {
        showToast(result.error, "error");
        throw new Error(result.error);
      }

      if (!result.preview) {
        showToast("Preview data not found", "error");
        throw new Error("Preview data not found");
      }

      return result.preview;
    } catch (error) {
      console.error("Preview error:", error);
      throw error;
    }
  }

  function displayPreview(data) {
    if (!Array.isArray(data) || data.length === 0) {
      previewTableBody.innerHTML =
        '<tr><td colspan="9" class="px-6 py-4 text-center text-gray-500">No preview data available</td></tr>';
      return;
    }

    let tableContent = "";
    for (const student of data) {
      const studentId = student.student_id || "N/A";
      const firstName = student.firstname || "";
      const lastName = student.lastname || "";
      const middleName = student.middle_name || "";
      const email = student.email || "N/A";
      const birthdate = student.birthdate || "N/A";
      const strand = student.strand || "N/A";
      const gradeLevel = student.grade_level || "N/A";
      const subjects = Array.isArray(student.subjects)
        ? student.subjects.join(", ")
        : student.subject_codes || "N/A";

      tableContent +=
        '<tr class="hover:bg-gray-50">' +
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
        studentId +
        "</td>" +
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
        firstName +
        "</td>" +
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
        lastName +
        "</td>" +
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
        middleName +
        "</td>" +
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
        email +
        "</td>" +
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
        birthdate +
        "</td>" +
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
        strand +
        "</td>" +
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
        gradeLevel +
        "</td>" +
        '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' +
        subjects +
        "</td>" +
        "</tr>";
    }

    previewTableBody.innerHTML = tableContent;
  }

  function showToast(toastElement, message) {
    if (!toastElement) {
      // Fallback: simple alert if toast element not present
      alert(message);
      return;
    }

    const msgEl = toastElement.querySelector(".message");
    if (msgEl) {
      msgEl.textContent = message;
    } else {
      // ensure we don't clobber structure -- append or set text
      if (toastElement.firstChild)
        toastElement.firstChild.textContent = message;
      else toastElement.textContent = message;
    }

    toastElement.classList.remove("hidden");
    setTimeout(() => toastElement.classList.add("hidden"), 3000);
  }
});
