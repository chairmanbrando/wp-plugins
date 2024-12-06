// ==UserScript==
// @name        DDG: Google Clicker
// @namespace   Violentmonkey Scripts
// @match       https://duckduckgo.com/*
// @grant       none
// @version     1.7
// @author      chairmanbrando
// @require     https://raw.githubusercontent.com/uzairfarooq/arrive/master/minified/arrive.min.js
// @description Added a clickable link to Google in case you forget your `!g`. Typing a "g" without anything having keyboard focus will also send you there! Finally, you can use the 1-9 keys to go to the respective search results while still on DDG.
// @todo        `event.keyCode` has been deprecated because of reasons.
// @noframes
// ==/UserScript==

const google = 'https://www.google.com/search?q=' + encodeURIComponent(document.querySelector('#search_form_input').value);

// On hitting a key, go to Google with your query or one of the found links.
document.body.addEventListener('keyup', (e) => {
    if (e.target === document.body) {
        if (e.keyCode === 71) { // G key!
            window.location.href = google;
        }
    }

    // The links, too, don't exist on `DOMContentLoaded`. 🙄
    const links = document.body.querySelectorAll('ol li:has(article)');
    const link = links[e.keyCode - 49].querySelector('h2 a[href]');

    if (link && link.href) {
        window.location.href = link.href;
    }
});

// Despite the "duckbar" being empty, I can't seem to watch for the arrival of
// `nav` or `ul` elements therein. Dunno what's up with that; maybe it's
// something weird with React (like shadow DOM shenanigans) or in how DDG seems
// to fire `DOMContentLoaded` twice. 🤷‍♀️
document.querySelector('#react-duckbar').arrive('li', { onceOnly: true }, (e) => {
    const $menu = document.querySelector('#react-duckbar ul:first-of-type');
    const $li   = $menu.querySelector('li:last-child').cloneNode(true);
    const $a    = $li.querySelector('a');

    $a.setAttribute('href', google);
    $a.textContent = 'Google It!';
    $li.append($a);
    $menu.append($li);
});
