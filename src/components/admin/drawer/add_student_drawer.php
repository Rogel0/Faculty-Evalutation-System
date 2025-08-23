<?php
include_once('../config/database.php');
?>


<div class="flex">
    <div class="fixed inset-0 bg-black bg-opacity-30 z-10 hidden" id="drawerOverlay"></div>


    <div class="fixed top-0 right-0 z-20 w-2/5 h-full transition-all duration-500 transform translate-x-full bg-white shadow-lg" id="drawerContent">
        <div class="px-6 py-4 flex flex-col h-full">

            <div class="flex justify-start border-b border-gray-200 p-4">
                <h2 class="text-lg font-semibold">New Student Evaluator</h2>
            </div>


            <div class="flex-1 p-4">
                <form action="../actions/AddStudent.php" method="POST" class="space-y-8">
                    <div class="bg-white rounded-2xl shadow p-8 mb-6 border border-gray-200">
                        <h3 class="text-xl font-bold text-amber-700 mb-6 flex items-center gap-3">
                            <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.657 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Student Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label for="student_id" class="block text-base font-semibold text-gray-700 mb-2">Student ID</label>
                                <div class="relative">
                                    <input type="text" name="student_id" id="student_id" required class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-50 transition-all px-4 py-3 text-gray-900 placeholder-gray-400 focus:bg-gray-50 peer" placeholder="Enter student ID" oninput="this.nextElementSibling.style.opacity = this.value ? '1' : '0'">
                                    <span class="absolute right-3 top-3 text-amber-500 opacity-0 transition-opacity duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="lastname" class="block text-base font-semibold text-gray-700 mb-2">Last Name</label>
                                <div class="relative">
                                    <input type="text" name="lastname" id="lastname" required class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-50 transition-all px-4 py-3 text-gray-900 placeholder-gray-400 focus:bg-gray-50 peer" placeholder="Enter last name" oninput="this.nextElementSibling.style.opacity = this.value ? '1' : '0'">
                                    <span class="absolute right-3 top-3 text-amber-500 opacity-0 transition-opacity duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="firstname" class="block text-base font-semibold text-gray-700 mb-2">First Name</label>
                                <div class="relative">
                                    <input type="text" name="firstname" id="firstname" required class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-50 transition-all px-4 py-3 text-gray-900 placeholder-gray-400 focus:bg-gray-50 peer" placeholder="Enter first name" oninput="this.nextElementSibling.style.opacity = this.value ? '1' : '0'">
                                    <span class="absolute right-3 top-3 text-amber-500 opacity-0 transition-opacity duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="middlename" class="block text-base font-semibold text-gray-700 mb-2">Middle Name <span class="text-xs text-gray-400">(optional)</span></label>
                                <div class="relative">
                                    <input type="text" name="middlename" id="middlename" class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-50 transition-all px-4 py-3 text-gray-900 placeholder-gray-400 focus:bg-gray-50 peer" placeholder="Enter middle name (optional)" oninput="this.nextElementSibling.style.opacity = this.value ? '1' : '0'">
                                    <span class="absolute right-3 top-3 text-amber-500 opacity-0 transition-opacity duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="course" class="block text-base font-semibold text-gray-700 mb-2">Course</label>
                                <div class="relative">
                                    <select name="course" id="course" required class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-50 transition-all px-4 py-3 text-gray-900 focus:bg-gray-50 peer" onchange="this.nextElementSibling.style.opacity = this.value ? '1' : '0'">
                                        <option value="">Select course</option>
                                        <option value="BSIT">BSIT</option>
                                        <option value="BSCS">BSCS</option>
                                        <option value="BSEd">BSEd</option>
                                        <option value="BSBA">BSBA</option>
                                        <option value="BSA">BSA</option>
                                        <option value="BSN">BSN</option>
                                        <option value="BSChem">BSChem</option>
                                    </select>
                                    <span class="absolute right-3 top-3 text-amber-500 opacity-0 transition-opacity duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="email" class="block text-base font-semibold text-gray-700 mb-2">Email</label>
                                <div class="relative">
                                    <input type="email" name="email" id="email" required class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-50 transition-all px-4 py-3 text-gray-900 placeholder-gray-400 focus:bg-gray-50 peer" placeholder="Enter email address" oninput="this.nextElementSibling.style.opacity = this.value ? '1' : '0'">
                                    <span class="absolute right-3 top-3 text-amber-500 opacity-0 transition-opacity duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="birthdate" class="block text-base font-semibold text-gray-700 mb-2">Birth Date</label>
                                <div class="relative">
                                    <input type="date" name="birthdate" id="birthdate" required class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-50 transition-all px-4 py-3 text-gray-900 placeholder-gray-400 focus:bg-gray-50 peer" oninput="this.nextElementSibling.style.opacity = this.value ? '1' : '0'">
                                    <span class="absolute right-3 top-3 text-amber-500 opacity-0 transition-opacity duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl shadow p-8 border border-gray-200">
                        <h3 class="text-xl font-bold text-blue-700 mb-6 flex items-center gap-3">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12v1m0 4v1m-8-5v1m0 4v1m8-10V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m0 10a2 2 0 002 2h4a2 2 0 002-2v-2" />
                            </svg>
                            Account Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label for="username" class="block text-base font-semibold text-gray-700 mb-2">Account Username</label>
                                <div class="relative">
                                    <input type="text" name="username" id="username" required class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-50 transition-all px-4 py-3 text-gray-900 placeholder-gray-400 focus:bg-gray-50 peer" placeholder="Enter username" oninput="this.nextElementSibling.style.opacity = this.value ? '1' : '0'">
                                    <span class="absolute right-3 top-3 text-blue-500 opacity-0 transition-opacity duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label for="password" class="block text-base font-semibold text-gray-700 mb-2">Account Password</label>
                                <div class="relative">
                                    <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-lg border border-gray-300 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-200 focus:ring-opacity-50 transition-all px-4 py-3 text-gray-900 placeholder-gray-400 focus:bg-gray-50 peer" placeholder="Enter password" oninput="this.nextElementSibling.style.opacity = this.value ? '1' : '0'">
                                    <span class="absolute right-3 top-3 text-blue-500 opacity-0 transition-opacity duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>


            </div>

            <div class="mt-auto flex justify-end gap-4 p-6 border-t border-gray-100 bg-white">
                <button id="closeDrawerBtn" type="button" class="flex items-center gap-2 px-6 py-2 rounded-lg font-semibold bg-gray-200 text-gray-700 shadow hover:bg-gray-300 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close
                </button>

                <button type="submit" class="flex items-center gap-2 px-8 py-3 rounded-lg font-bold bg-amber-600 text-white shadow hover:bg-amber-700 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Student
                </button>

            </div>
            </form>
        </div>
    </div>
</div>
<style>
    #drawerOverlay {
        opacity: 0.5;
        transition: opacity 0.3s ease-in-out;
        background-color: black;
    }
</style>