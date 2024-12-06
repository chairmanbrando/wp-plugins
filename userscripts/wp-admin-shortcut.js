// ==UserScript==
// @name        Global: WP Admin Shortcut
// @namespace   Violentmonkey Scripts
// @match       *://*/*
// @grant       none
// @version     1.2
// @author      chairmanbrando
// @description Checks every page you're on for the obvious presence of WP. If it's there, hitting Cmd-Escape will take you to `/wp-admin`.
// ==/UserScript==

const checkForWP = function () {
    let sheets = document.getElementsByTagName('style');

    for (let i = 0; i < sheets.length; i++) {
        if (sheets[i].id.indexOf('wp-') > -1) {
            return true;
        }
    }

    sheets = document.getElementsByTagName('link');

    for (i = 0; i < sheets.length; i++) {
        if (sheets[i].href.indexOf('wp-') > -1) {
            return true;
        }
    }

    return false;
};

if (checkForWP()) {
    document.addEventListener('keydown', function (event) {
        if (document.querySelectorAll(':focus').length) return;

        if (event.metaKey && event.key === 'Escape') {
            window.location.href = `${window.location.protocol}//${window.location.hostname}/wp-admin`;
        }
    });
}
