document.addEventListener("DOMContentLoaded", () => {
  const password = document.getElementById("registration_form_password_first");
  const passwordConfirmation = document.getElementById(
    "registration_form_password_second"
  );
  const alertChars = document.querySelector(".chars");
  const alertCapital = document.querySelector(".capital");
  const alertLower = document.querySelector(".lower");
  const alertSpecial = document.querySelector(".special");
  const alertIdentical = document.querySelector(".identical");

  // Define password rules
  const hasMin8chars = (string) => string.length >= 8;
  const hasMinOneCapital = (string) => /[A-Z]/.test(string);
  const hasMinOneLowerCase = (string) => /[a-z]/.test(string);
  const hasMinOneSpecialChar = (string) => /[!?#@,.:;]/.test(string);
  const arePasswordsIdentical = () => {
    return password.value === passwordConfirmation.value;
  };

  // Adapt css class
  const addOrRemoveValidatedClass = (element, test) => {
    if (element) {
      element.classList.toggle("validated", test);
    }
  };

  if (password && passwordConfirmation) {
    password.addEventListener("input", () => {
      const value = password.value;
      addOrRemoveValidatedClass(alertChars, hasMin8chars(value));
      addOrRemoveValidatedClass(alertCapital, hasMinOneCapital(value));
      addOrRemoveValidatedClass(alertLower, hasMinOneLowerCase(value));
      addOrRemoveValidatedClass(alertSpecial, hasMinOneSpecialChar(value));
      addOrRemoveValidatedClass(alertIdentical, arePasswordsIdentical());
    });

    passwordConfirmation.addEventListener("input", () => {
      addOrRemoveValidatedClass(alertIdentical, arePasswordsIdentical());
    });
  }
});
