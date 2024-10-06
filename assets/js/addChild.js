document.addEventListener('DOMContentLoaded', function () {
    const toggleAddressFields = () => {
        const sameAddressCheckbox = document.querySelector(
            '.same-address-as-parent > input',
        );
        console.log(sameAddressCheckbox);

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
    };

    const calculateAge = dateOfBirth => {
        const diffMs = Date.now() - dateOfBirth.getTime();
        const ageDt = new Date(diffMs);
        return Math.abs(ageDt.getUTCFullYear() - 1970);
    };

    const toggleCanUseAppFields = () => {
        const dateOfBirthInput = document.querySelector(
            '.date-of-birth > input',
        );
        const canUseAppInput = document.querySelector('.can-use-app > input');

        dateOfBirthInput.addEventListener('change', () => {
            const age = calculateAge(new Date(dateOfBirthInput.value));

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
            ('can use app is changed');
            if (canUseAppInput.checked) {
                emailsInput.setAttribute('required', true);
                emailsInput.parentNode.firstChild.innerHTML = 'Email *';
            } else {
                emailsInput.setAttribute('required', false);
                emailsInput.parentNode.firstChild.innerHTML = 'Email';
            }
        });
    };

    toggleAddressFields();
    toggleCanUseAppFields();
    toggleRequiredOnEmailFields;
});
