document.addEventListener("DOMContentLoaded", () => {
  const teamDropdowns = document.querySelectorAll(".container-team h2");

  teamDropdowns.forEach((dropdown) => {
    dropdown.addEventListener("click", () => {
      const teamCards = dropdown.nextElementSibling;

      document.querySelectorAll(".team-cards").forEach((cards) => {
        const icon = cards.previousElementSibling.querySelector("svg");
        cards.style.display =
          cards !== teamCards
            ? "none"
            : teamCards.style.display === "none" ||
              teamCards.style.display === ""
            ? "flex"
            : "none";
        if (icon) {
          icon.classList.toggle(
            "fa-rotate-180",
            cards === teamCards && teamCards.style.display === "flex"
          );
        }
      });
    });
  });
});
