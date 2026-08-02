# Security Policy

This is the repository for the website that is hosted at `https://phpunit.de/`. It is served entirely as static files: there is no server-side application, no database, and no user input is processed.

The maintained pages use **no JavaScript**. They consist of static HTML, one stylesheet, two web fonts, and images. They are served with `Content-Security-Policy: default-src 'none'; script-src 'none'; style-src 'self'; img-src 'self' data:; font-src 'self'; form-action 'none'; base-uri 'none'; frame-ancestors 'none'`, so no script executes even if one were injected into the markup. The `<script type="application/ld+json">` elements these pages contain hold [structured data](https://schema.org/); browsers parse them as data and never execute them.

The archived manuals below `/manual/` (covering PHPUnit 2.3 through 6.5) are kept online unchanged for historical reference. They still load jQuery, Bootstrap 3, and highlight.js, and are therefore served with a separate, less restrictive policy that permits `script-src 'self'`. They are served with `X-Robots-Tag: noindex`.

If you believe you have found a security vulnerability in this website, please report it to us through coordinated disclosure.

**Please do not report security vulnerabilities through public GitHub issues, discussions, or pull requests.**

Instead, please email `sebastian@phpunit.de`.

Please include as much of the information listed below as you can to help us better understand and resolve the issue:

* The type of issue
* Full paths of source file(s) related to the manifestation of the issue
* The location of the affected source code (tag/branch/commit or direct URL)
* Any special configuration required to reproduce the issue
* Step-by-step instructions to reproduce the issue
* Proof-of-concept or exploit code (if possible)
* Impact of the issue, including how an attacker might exploit the issue

This information will help us triage your report more quickly.
