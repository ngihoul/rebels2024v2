document.addEventListener('DOMContentLoaded', () => {
    // Main menu
    if (document.getElementById('hamburger-icon')) {
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');
        const menu = document.querySelector('#main-menu');

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
        '#children-dropdown-icon-mobile',
    );
    const closeSwitchAccountBtn = document.getElementById(
        'close-icon-switch-account',
    );
    const switchAccountMenu = document.querySelector('#switch-account-menu');

    const openSwitchAccountmenu = () => {
        if (switchAccountMenu.classList.contains('menu-open')) {
            switchAccountMenu.classList.remove('menu-open');
            switchAccountMenu.classList.add('menu-closed');
        } else {
            switchAccountMenu.classList.remove('menu-closed');
            switchAccountMenu.classList.add('menu-open');
        }
    };

    profilePicture.addEventListener('click', openSwitchAccountmenu);
    dropDownSwitchAccount.addEventListener('click', openSwitchAccountmenu);

    closeSwitchAccountBtn.addEventListener('click', () => {
        switchAccountMenu.classList.remove('menu-open');
        switchAccountMenu.classList.add('menu-closed');
    });

    // Switch account - Desktop
    const profilePictureDesktop = document.querySelector(
        '.profile-icon-desktop',
    );

    profilePictureDesktop.addEventListener('click', openSwitchAccountmenu);

    // Close menu if a click is done outside the menu
    document.addEventListener('click', event => {
        const isClickInsideMenu = switchAccountMenu.contains(event.target);
        const isClickOnProfileIcon =
            profilePicture.contains(event.target) ||
            profilePictureDesktop.contains(event.target);
        const isClickOnDropDownIcon = dropDownSwitchAccount.contains(
            event.target,
        );

        if (
            !isClickInsideMenu &&
            !isClickOnProfileIcon &&
            !isClickOnDropDownIcon
        ) {
            switchAccountMenu.classList.remove('menu-open');
            switchAccountMenu.classList.add('menu-closed');
        }
    });
});
