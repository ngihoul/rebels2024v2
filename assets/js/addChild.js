document.addEventListener('DOMContentLoaded', function () {
    const toggleAddressFields = () => {
        const sameAddressCheckbox = document.querySelector(
            '.same-address-as-parent > input',
        );

        if (sameAddressCheckbox) {
            sameAddressCheckbox.addEventListener('change', function () {
                const addressFields = document.querySelectorAll(
                    '.address-street, .address-number, .zipcode, .locality, .country',
                );

                addressFields.forEach(field => {
                    field.style.display = sameAddressCheckbox.checked
                        ? 'none'
                        : 'block';
                });
            });
        }
    };

    const calculateAge = dateOfBirth => {
        const currentDate = new Date();

        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth();
        const currentDay = currentDate.getDate();

        const birthYear = dateOfBirth.getFullYear();
        const birthMonth = dateOfBirth.getMonth();
        const birthDay = dateOfBirth.getDate();

        let age = currentYear - birthYear;

        if (
            currentMonth < birthMonth ||
            (currentMonth === birthMonth && currentDay < birthDay)
        ) {
            age--;
        }

        return age;
    };

    // Calculate age from date of birth

    const toggleCanUseAppFields = () => {
        const dateOfBirthInput = document.querySelector(
            '.date-of-birth > input',
        );
        const canUseAppInput = document.querySelector('.can-use-app > input');

        dateOfBirthInput.addEventListener('change', () => {
            const age = calculateAge(new Date(dateOfBirthInput.value));
            console.log(age);
            if (age >= 16 && age < 18) {
                canUseAppInput.parentNode.classList.remove('hidden');
            } else {
                canUseAppInput.parentNode.classList.add('hidden');
            }

            toggleRequiredOnEmailFields();
        });
    };

    const toggleRequiredOnEmailFields = () => {
        const emailsInput = document.querySelector('.email > input');

        const canUseAppInput = document.querySelector('.can-use-app > input');

        canUseAppInput.addEventListener('change', () => {
            if (canUseAppInput.checked) {
                emailsInput.setAttribute('required', true);
                emailsInput.parentNode.firstChild.innerHTML = 'Email *';
            } else {
                emailsInput.setAttribute('required', false);
                emailsInput.parentNode.firstChild.innerHTML = 'Email';
            }
        });
    };

    // For update child profile
    const UserCanUseAppInput = document.getElementById('user_can_use_app');

    if (UserCanUseAppInput) {
        if (UserCanUseAppInput.checked) {
            document.getElementById('user_email').required = true;
        }

        UserCanUseAppInput.addEventListener('change', () => {
            if (UserCanUseAppInput.checked) {
                document.getElementById('user_email').required = true;
            } else {
                document.getElementById('user_email').required = false;
            }
        });
    }

    toggleAddressFields();
    toggleCanUseAppFields();
    toggleRequiredOnEmailFields;
});
