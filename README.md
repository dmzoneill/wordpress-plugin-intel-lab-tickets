# Intel Lab Tickets WordPress Plugin

A WordPress plugin that shows Intel lab support tickets in a widget.

## What it does

- Registers an **Intel Lab Tickets** widget that displays tickets.
- Adds an admin settings page.
- Includes a ticker for scrolling ticket updates.
- Fetches tickets from a Kayako help desk through the bundled Kayako
  REST API client in `ky`.

## Tech

- PHP and WordPress
- Kayako REST API

## Installation

Copy the folder into `wp-content/plugins`, activate **Intel Lab
Tickets** in the WordPress admin, set the connection details on its
settings page, and add the widget to a sidebar.

## License

Licensed under the Apache License 2.0. See [LICENSE](LICENSE).
