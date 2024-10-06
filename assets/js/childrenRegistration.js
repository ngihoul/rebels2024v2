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
        childDiv.innerHTML =
            `<h2>{{ 'register.children_data'|trans }} #${childNumber}</h2>` +
            newChildForm;

        childrenList.appendChild(childDiv);

        index++;
        childNumber++;

        updateRemoveButtonState();
        toggleAddressFields();
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
    });

    updateRemoveButtonState();
    toggleAddressFields();
});
