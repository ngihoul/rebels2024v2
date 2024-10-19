document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('confirmation-modal');
    const switchAccountModal = document.getElementById('switch-account-modal');

    const confirmButtons = document.querySelectorAll('.confirm-button');
    const cancelButtons = document.querySelectorAll('.cancel-button');

    console.log(cancelButtons);

    const switchAccountMenu = document.querySelector('#switch-account-menu');

    const openModal = (modal, actionUrl) => {
        modal.classList.add('open');

        confirmButtons.forEach(b => {
            b.addEventListener('click', function () {
                window.location.href = actionUrl
            })
        });

        window.onclick = function (event) {
            if (event.target === modal) {
                closeModal(modal);
            }
        };
    };

    const closeModal = (modal) => {
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

    cancelButtons.forEach(b => b.addEventListener('click', function () {
        closeModal(deleteModal);
        closeModal(switchAccountModal);
    }));

    document.querySelectorAll('.delete-button').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const actionUrl = this.getAttribute('data-action-url');
            openModal(deleteModal, actionUrl);
        });
    });

    document
        .querySelectorAll('.switch-account-menu-item')
        .forEach(function (item) {
            item.addEventListener('click', function (event) {
                event.preventDefault();
                const actionUrl = this.getAttribute('data-action-url');
                openCloseSwitchAccountmenu();
                openModal(switchAccountModal, actionUrl);
            });
        });
});
