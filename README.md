# OpenPanel WordPress Plugin (Self-Hosted Edition)

A privacy-friendly analytics WordPress plugin that integrates [OpenPanel](https://openpanel.dev) with ad-blocker resistance. 

**Fork Notice:** The official plugin is currently designed to send analytics data to the OpenPanel Cloud. This community fork modifies the plugin to add full support for **Self-Hosted OpenPanel instances**, allowing you to keep 100% control over your data while retaining the powerful local proxy feature.

## Features

- 🏠 **Self-Hosted Support** - Connect directly to your own custom OpenPanel instance URL.
- 🚀 **Ad-Blocker Resistant** - Serves scripts and API requests through a local proxy (`/wp-json/openpanel/`) on your own domain.
- 🔒 **Privacy-Friendly** - Cookie-less tracking, no consent banners needed.
- ⚡ **Performance Optimized** - Local script caching with fallback.
- 📊 **Real-Time Analytics** - Instant insights without delays.

## Quick Start

1. Deploy your own OpenPanel instance or get your Client ID from OpenPanel Cloud.
2. Download the latest release `.zip` from this repository and install the plugin in WordPress.
3. Go to **Settings → OpenPanel**.
4. Configure your **Client ID** and **Client Secret** (required for some self-hosted instances).
5. Enter your **Self-Hosted Instance URL** (e.g., `https://analytics.yourdomain.com`). 
    * *Note: If you only enter the domain, the `/api` path will be added automatically.*
6. Save settings and start tracking!

## 🤝 Maintainers

This fork is maintained by [Mediapixel](https://mediapixel.kr), a web agency operating between France and South Korea. We created this to support the community's need for data sovereignty.


## Documentation

For detailed installation instructions, FAQ, and full documentation, see [`readme.txt`](openpanel/readme.txt).

## License

GPLv2 or later - see [LICENSE](openpanel/LICENSE)
