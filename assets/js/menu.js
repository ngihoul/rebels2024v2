if (document.getElementById("hamburger-icon")) {
  const hamburgerIcon = document.getElementById("hamburger-icon");
  const closeIcon = document.getElementById("close-icon");
  const menu = document.querySelector(".menu");

  hamburgerIcon.addEventListener("click", function () {
    menu.classList.add("menu-open");
  });

  closeIcon.addEventListener("click", function () {
    menu.classList.remove("menu-open");
  });
}

const container = document.querySelector(".container");
const nav = document.querySelector("nav");

if (nav) {
  container.classList.add("nav-open");
  container.classList.remove("nav-closed");
} else {
  container.classList.add("nav-closed");
  container.classList.remove("nav-open");
}
