# Escape Info plugin Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres
to [Semantic Versioning](http://semver.org/).

## 3.1.0 - 2026-10-09

> [!IMPORTANT]
> Clear the data caches after updating. Banner placeholders cached by an earlier version won't render.

### Security
- Hardened the plugin's front end and control panel output
### Fixed
- Pages no longer wait on the Playground API on every request while it's unreachable
- Requests to the Playground API now time out after 10 seconds

## 3.0.1 - 2025-12-16
### Fixed
- Fixed a rendering bug

## 3.0.0 - 2025-08-10
### Added
- Craft 5 support

## 2.2.0 - 2024-01-24
- Adds error handling for the Adspace Select field type   

## 2.1.0 - 2023-08-10
- Brings back multi-format support and other latest changes from v. 1.x

## 2.0.0 - 2023-08-09
### Added
- Craft 4 support

## 1.5.1 - 2023-05-03

### Improved

- Ad meta data is no longer stored with the Adspace Select field JSON

## 1.5.0 - 2023-05-03

### Added

- Added support for rendering Adspace banners in a wrapper
- Added the `bannerWrapperClassName` config setting

## 1.4.0 - 2023-05-03

### Added

- Added support for multi-ratio adspace banners
- Added "Adspace Containers" setting to Adspace Select field type

## 1.3.2 - 2022-03-14

### Fixed

- Fixes an issue where the Adspace data caches was sent a raw PHP date interval string

## 1.3.1 - 2022-01-26

### Improved

- Don't boot anything adspacey if in sandbox and the user is anonymous

## 1.3.0 - 2022-01-25

### Changed

- Refactored everything so there's no server-side API calls. Safer this way.

## 1.2.0 - 2022-01-24

### Added

- Adds theming via CSS variables
- Adds popups

### Improved

- Improved scaling and positioning for shoutout popup

## 1.1.4 - 2022-01-19

### Fixed

- Fixes Craft 3.5 compatibility issue

## 1.1.3 - 2022-01-19

### Changed

- Don't cache on input

## 1.1.2 - 2022-01-19

### Fixed

- Fixed bug where spinner would appear above the shoutout iframes

## 1.1.1 - 2022-01-19

### Changed

- Disabled Parcel cache, because it's really unreliable

## 1.1.0 - 2022-01-19

### Added

- Adds spinner to shoutouts popup

## 1.0.0 - 2022-01-18

### Added

- Initial release
