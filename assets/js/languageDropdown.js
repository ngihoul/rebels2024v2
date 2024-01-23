const dropdownToggle = document.querySelector(".dropdown-toggle");
const dropdownMenu = document.querySelector(".dropdown-menu");

dropdownToggle.addEventListener("click", (event) => {
  event.preventDefault();
  dropdownMenu.classList.toggle("hidden");
});
