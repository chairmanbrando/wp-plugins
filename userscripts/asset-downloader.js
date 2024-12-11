// ==UserScript==
// @name        Global: Asset Downloader
// @namespace   Violentmonkey Scripts
// @match       *://*/*
// @grant       none
// @run-at      document-idle
// @version     1.0
// @author      chairmanbrando
// @description Adds a direct link to top-level asset views -- e.g. you're looking directly at an image.
// @note        I don't know if this works on Chrome; I use Firefox for all personal browsing because Google is bad.
// @note        Install this: https://addons.mozilla.org/en-US/firefox/addon/load-reddit-images-directly/
// ==/UserScript==

// Test for being on a single media asset that doesn't care if things are loaded.
if (! document.querySelector('body > :where(img, video, audio):only-child')) return;

// Get the display-formatted file size of the given URL.
function getFileSize(href) {
  let size = performance.getEntriesByName(href)[0].decodedBodySize;

  if (size) {
    const units = ['B', 'K', 'M', 'G'];
    const power = Math.floor(Math.log(size) / Math.log(1024));
    const fixed = (size / Math.pow(1024, power)).toFixed(2);

    return ` ${fixed}${units[power]}`;
  }

  return false;
}

// Styles to be injected.
const styles = `
  a.download {
    background-color: #f0f;
    color: #000;
    font-family: sans-serif;
    font-weight: bold;
    left: 0;
    line-height: 1;
    padding: 0.4em 0.5em 0.5em;
    position: fixed;
    opacity: 0.75;
    text-decoration: none;
    text-transform: uppercase;
    top: 0;
    z-index: 999;
  }

    a.download:not([href]) {
      display: none;
    }
`;

const style = document.createElement('style');

style.textContent = styles;
document.head.appendChild(style);

const href = window.location.href;
const a    = document.createElement('a');

a.textContent = '↓';
a.setAttribute('href', href);
a.setAttribute('download', href.split('/').pop());
a.setAttribute('class', 'download');

document.body.appendChild(a);

// Get the file's size, but give it a bit to make sure it's loaded.
const interval = setInterval(() => {
  const size = getFileSize(href);

  if (size) {
    a.textContent += size;
    clearInterval(interval);
  }
}, 1000);
