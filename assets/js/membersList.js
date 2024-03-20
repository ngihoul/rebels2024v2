document.addEventListener("DOMContentLoaded", () => {
  // get locale
  const localeContainer = document.querySelector(".locale-container");
  const locale = localeContainer.dataset.locale;

  // get page number
  const path = window.location.pathname;
  const parts = path.split("/");
  let page = parts[parts.length - 1] == "members" ? 1 : parts[parts.length - 1];

  // Define initial sort direction
  const queryString = window.location.search;
  const params = new URLSearchParams(queryString);
  let direction = params.get("dir");
  // Avoid 'null' value in search query
  let simpleSearch = params.get("q") ?? "";

  // Replace null values with empty string for advanced search parameters
  const advancedSearchParameters = {
    firstname: params.get("firstname") ?? "",
    lastname: params.get("lastname") ?? "",
    gender: params.get("gender") ?? "",
    ageMin: params.get("ageMin") ?? "",
    ageMax: params.get("ageMax") ?? "",
    licenseStatus: params.get("licenseStatus") ?? "",
  };

  // Link on <tr> redirectin to member's profile
  const tableRows = document.querySelectorAll(".member-row");
  tableRows.forEach((row) => {
    row.addEventListener("click", () => {
      const memberId = row.dataset.memberId;
      if (memberId) {
        window.location.href = `/${locale}/profile/${memberId}`;
      }
    });
    // bold when cursor on tr
    row.addEventListener("mouseover", () => {
      row.classList.add("focus");
    });
    // unbold when cursor out of tr
    row.addEventListener("mouseout", () => {
      row.classList.remove("focus");
    });
  });

  // Sort table
  const tableTh = document.querySelectorAll("th");
  tableTh.forEach((th) => {
    th.addEventListener("click", () => {
      const orderBy = th.dataset.order;
      // reinitialize page number
      page = 1;
      direction = direction == "ASC" ? "DESC" : "ASC";

      if (orderBy) {
        const url = simpleSearch
          ? `/${locale}/members/${page}?q=${simpleSearch}&order=${orderBy}&dir=${direction}`
          : `/${locale}/members/${page}?firstname=${advancedSearchParameters.firstname}&lastname=${advancedSearchParameters.lastname}&gender=${advancedSearchParameters.gender}&ageMin=${advancedSearchParameters.ageMin}&ageMax=${advancedSearchParameters.ageMax}&licenseStatus=${advancedSearchParameters.licenseStatus}&order=${orderBy}&dir=${direction}`;
        window.location.href = url;
      }
    });
  });

  // Search engine
  const searchInput = document.querySelector(".search-input");
  const searchButton = document.querySelector(".search-btn");
  searchInput.value = simpleSearch;
  searchButton.addEventListener("click", (event) => {
    event.preventDefault();
    // reinitialize page number
    page = 1;
    const query = searchInput.value;
    window.location.href = `/${locale}/members/${page}?q=${query}`;
  });

  // Advanced Search Engine

  // Open advanced Search Engine
  const advancedSearchLinks = document.querySelectorAll(
    ".advanced-research-link"
  );
  const searchEngineBox = document.querySelector(".search-engine");
  const advancedSearchEngineBox = document.querySelector(
    ".advanced-search-engine"
  );
  advancedSearchLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      advancedSearchEngineBox.classList.add("active");
      searchEngineBox.classList.add("inactive");
    });
  });

  // Close advanced Search Engine
  const closeBtn = document.querySelector(".close-btn");
  closeBtn.addEventListener("click", () => {
    advancedSearchEngineBox.classList.remove("active");
    searchEngineBox.classList.remove("inactive");
  });
});
