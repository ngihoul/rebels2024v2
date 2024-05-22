document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("hamburger-icon")) {
    const hamburgerIcon = document.getElementById("hamburger-icon");
    const closeIcon = document.getElementById("close-icon");
    const menu = document.querySelector(".menu");

    // Open menu
    hamburgerIcon.addEventListener("click", function () {
      menu.classList.add("menu-open");
      menu.classList.remove("menu-closed");
    });

    // Close menu
    closeIcon.addEventListener("click", function () {
      menu.classList.remove("menu-open");
      menu.classList.add("menu-closed");
    });
  }

  const container = document.querySelector(".container");
  const nav = document.querySelector("nav");
  // Adapt main container size if desktop or mobile version
  if (nav) {
    container.classList.add("nav-open");
    container.classList.remove("nav-closed");
  } else {
    container.classList.add("nav-closed");
    container.classList.remove("nav-open");
  }
});
