// sw.js
// Doc : https://developer.mozilla.org/fr/docs/Web/Progressive_web_apps/Tutorials/CycleTracker/Service_workers

const VERSION = "v1.0";
const CACHE_NAME = `clubhouse-cache-${VERSION}`;

const APP_STATIC_RESOURCES = [
  "/",
  "/build/app.css",
  "/build/app.js",
  "/images/logo.png",
  "/images/logo_R.png",
  "/images/icons/icon_64.svg",
  "/images/icons/icon_180.svg",
  "/images/icons/icon_192.svg",
  "/images/icons/icon_512.svg",
  "/manifest.json",
];

// Save the current static ressources based on the cache name
self.addEventListener("install", (event) => {
  event.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_NAME);
      cache.addAll(APP_STATIC_RESOURCES);
    })()
  );
});

// Delete old static ressources based on current cache name
self.addEventListener("activate", (event) => {
  event.waitUntil(
    (async () => {
      const names = await caches.keys();
      await Promise.all(
        names.map((name) => {
          if (name !== CACHE_NAME) {
            return caches.delete(name);
          }
        })
      );
      await clients.claim();
    })()
  );
});

self.addEventListener("fetch", (event) => {
  // Lorsqu'on cherche une page HTML
  if (event.request.mode === "navigate") {
    // On renvoie à la page index.html
    event.respondWith(caches.match("/"));
    return;
  }

  // Pour tous les autres types de requête
  event.respondWith(
    (async () => {
      const cache = await caches.open(CACHE_NAME);
      const cachedResponse = await cache.match(event.request.url);
      if (cachedResponse) {
        // On renvoie la réponse mise en cache si elle est disponible.
        return cachedResponse;
      }
      // On répond avec une réponse HTTP au statut 404.
      return new Response(null, { status: 404 });
    })()
  );
});

// Notifications
self.addEventListener("push", (event) => {
  const options = {
    body: event.data ? event.data.text() : "Notification par défaut",
    icon: "/icons/icon-192x192.png",
  };

  event.waitUntil(
    self.registration.showNotification("Titre de la notification", options)
  );
});
