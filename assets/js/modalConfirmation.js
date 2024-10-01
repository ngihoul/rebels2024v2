document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('confirmation-modal');
    const confirmButton = document.getElementById('confirm-button');
    const cancelButton = document.getElementById('cancel-button');

    const switchAccountMenu = document.querySelector('#switch-account-menu');

    const openModal = actionUrl => {
        modal.classList.add('open');

        confirmButton.onclick = function () {
            window.location.href = actionUrl;
        };

        window.onclick = function (event) {
            if (event.target === modal) {
                closeModal();
            }
        };
    };

    const closeModal = () => {
        modal.classList.remove('open');
    };

    const openCloseSwitchAccountmenu = () => {
        if (switchAccountMenu.classList.contains('menu-open')) {
            switchAccountMenu.classList.remove('menu-open');
            switchAccountMenu.classList.add('menu-closed');
        } else {
            switchAccountMenu.classList.remove('menu-closed');
            switchAccountMenu.classList.add('menu-open');
        }
    };

    cancelButton.addEventListener('click', function () {
        closeModal();
    });

    document.querySelectorAll('.delete-button').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const actionUrl = this.getAttribute('data-action-url');
            openModal(actionUrl);
        });
    });

    document
        .querySelectorAll('.switch-account-menu-item')
        .forEach(function (item) {
            item.addEventListener('click', function (event) {
                event.preventDefault();
                const actionUrl = this.getAttribute('data-action-url');
                openCloseSwitchAccountmenu();
                openModal(actionUrl);
            });
        });
});
