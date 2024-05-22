// Using Leaflet.js to display Open Street Maps
import L from "leaflet";
import "leaflet-control-geocoder/dist/Control.Geocoder.js";

document.addEventListener("DOMContentLoaded", () => {
  const street = document.getElementById("map").getAttribute("data-street");
  const number = document.getElementById("map").getAttribute("data-number");
  const zipcode = document.getElementById("map").getAttribute("data-zipcode");
  const locality = document.getElementById("map").getAttribute("data-locality");
  const country = document.getElementById("map").getAttribute("data-country");

  const address = `${street} ${number}, ${zipcode} ${locality}, ${country}`;

  const mapContainer = document.getElementById("map");
  const map = L.map("map").setView([0, 0], 15);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution:
      '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  const geocoder = L.Control.Geocoder.nominatim();

  geocoder.geocode(address, (results) => {
    if (results.length === 0) {
      const errorMessage = document.querySelector(".error-message");
      mapContainer.parentNode.replaceChild(errorMessage, mapContainer);
      errorMessage.classList.add("show");
    } else {
      map.setView(results[0].center);
      L.marker(results[0].center).addTo(map);
    }
  });
});
