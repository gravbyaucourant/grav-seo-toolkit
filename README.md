# SeoToolkit Plugin for Grav CMS

The **SeoToolkit** plugin is a powerful, all-in-one SEO solution for [Grav CMS](https://getgrav.org). It enhances your website’s search engine optimization by providing tools to manage meta tags, generate sitemaps, configure robots.txt, add Open Graph tags for social media, and implement JSON-LD structured data. With a user-friendly admin interface, real-time SEO validation, and automated features, SeoToolkit makes it easy for both beginners and advanced users to optimize their Grav sites for search engines and social sharing.

## Features

- **SEO Meta Tags Management**:
  - Add custom SEO title, meta description, and focus keyword for each page.
  - Real-time validation ensures the focus keyword appears in the title and description.
  - Live Google-style snippet preview in the admin panel shows how your page will appear in search results.
  - Injects meta tags (`title`, `description`, `keywords`, `canonical`, `robots`) into the page `<head>`.

- **Open Graph (OG) Support**:
  - Configure Open Graph tags (`og:title`, `og:description`, `og:image`, `og:type`, `og:url`) for social media sharing.
  - Supports page-specific OG settings with fallbacks to global defaults.
  - Handles image uploads for OG images with proper URL resolution.

- **Sitemap Generation**:
  - Automatically generates a dynamic `sitemap.xml` with customizable options (e.g., include hidden pages, set change frequency and priority).
  - Includes an XSL stylesheet for a human-readable sitemap view in browsers.
  - Pings Google automatically when the sitemap is updated.
  - Ensures the sitemap URL is included in `robots.txt`.

- **Robots.txt Generation**:
  - Auto-generates a `robots.txt` file with customizable rules (e.g., disallowing `/cache/`, `/logs/`).
  - Automatically adds the sitemap URL to `robots.txt`.

- **JSON-LD Schema Support**:
  - Generates site-wide structured data (Organization and WebSite schemas).
  - Allows page-specific JSON-LD overrides via a textarea in the admin panel.
  - Enable or disable schema generation via configuration.

- **User-Friendly Admin Interface**:
  - Adds an “SEO Toolkit” menu item in the Grav Admin Plugin for global settings.
  - Organizes settings into tabs: SEO, Schema, Social, Advanced, and Sitemap.
  - Includes custom CSS and JavaScript for validation and live snippet previews.

## Advantages

- **Comprehensive SEO Solution**: Covers meta tags, sitemaps, robots.txt, Open Graph, and schema in one plugin.
- **Ease of Use**: Intuitive admin interface with live previews and validation for non-technical users.
- **Automation**: Saves time with auto-generated `robots.txt` and `sitemap.xml`.
- **Customizability**: Extensive configuration options for advanced users.
- **Modern SEO Standards**: Supports JSON-LD and Open Graph for better search engine and social media compatibility.

## Installation

The SeoToolkit plugin can be installed in three ways: using the Grav Package Manager (GPM), manually via a ZIP file, or through the Grav Admin Plugin.
