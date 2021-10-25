# Egnyte Filesystem

[![Latest Version](https://img.shields.io/github/release/thephpleague/skeleton.svg?style=flat-square)](https://github.com/thepartnership-suman/egnyte)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/league/skeleton.svg?style=flat-square)](https://packagist.org/packages/sumanpoudel/egnyte)


I was working on Egnyte project for Laravel and didn't find any decent solution
so decided to write my own. Inspired by Yespbs\Egnyte.

## Install

Via Composer

``` bash
$ composer require sumanpoudel/egnyte
```

## Usage

``` php
$client = new \Suman\Egnyte\Model\File(EgnyteDomain, OAuthToken);

$adapter = new \Suman\Egnyte\EgnyteAdapter($client);

$filesystem = new Filesystem($adapter);

//to get the content of the file
$fileContents = $filesystem->get('/Shared/file.jpg');
//to check file exists
$fileContents = $filesystem->has('file.jpg');
//upload the file
$fileContents = $filesystem->put(fileLocation, contents_of_file);
//move the file 
$fileContents = $filesystem->move(originalLocation, newLocation);
//delete the file/folder
$fileContents = $filesystem->delete(location_of_file_or_folder);

// see the usrl below for more functions 
https://flysystem.thephpleague.com/v2/docs/usage/filesystem-api/

```

## Testing

``` bash
$ phpunit
```

## Credits

- [:author_name](https://github.com/thepartnership-suman/egnyte:author_username)
- [All Contributors](https://github.com/thepartnership-suman/:egnyte/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
