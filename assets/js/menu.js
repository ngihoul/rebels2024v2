document.addEventListener('DOMContentLoaded', () => {
    // Main menu
    if (document.getElementById('hamburger-icon')) {
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');
        const menu = document.querySelector('.menu');

        // Open menu
        hamburgerIcon.addEventListener('click', function () {
            menu.classList.add('menu-open');
            menu.classList.remove('menu-closed');
        });

        // Close menu
        closeIcon.addEventListener('click', function () {
            menu.classList.remove('menu-open');
            menu.classList.add('menu-closed');
        });
    }

    const container = document.querySelector('.container');
    const nav = document.querySelector('#main-menu');

    // Adapt main container size if desktop or mobile version
    if (nav) {
        container.classList.add('nav-open');
        container.classList.remove('nav-closed');
    } else {
        container.classList.add('nav-closed');
        container.classList.remove('nav-open');
    }

    // Switch account menu - Mobile
    const profilePicture = document.querySelector('.profile-icon-mobile');
    const dropDownSwitchAccount = document.querySelector(
        '.children-dropdown-icon',
    );
    const closeSwitchAccountBtn = document.getElementById(
        'close-icon-switch-account',
    );
    const switchAccountMenu = document.querySelector('#switch-account-menu');

    const openSwitchAccountmenu = () => {
        switchAccountMenu.classList.add('menu-open');
        switchAccountMenu.classList.remove('menu-closed');
    };

    profilePicture.addEventListener('click', openSwitchAccountmenu);
    dropDownSwitchAccount.addEventListener('click', openSwitchAccountmenu);

    closeSwitchAccountBtn.addEventListener('click', () => {
        switchAccountMenu.classList.remove('menu-open');
        switchAccountMenu.classList.add('menu-closed');
    });
});
