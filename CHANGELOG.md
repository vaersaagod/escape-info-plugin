# Escape Info plugin Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

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
