       <div id="addCriteriaModalSupervisor" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:1000;" onclick="if(event.target==this)this.style.display='none'">
           <div style="background:#fff;padding:2.5rem 2rem;border-radius:16px;max-width:420px;width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.18);margin:10vh auto;position:relative;">
               <button type="button" onclick="document.getElementById('addCriteriaModalSupervisor').style.display='none'" style="position:absolute;top:1rem;right:1rem;background:transparent;border:none;font-size:1.5rem;line-height:1;color:#888;cursor:pointer;">&times;</button>
               <h3 class="text-xl font-bold mb-4 text-gray-800 text-center">Add Criteria</h3>
               <form action="../actions/AddCriteriaStudent.php" method="POST">
                   <input type="hidden" name="evaluator_type" value="supervisor">
                   <div class="mb-4">
                       <label for="criteria-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                       <input type="text" name="criteria-name" id="criteria-name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                   </div>
                   <div class="mb-4">
                       <label for="criteria-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                       <textarea name="criteria-description" id="criteria-description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                   </div>
                   <div class="flex justify-end gap-2 mt-2">
                       <button type="button" onclick="document.getElementById('addCriteriaModalSupervisor').style.display='none'" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</button>
                       <button type="submit" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 font-semibold">Add</button>
                   </div>
               </form>
           </div>
       </div>