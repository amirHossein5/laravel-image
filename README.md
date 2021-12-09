Create image with multiple sizes based on [intervention](http://image.intervention.io/) easily.

*for Example:*

```php
  Image::make($request->image)
    ->setExclusiveDirectory('post')
    ->save();
```

It will save your image in three default size in path:

```public/``` 
- ```images/post/2021/12/2/1638611107/1638611107_960_large.png```
- ```images/post/2021/12/2/1638611107/1638611107_960_medium.png```
- ```images/post/2021/12/2/1638611107/1638611107_960_small.png```

But all if these are changeable.

- **[Installation](#installation)**

- **["make" method](#make-method)**
  - **[Directory customazations](#directory-customazations)**
  
- **["raw" method](#raw-method)**
    
- **[Size customazations](#size-customazations)**
  - **[Default size](#default-size)**
  
- **[result array](#result-array)** 

- **[Upsize or not](#upsize-or-not)**

- **[Remove image(s)](#remove-images)**
  
- **[Examples](#examples)**

## Prerequisites

- Laravel 8
- PHP >= 7.4

## Installation

```bash
composer require amir-hossein5/laravel-image
```

and for publishing configuration file: 

```bash
php artisan vendor:publish --tag image
```

## "make" method

When you are using "make" method like first example above, defaults for directories, and sizes('use_size' passed array in config file) are setted.
 Except "->setExclusiveDirectory('post')" which you have to pass, and this will save image in path of first Example above. 
And creates images automatically with sizes(because "make" method sets defaults for directories and sizes), which defined in config file.

But how to customize directories, and sizes.

### Directory customazations

in the parantheses written name of each directory:

```public/``` 
- ```images(rootDirectory)```
- ```/post(exclusiveDirectory)```
- ```/2021/12/2/(archiveDirectories)```
- ```/1638611107(sizesDirectory)``` -> **if there be more than one size**

All image Path setters:

| setter                          | default                                              |
|---------------------------------|------------------------------------------------------|
| setRootDirectory( string )      | images (written in config file)                      |
| setExclusiveDirectory( string ) |                                                      |
| setArchiveDirectories( string ) | year/month/day                                       |
| setSizesDirectory( string )     | time()                                               |
| setImageName( string )          | time()_rand(100, 999)_sizeName(if there be any size) |
| setImageFormat( string )        | uploaded image format                                |

> Notice: root directory is also changeable in config file.

Example: 

```php 
Image::make($image)
  ->setExclusiveDirectory('book')
  ->setRootDirectory('image')
  .
  .
  .
  ->save()
```

For size customazations see [Size customazations](#size-customazations).


## "raw" method

When you are using "raw" method like:

```php
Image::raw($image)
  .
  .
  ->save()
```

Nothing will be automatically set(directories, and sizes). For setting directory of image there is two method:

```php
Image::raw($image)
  ->inPath('book')
  ->save()
  
// will be save in public/book/

Image::raw($image)
  ->inPublicPath()
  ->save()
  
// will be save in public/
```

For size customazations see [Size customazations](#size-customazations).

## Size customazations

You can modify or add your own array of sizes in config file and write key of that in 'use_size' of configuration. Then whenever you use "make" method (or add with ```->resizeBy()```) it will automatically create images with specified sizes.

Or if you want to customize that manually:

| setter                                    | description                                                                         |
|-------------------------------------------|-------------------------------------------------------------------------------------|
| autoResize()                              | removes previous defined sizes                                                      |
| resize( $width, $height, $as = null )     | removes previous defined sizes, and adds a size                                     |
| alsoResize( $width, $height, $as = null ) | adds a size                                                                         |
| resizeBy( array )                         | resize by intended array(the structure shuold be like 'imageSizes' in configuration)|

You may add resizeBy's array from configuration like ```->resizeBy(config('image.postSizes'))```.


### Default size

You can specify default size of the defined sizes too. From configuration, or:

```php
Image::
  ...
  ->defaultSize('key of one of your sizes')
  ...
  ->save()
```

Which affects on output array.

When you want to update default_size:

```php
Image::setDefaultSizeFor($post->image, 'small');
```

Will return previous array but default_size has changed.


## result-array

After creating image returns array, which

```index``` key is array,or string(contains one, or more) image paths, which depends on number of sizes(if there be more than one size it's array).

For example:

```php
[
  'index' => [
      "images/post/2021/12/08/1638966454/1638966454_491_large.png",
      .
      .
      "images/post/2021/12/08/1638966454/1638966454_491_small.png",
   ]
   // or 
   'index' => 'image path'
   
   'imageDirectory' => 'image directory'
   
   'default_size' => 'medium' (if you are using "default_size", and if you have more than one size)
]
```

## Upsize or not

If you want or don't want to use upsize of intervention you should:

```php
Image::
  .
  .
  ->save(true)
```

## Remove image(s)

For deleting pass created image array:

```php
Image::rm($post->image);
```



## Examples

```php
$request['image'] = Image::make($request['image'])
  ->setExclusiveDirectory('post')
  ->save();

if (!$request['image']) {
  return back()
    ->withInput()
    ->withErrors(['image' => __('validation.uploaded')]);
}

Post::create($request);
```

```php
if(!Image::rm($post->image)){
  return back()->with('fail message');
}

$post->delete;
```

```php
$image = Image::setDefaultSizeFor($post->image,$request['default_size']);

if (!$request['image']) {
  return back()
    ->withInput()
    ->withErrors(['image' => __('validation.uploaded')]);
}

$post->image = $image;
$post->save();
```


<br/>

## License

[License](LICENSE)

