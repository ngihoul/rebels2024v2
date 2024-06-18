document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("confirmation-modal");
  const closeButton = document.querySelector(".close-button");
  const confirmButton = document.getElementById("confirm-button");
  const cancelButton = document.getElementById("cancel-button");

  document.querySelectorAll(".delete-button").forEach(function (button) {
    button.addEventListener("click", function (event) {
      event.preventDefault();
      const teamId = this.getAttribute("data-team-id");
      const playerId = this.getAttribute("data-player-id");

      modal.classList.add("open");

      confirmButton.onclick = function () {
        window.location.href = `${teamId}/remove/${playerId}`;
      };

      closeButton.onclick = cancelButton.onclick = function () {
        modal.classList.remove("open");
      };

      window.onclick = function (event) {
        if (event.target === modal) {
          modal.classList.remove("open");
        }
      };
    });
  });
});
