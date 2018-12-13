# PHP Library for Image processing

[![Latest Stable Version](https://poser.pugx.org/geo6/php-image-tools/v/stable)](https://packagist.org/packages/geo6/php-image-tools)
[![Total Downloads](https://poser.pugx.org/geo6/php-image-tools/downloads)](https://packagist.org/packages/geo6/php-image-tools)
[![Monthly Downloads](https://poser.pugx.org/geo6/php-image-tools/d/monthly.png)](https://packagist.org/packages/geo6/php-image-tools)
[![Software License](https://img.shields.io/badge/license-GPL--3.0-brightgreen.svg)](LICENSE)

## Install

```shell
composer require geo6/php-image-tools
```

## Functions

This library provides following functions :

### `thumbnail()`

Generates thumbnail by defining maximum size for the long side of your image.

### `EXIFRotate()`

Rotates your image based on EXIF `Orientation` parameter (see <https://www.impulseadventure.com/photo/exif-orientation.html>).

### `save()`

Save your (new) image on the disk.

### `display()`

Send your image to the browser with correct headers to display it.
