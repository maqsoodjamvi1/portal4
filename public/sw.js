/* Simple service worker: cache app shell + static assets */
const CACHE = 'school-app-v1';
const APP_SHELL = [
  '/', '/manifest.json',
  // add your CSS/JS entry points:
  '/css/app.css', '/js/app.js'
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE).then(c => c.addAll(APP_SHELL)));
  self.skipWaiting();
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

/* Network-first for HTML, cache-first for others */
self.addEventListener('fetch', e => {
  const req = e.request;
  const isHTML = req.headers.get('accept')?.includes('text/html');
  if (isHTML) {
    e.respondWith(fetch(req).catch(() => caches.match('/')));
    return;
  }
  e.respondWith(
    caches.match(req).then(cached => cached || fetch(req).then(res => {
      const resClone = res.clone();
      caches.open(CACHE).then(c => c.put(req, resClone));
      return res;
    }))
  );
});
