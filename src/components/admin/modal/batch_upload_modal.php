<!-- Batch Upload Modal -->
<div id="addStudentBatchModal" class="fixed inset-0 overflow-y-auto h-full w-full hidden z-50" onclick="if(event.target==this)this.classList.add('hidden')">
    <!-- Use the global overlay style -->
    <div id="drawerOverlay" class="absolute inset-0 opacity-50 bg-black"></div>
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white z-10">
        <div class="flex flex-col items-start">
            <!-- Header -->
            <div class="flex items-center justify-between w-full">
                <h3 class="text-2xl font-bold text-gray-900">Upload Students</h3>
                <button onclick="document.getElementById('addStudentBatchModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Upload Form -->
            <form action="/faculty_evaluation/src/actions/BatchStudentUpload.php" method="POST" enctype="multipart/form-data" class="w-full">
                <div class="mt-6 w-full">
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col w-full h-32 border-4 border-dashed border-blue-200 hover:bg-gray-50 hover:border-blue-300 rounded-lg cursor-pointer">
                            <div class="flex flex-col items-center justify-center pt-7">
                                <svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="pt-1 text-sm tracking-wider text-gray-600 group-hover:text-gray-600">
                                    Select Excel/CSV file
                                </p>
                            </div>
                            <input type="file" name="batch_file" id="fileUpload" class="opacity-0" accept=".xlsx,.xls,.csv" required onchange="handleFilePreview(this)" />
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Accepted formats: .xlsx, .xls, .csv</p>
                </div>

                <!-- Preview Section -->
                <div class="mt-6 w-full">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Preview</h4>
                    <div id="previewTable" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Middle Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birthdate</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strand</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade Level</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject Codes</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody" class="bg-white divide-y divide-gray-200">
                                <!-- Preview data will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4 mt-8 w-full">
                    <button type="button" onclick="document.getElementById('addStudentBatchModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-400">
                        Cancel
                    </button>
                    <button id="uploadButton" type="submit" name="upload"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Upload Students
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    // File input check (no debug logs in production)
    (function() {
        const fi = document.getElementById('fileUpload');
        if (fi) {
            // attached preview handler via onchange attribute
        }
    })();
</script>