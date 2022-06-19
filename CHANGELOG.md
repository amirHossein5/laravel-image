# Release Notes

## [v2.2.0](https://github.com/amirHossein5/laravel-image/compare/v2.1.1...v2.2.0)
### Added:
- Transactions [7af08faa](https://github.com/amirHossein5/laravel-image/commit/7af08faa6121f247fe585cb2436472826a0937cb)
- Determining image quality [6cbe253eb](https://github.com/amirHossein5/laravel-image/commit/6cbe253eb0e629103b9ea36b3f851d65842f0dc2)
- \Intervention\Image\Image is acceptable for input of ```raw``` and ```make``` method [6304c3cae](https://github.com/amirHossein5/laravel-image/commit/6304c3caeacc23340f3c93eb82becaa064361510)

### Changed:
- ```in``` method is optional
- ```be``` method improved


## [v2.1.1](https://github.com/amirHossein5/laravel-image/compare/v2.1.0...v2.1.1)

### Fixed
Fix throwing exception when removing a false path (unlocated) image(s) ([2afca87bde](https://github.com/amirHossein5/laravel-image/commit/2afca87bde1882b7ff3f4342f2a58bcd975655c5))


## [v2.1.0](https://github.com/amirHossein5/laravel-image/compare/v2.0.2...v2.1.0)

laravel 9 support


## [v2.0.2](https://github.com/amirHossein5/laravel-image/compare/v2.0.1...v2.0.2)

### Fixed
- On removing image, only if directory is empty, it'll be remove [97d736a](https://github.com/amirHossein5/laravel-image/commit/97d736a2e3f7354ef6ac0bcbd7d1a5622daa457b)


## [v2.0.1](https://github.com/amirHossein5/laravel-image/compare/v2.0.0...v2.0.1)

Set public disk for ```rm``` when not setted.


## [v2.0.0](https://github.com/amirHossein5/laravel-image/compare/v1.2.0...v2.0.0)

### Added
- Disks (saving in storage).

### Changed
 - ```inPath``` to ```in```
 - Result array.
 - Getting result array manually properties.

### Removed
- ```inPublicPath```