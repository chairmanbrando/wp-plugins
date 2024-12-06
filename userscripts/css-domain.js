// ==UserScript==
// @name        Global: CSS Domain/URL Support
// @namespace   Violentmonkey Scripts
// @match       *://*/*
// @grant       none
// @version     1.3
// @author      chairmanbrando
// @run-at      document-start
// @description Runs as soon as possible for a web extension to add the page's domain as a class on the `<html>` element.
// ==/UserScript==

let hn = window.location.hostname.split('.');
let sr = window.location.pathname.toLowerCase().split('/');

while (hn.length > 2) {
    hn.shift();
}

hn = hn.join('-');
sr = `${sr[1]}-slash-${sr[2]}`;

function addClassToDamnedRootElement() {
    document.documentElement.classList.add(hn);

    // Special consideration for reddit because I'm there constantly.
    if (window.location.hostname.indexOf('reddit.com') > -1) {
        if (sr.length > 1) {
            document.documentElement.classList.add(sr);
        }
    }
}

// Add our handy class as soon as possible...
addClassToDamnedRootElement();

//...but also watch for changes and reapply if necessary. These JS frameworks are out of control!
const observer = new MutationObserver((mutationsList, observer) => {
    for (const mutation of mutationsList) {
        if (mutation.attributeName === 'class') {
            if (!document.documentElement.classList.contains(hn)) {
                addClassToDamnedRootElement();
            }
        }
    }
});

observer.observe(document.documentElement, {
    attributes: true,
    childList: false,
    subtree: false
});
