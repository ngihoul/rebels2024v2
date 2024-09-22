/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.scss";
// Leaflet CSS for Open Street Map
import "leaflet/dist/leaflet.css";

// Service worker registration
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/sw.js")
      .then((registration) => {
        console.log("Service Worker enregistré avec succès :", registration);
      })
      .catch((error) => {
        console.log("Échec de l'enregistrement du Service Worker :", error);
      });
  });
}

// Install PWA
let deferredPrompt;

window.addEventListener("beforeinstallprompt", (event) => {
  event.preventDefault();
  deferredPrompt = event;
  console.log("BeforeInstall : OK");
});

function showInstallPromotion() {}

document.getElementById("installButton").addEventListener("click", () => {
  console.log("Click Install btn");
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then((choiceResult) => {
      if (choiceResult.outcome === "accepted") {
        console.log("Utilisateur a accepté d'installer l'application");
      } else {
        console.log("Utilisateur a refusé l'installation de l'application");
      }
      deferredPrompt = null;
    });
  }
});
