var VERSION ='20200721.2030';
var CACHE_KEY_PREFIX = 'elasticsearch-admin-';
var CACHE_KEY = CACHE_KEY_PREFIX + VERSION;
var CACHE_FILES = [
    'favicon-red.png',
    'favicon-yellow.png',
    'favicon-green.png',
];

self.addEventListener('install', function(InstallEvent) {
    self.skipWaiting();

    if('waitUntil' in InstallEvent) {
        InstallEvent.waitUntil(
            cacheAddAll()
        );
    }
});

self.addEventListener('activate', function(ExtendableEvent) {
    if('waitUntil' in ExtendableEvent) {
        ExtendableEvent.waitUntil(
            caches.keys()
            .then(function(cacheNames) {
                return Promise.all(
                    cacheNames.map(function(cacheName) {
                        if(cacheName !== CACHE_KEY && -1 !== cacheName.indexOf(CACHE_KEY_PREFIX)) {
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(function() {
                return self.clients.claim();
            })
        );
    }
});

self.addEventListener('fetch', function(FetchEvent) {
    var request = FetchEvent.request;

    if(
        request.url.indexOf('.css') !== -1 ||
        request.url.indexOf('.js') !== -1 ||
        request.url.indexOf('.png') !== -1
    ) {
        FetchEvent.respondWith(
            caches.match(request)
            .then(function(response) {
                if(response) {
                    return response;
                }

                if(request.method == 'GET') {
                    caches.open(CACHE_KEY)
                    .then(function(cache) {
                        return fetch(request)
                        .then(function(response) {
                            cache.put(request, response);
                        });
                    });
                }
                return fetch(request);
            })
        );
    }
});

self.addEventListener('message', function(message) {
    switch(message.data.command) {
        case 'reload':
            messageToClient('reload', true);
            break;
    }
});

function cacheAddAll() {
    caches.delete(CACHE_KEY);
    return caches.open(CACHE_KEY)
    .then(function(cache) {
        return cache.addAll(CACHE_FILES);
    });
}

function messageToClient(type, content) {
    self.clients.matchAll()
    .then(function(clients) {
        clients.map(function(client) {
            client.postMessage({'type': type, 'content': content});
        });
    });
}
