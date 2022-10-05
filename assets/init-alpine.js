function data () {
    function getThemeFromLocalStorage () {
        // if user already changed the theme, use it
        if (window.localStorage.getItem('dark')) {
            return JSON.parse(window.localStorage.getItem('dark'))
        }

        // else return their preferences
        return (
            !!window.matchMedia &&
            window.matchMedia('(prefers-color-scheme: dark)').matches
        )
    }

    function setThemeToLocalStorage (value) {
        window.localStorage.setItem('dark', value)
    }

    return {
        dark: getThemeFromLocalStorage(),
        toggleTheme () {
            this.dark = !this.dark
            setThemeToLocalStorage(this.dark)
        },
        isSideMenuOpen: false,
        toggleSideMenu () {
            this.isSideMenuOpen = !this.isSideMenuOpen
        },
        closeSideMenu () {
            this.isSideMenuOpen = false
        },
        isNotificationsMenuOpen: false,
        toggleNotificationsMenu () {
            this.isNotificationsMenuOpen = !this.isNotificationsMenuOpen
        },
        closeNotificationsMenu () {
            this.isNotificationsMenuOpen = false
        },
        isProfileMenuOpen: false,
        toggleProfileMenu () {
            this.isProfileMenuOpen = !this.isProfileMenuOpen
        },
        closeProfileMenu () {
            this.isProfileMenuOpen = false
        },
        isPagesMenuOpen: false,
        togglePagesMenu () {
            this.isPagesMenuOpen = !this.isPagesMenuOpen
        },
        // Modal
        isModalOpen: {},
        trapCleanup: null,
        openModal (modalName) {
            this.isModalOpen[modalName] = true
            this.trapCleanup = focusTrap(document.querySelector('#modal' + modalName))
        },
        closeModal (modalName) {
            this.isModalOpen[modalName] = false
            this.trapCleanup()
        },
        confirm: {},
        run (link, confirmation, actions) {
            if (typeof actions === 'object') {
                for (action in actions) {
                    if (link.includes(actions[action])) {
                        this.openModal('confirm')
                        confirmation.dataset.href = link
                        return false;
                    }
                }
            }
            location.href = link
            return true;
        }
    }
}
