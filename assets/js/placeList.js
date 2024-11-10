document.addEventListener("DOMContentLoaded", () => {
  // get locale
  const localeContainer = document.querySelector(".locale-container");
  const locale = localeContainer.dataset.locale;

  // Link on <tr> redirectin to member's profile
  const tableRows = document.querySelectorAll(".place-row");
  tableRows.forEach((row) => {
    row.addEventListener("click", () => {
      const placeId = row.dataset.placeId;
      if (placeId) {
        window.location.href = `/${locale}/places/${placeId}`;
      }
    });
    // bold when cursor on tr
    row.addEventListener("mouseover", () => {
      row.classList.add("focus");
    });
    // unbold when cursor out of tr
    row.addEventListener("mouseout", () => {
      row.classList.remove("focus");
    });
  });
});
