const addInvoiceBtn = document.getElementById("addStudentBtn");
const DrawerContent = document.getElementById("drawerContent");
const closeDrawerBtn = document.getElementById("closeDrawerBtn");
const drawerOverlay = document.getElementById("drawerOverlay");

function openProductDrawer() {
  // Opening Product Drawer
  DrawerContent.classList.remove("translate-x-full");
  closeDrawerBtn.classList.add("translate-x-0");
  drawerOverlay.classList.remove("hidden");
  // Overlay is now visible
}

function closeProductDrawer() {
  // Closing Product Drawer
  DrawerContent.classList.remove("translate-x-0");
  DrawerContent.classList.add("translate-x-full");
  drawerOverlay.classList.add("hidden");
  // Overlay is now hidden
}

addInvoiceBtn.addEventListener("click", openProductDrawer);
closeDrawerBtn.addEventListener("click", closeProductDrawer);
drawerOverlay.addEventListener("click", closeProductDrawer);
