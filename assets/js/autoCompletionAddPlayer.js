document.addEventListener("DOMContentLoaded", () => {
  const userAutocomplete = document.getElementById("user-autocomplete");
  const autocompleteList = document.getElementById("autocomplete-list");
  const formUserInput = document.getElementById("add_user_to_team_user");

  userAutocomplete.addEventListener("input", () => {
    const inputValue = userAutocomplete.value.trim();

    if (inputValue.length >= 2) {
      fetch(`/fr/search_player?q=${inputValue}`)
        .then((response) => response.json())
        .then((data) => displayAutocompleteResults(data));
    } else {
      clearAutocompleteResults();
    }
  });

  // Display results
  const displayAutocompleteResults = (results) => {
    clearAutocompleteResults();
    autocompleteList.classList.add("active");

    const resultList = document.createElement("ul");
    resultList.className = "autocomplete-results list-group";

    results.forEach((user) => {
      const listItem = document.createElement("li");
      listItem.textContent = `${user.lastname} ${user.firstname} - ${
        user.gender
      } - ${formatDate(user.date_of_birth.date)}`;
      listItem.className = "list-group-item";
      listItem.setAttribute("data-user-id", user.id);

      listItem.addEventListener("click", () => {
        userAutocomplete.value = `${user.lastname} ${user.firstname} - ${
          user.gender
        } - ${formatDate(user.date_of_birth.date)}`;
        formUserInput.value = user.id;
        formUserInput.style.display = "none";
        clearAutocompleteResults();
      });

      resultList.appendChild(listItem);
    });

    autocompleteList.appendChild(resultList);
  };

  // Clear & hide list
  const clearAutocompleteResults = () => {
    autocompleteList.innerHTML = "";
    autocompleteList.classList.remove("active");
  };

  // Format date of Birth
  const formatDate = (dateString) => {
    const date = new Date(dateString);
    let day = date.getDate();
    let month = date.getMonth() + 1;
    const year = date.getFullYear();

    if (day < 10) {
      day = "0" + day;
    }
    if (month < 10) {
      month = "0" + month;
    }

    return `${day}/${month}/${year}`;
  };
});
