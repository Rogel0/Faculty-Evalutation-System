const form = document.querySelector("form");
const loadingScreen = document.getElementById("loadingScreen");

form.addEventListener("submit", function (event) {
  event.preventDefault();
  loadingScreen.style.display = "flex";

  setTimeout(() => {
    loadingScreen.style.display = "none";
    form.submit();
  }, 3000);
});
