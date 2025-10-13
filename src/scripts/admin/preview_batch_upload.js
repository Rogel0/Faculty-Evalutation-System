function handleFilePreview(input) {
  const file = input.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append("file", file);
  formData.append("preview", "true");

  // Show loading state
  document.getElementById("previewTableBody").innerHTML = `
        <tr>
            <td colspan="7" class="px-6 py-4 text-center">
                <div class="flex justify-center items-center">
                    <div class="spinner"></div>
                    <span class="ml-2">Loading preview...</span>
                </div>
            </td>
        </tr>
    `;

  fetch("/faculty_evaluation/src/actions/BatchStudentUpload.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.text())
    .then((text) => {
      let data;
      try {
        data = JSON.parse(text);
      } catch (err) {
        document.getElementById("previewTableBody").innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-red-600">
                        ${text}
                    </td>
                </tr>
            `;
        return;
      }

      if (data.error) {
        document.getElementById("previewTableBody").innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-red-600">
                        ${data.error}
                    </td>
                </tr>
            `;
        return;
      }

      const tableBody = document.getElementById("previewTableBody");
      tableBody.innerHTML = "";

      data.preview.forEach((student) => {
        const row = document.createElement("tr");
        // PHP preview returns keys: student_id, firstname, lastname, middle_name, email, birthdate, strand, grade_level, subjects (array)
        const subjects = Array.isArray(student.subjects)
          ? student.subjects.join(", ")
          : student.subject_codes || "";
        row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${
                  student.student_id || ""
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${
                  student.firstname || ""
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${
                  student.lastname || ""
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${
                  student.middlename || ""
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${
                  student.email || ""
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${
                  student.birthdate || ""
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${
                  student.strand || ""
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${
                  student.grade_level || ""
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${subjects}</td>
            `;
        tableBody.appendChild(row);
      });
    })
    .catch((error) => {
      document.getElementById("previewTableBody").innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-red-600">
                    An error occurred while loading the preview.
                </td>
            </tr>
        `;
      console.error("Error:", error);
    });
}
