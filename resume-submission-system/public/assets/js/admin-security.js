(function () {
    'use strict';

    const cookieName = 'admin_csrf_token';
    const tokenPattern = /^[a-f0-9]{64}$/;
    const cookiePrefix = cookieName + '=';
    let token = document.cookie.split('; ').reduce(function (found, cookie) {
        return found || (cookie.indexOf(cookiePrefix) === 0
            ? decodeURIComponent(cookie.slice(cookiePrefix.length))
            : '');
    }, '');

    if (!tokenPattern.test(token)) {
        const bytes = new Uint8Array(32);
        crypto.getRandomValues(bytes);
        token = Array.from(bytes, function (byte) {
            return byte.toString(16).padStart(2, '0');
        }).join('');

        document.cookie = cookieName + '=' + token
            + '; Path=/; Max-Age=7200; SameSite=Strict'
            + (location.protocol === 'https:' ? '; Secure' : '');
    }

    document.querySelectorAll('input[name="_admin_csrf"]').forEach(function (input) {
        input.value = token;
    });
}());
