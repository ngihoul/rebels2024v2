document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('confirmation-modal');
    const closeButton = document.querySelector('.close-button');
    const confirmButton = document.getElementById('confirm-button');
    const cancelButton = document.getElementById('cancel-button');

    document.querySelectorAll('.delete-button').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            // const teamId = this.getAttribute("data-team-id");
            // const playerId = this.getAttribute("data-player-id");
            const actionUrl = this.getAttribute('data-action-url');

            modal.classList.add('open');

            confirmButton.onclick = function () {
                window.location.href = actionUrl;
            };

            closeButton.onclick = cancelButton.onclick = function () {
                modal.classList.remove('open');
            };

            window.onclick = function (event) {
                if (event.target === modal) {
                    modal.classList.remove('open');
                }
            };
        });
    });

    // Switch account
    const switchAccountMenuItem = document.querySelectorAll(
        '.switch-account-menu-item',
    );

    switchAccountMenuItem.forEach(item => {
        item.addEventListener('click', function (event) {
            event.preventDefault();

            const actionUrl = this.getAttribute('data-action-url');

            modal.classList.add('open');

            confirmButton.onclick = function () {
                window.location.href = actionUrl;
            };

            closeButton.onclick = cancelButton.onclick = function () {
                modal.classList.remove('open');
            };

            window.onclick = function (event) {
                if (event.target === modal) {
                    modal.classList.remove('open');
                }
            };

            confirmButton.onclick = () => {
                window.location.href = actionUrl;
            };
        });
    });
});
