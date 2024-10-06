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
    toggleAddressFields();
});
