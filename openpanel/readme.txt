=== OpenPanel (Self-Hosted Edition) ===
Contributors: mediapixel
Tags: analytics, web analytics, product analytics, privacy-friendly, tracking, proxy, ad-blocker resistant, self-hosted
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

OpenPanel WordPress plugin - Now with full support for Self-Hosted instances. Privacy-friendly analytics with ad-blocker resistance.

== Description ==

**OpenPanel (Self-Hosted Edition)** is a community fork maintained by [Mediapixel](https://mediapixel.kr). While the official plugin focuses on OpenPanel Cloud, this version adds the ability to connect to your own self-hosted OpenPanel instance, ensuring 100% data sovereignty.

**OpenPanel** is an open-source web and product analytics platform that serves as a privacy-friendly alternative to traditional analytics solutions. This WordPress plugin seamlessly integrates OpenPanel with your WordPress site while maximizing reliability and avoiding ad-blocker interference.

= Key Features =

* **🏠 Self-Hosted Support**: Connect directly to your own custom OpenPanel instance URL.
* **🚀 Ad-Blocker Resistant**: Serves analytics scripts and API calls from your own domain.
* **📊 Real-Time Analytics**: Get instant insights without processing delays.
* **🔒 Privacy-Friendly**: Cookie-less tracking that respects user privacy (no cookie banners needed!).
* **⚡ Performance Optimized**: Caches scripts locally and uses efficient proxying.
* **🛠️ Dev Friendly**: Option to disable SSL verification for local/homelab testing.

= How It Works =

This plugin integrates OpenPanel with WordPress in a blocker-resistant way:

* **Inlines** `op1.js` directly into your pages, fetched from your own instance.
* **Bootstraps** the OpenPanel SDK with your Client ID automatically.
* **Proxies** all SDK requests through WordPress REST API (`/wp-json/openpanel/`).
* **Preserves** all request methods, headers, and body data.

**Why use a proxy?** Serving scripts and data from your own domain origin avoids third-party blocking and improves tracking reliability significantly.

= Privacy Benefits =

* **🍪 No Cookie Banners Required**: OpenPanel uses cookie-less tracking.
* **🛡️ GDPR Friendly**: Compliant with privacy regulations without requiring user consent.
* **🔐 Data Ownership**: With self-hosting support, you maintain 100% control over your data.

== Installation ==

= Getting Started =

1. **Prepare your OpenPanel Instance**:
   * Use OpenPanel Cloud or deploy your own self-hosted instance.
   * Copy your Project Client ID (starts with `op_client_`).

2. **Install the Plugin**:
   * Upload the plugin ZIP file via **Plugins → Add New → Upload Plugin**.
   * Activate the plugin via **Plugins → Installed Plugins**.

3. **Configure Settings**:
   * Go to **Settings → OpenPanel** in your WordPress admin.
   * Enter your **Client ID**.
   * Enter your **Instance URL** (e.g., `https://analytics.yourdomain.com`). Leave empty for Cloud.
   * (Optional) For local testing, you can **Disable SSL Verification**.

4. **Verify Installation**:
   * Visit your website frontend and check your OpenPanel dashboard for incoming data.

== Frequently Asked Questions ==

= Can I use this with OpenPanel Cloud? =
Yes! If you leave the "Instance URL" field empty, it will default to the official OpenPanel Cloud API and CDN.

= What is the "Disable SSL Verification" option for? =
This is strictly for development. It allows the plugin to communicate with self-hosted instances using self-signed certificates or local IP addresses (homelabs) without failing. **Do not use in production.**

= Who maintains this fork? =
This fork is maintained by [Mediapixel](https://mediapixel.kr), a web agency specializing in privacy-first digital solutions.

== Changelog ==

= 1.0.1 =
* **Feature**: Added support for Self-Hosted OpenPanel instances.
* **Feature**: Added "Instance URL" setting.
* **Feature**: Added "Disable SSL Verification" for local development/homelabs.
* **Fix**: Improved script fetching to use the custom instance URL for `op1.js`.

= 1.0.0 =
* **Initial Release** - Complete OpenPanel WordPress integration.


== Upgrade Notice ==

= 1.0.0 =
Initial release of the OpenPanel WordPress plugin. Provides ad-blocker resistant analytics with local script caching and API proxying.
