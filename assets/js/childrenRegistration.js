document.addEventListener('DOMContentLoaded', function () {
    const toggleAddressFields = () => {
        const sameAddressCheckbox = document.querySelectorAll(
            '.same-address-as-parent',
        );

        for (let i = 0; i < sameAddressCheckbox.length; i++) {
            sameAddressCheckbox[i].addEventListener('change', function () {
                const addressFields = sameAddressCheckbox[i]
                    .closest('.form-user')
                    .querySelectorAll(
                        '.address-street, .address-number, .zipcode, .locality, .country',
                    );

                addressFields.forEach(field => {
                    field.style.display = sameAddressCheckbox[i].children[1]
                        .checked
                        ? 'none'
                        : 'block';
                });
            });
        }
    };

    const updateRemoveButtonState = () => {
        if (index > 1) {
            removeChildButton.disabled = false;
        } else {
            removeChildButton.disabled = true;
        }
    };

    const calculateAge = dateOfBirth => {
        const diffMs = Date.now() - dateOfBirth.getTime();
        const ageDt = new Date(diffMs);
        return Math.abs(ageDt.getUTCFullYear() - 1970);
    };

    const toggleCanUseAppFields = () => {
        const dateOfBirthInputs = document.querySelectorAll(
            '.date-of-birth > input',
        );
        const canUseAppInputs = document.querySelectorAll(
            '.can-use-app > input',
        );

        for (let i = 0; i < dateOfBirthInputs.length; i++) {
            dateOfBirthInputs[i].addEventListener('change', () => {
                const age = calculateAge(new Date(dateOfBirthInputs[i].value));

                if (age >= 16 && age < 18) {
                    canUseAppInputs[i].parentNode.classList.remove('hidden');
                } else {
                    canUseAppInputs[i].parentNode.classList.add('hidden');
                }

                toggleRequiredOnEmailFields();
            });
        }
    };

    const checkIfCanUseAppisChecked = () => {
        const canUseAppInputs = document.querySelectorAll(
            '.can-use-app > input',
        );

        canUseAppInputs.forEach(input => {
            input.addEventListener('change', () => {
                toggleRequiredOnEmailFields();
            });
        });
    };

    const toggleRequiredOnEmailFields = () => {
        const emailsInputs = document.querySelectorAll('.email > input');

        const canUseAppInputs = document.querySelectorAll(
            '.can-use-app > input',
        );

        for (let i = 0; i < canUseAppInputs.length; i++) {
            canUseAppInputs[i].addEventListener('change', () => {
                ('can use app is changed');
                if (canUseAppInputs[i].checked) {
                    emailsInputs[i].setAttribute('required', true);
                    emailsInputs[i].parentNode.firstChild.innerHTML = 'Email *';
                } else {
                    emailsInputs[i].setAttribute('required', false);
                    emailsInputs[i].parentNode.firstChild.innerHTML = 'Email';
                }
            });
        }
    };

    // Add and remove children forms
    const addChildButton = document.getElementById('add-child');
    const removeChildButton = document.getElementById('remove-child');
    const childrenList = document.getElementById('children-list');
    const prototype = childrenList.getAttribute('data-prototype');
    let index = parseInt(
        document.querySelector('.children-nb').dataset.childrenNb,
    );
    let childNumber = index + 1;

    addChildButton.addEventListener('click', function () {
        const newChildForm = prototype.replace(/__name__/g, index);

        const childDiv = document.createElement('div');
        childDiv.classList.add('form-user');
        childDiv.innerHTML = `<h2>${title} #${childNumber}</h2>` + newChildForm;

        childrenList.appendChild(childDiv);

        index++;
        childNumber++;

        updateRemoveButtonState();
        toggleAddressFields();
        toggleCanUseAppFields();
        toggleRequiredOnEmailFields();
    });

    removeChildButton.addEventListener('click', function () {
        const childForms = childrenList.getElementsByClassName('form-user');
        if (childForms.length > 0) {
            childrenList.removeChild(childForms[childForms.length - 1]);
            index--;
            childNumber--;
        }

        updateRemoveButtonState();
        toggleAddressFields();
        toggleCanUseAppFields();
        toggleRequiredOnEmailFields();
    });

    updateRemoveButtonState();
    toggleAddressFields();
    toggleCanUseAppFields();
    toggleRequiredOnEmailFields();
});
