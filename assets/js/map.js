// Necessary Leaflet scripts & CSS are loaded from place/detail.html.twig
document.addEventListener("DOMContentLoaded", () => {
  const street = document.getElementById("map").getAttribute("data-street");
  const number = document.getElementById("map").getAttribute("data-number");
  const zipcode = document.getElementById("map").getAttribute("data-zipcode");
  const locality = document.getElementById("map").getAttribute("data-locality");
  const country = document.getElementById("map").getAttribute("data-country");

  const address = `${street} ${number}, ${zipcode} ${locality}, ${country}`;

  const map = L.map("map").setView([0, 0], 15);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution:
      '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  const geocoder = L.Control.Geocoder.nominatim();
  geocoder.geocode(address, (results) => {
    map.setView(results[0].center);
    L.marker(results[0].center).addTo(map);
  });
});
