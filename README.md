Create image with multiple sizes based on [intervention](http://image.intervention.io/) easily.

*for Example:*

```php
  Image::make($request->image)
    ->setExclusiveDirectory('post')
    ->save();
```

It will save your image in three sizes, which were defined in config file, in path:

- ```public/``` 
  - ```images/post/2021/12/2/1638611107/```
    - ```1638611107_960_large.png```
    - ```1638611107_960_medium.png```
    - ```1638611107_960_small.png```



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

And finally you may specify your sizes in configuration:

```
'use_size' => 'imageSizes',

 'imageSizes' => [
    'large' => [
        'width' => '800',
        'height' => '600'
    ],
    'medium' => [
        'width' => '400',
        'height' => '300'
    ],
    'small' => [
        'width' => '80',
        'height' => '60'
    ]
],
``` 




<!-- - **["make" method](#make-method)**
  - **[Directory customazations](#directory-customazations)**
  
- **["raw" method](#raw-method)**
    
- **[Size customazations](#size-customazations)**
  - **[Default size](#default-size)**
  
- **[result array](#result-array)** 

- **[Upsize or not](#upsize-or-not)**

- **[Remove image(s)](#remove-images)**
  
- **[Examples](#examples)**
 -->
 
## "make" method

In ```make``` method defaults for directories and sizes will be set. Beacause of this, when you use:

```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::make($request->image)
  ->setExclusiveDirectory('post')
  ->save();
```

saves in:

- ```public/``` 
  - ```images/post/2021/12/2/1638611107/```
    - ```1638611107_960_large.png```
    - ```1638611107_960_medium.png```
    - ```1638611107_960_small.png```


It will create your image in some default path, and with those sizes, that you defined in config (in ```use_size``` part). 
But how to customize directories, and sizes.

For size customazations see [Size customazations](#size-customazations).

### Directory customazations

In parantheses written name of each directory:


- ```images(rootDirectory)```
- ```/post(exclusiveDirectory)```
- ```/2021/12/2/(archiveDirectories)```
- ```/1638611107(sizesDirectory)``` -> **if there be more than one size**

Image path setters:

| setter                          | default                                              |
|---------------------------------|------------------------------------------------------|
| setImageName( string )          | time()_rand(100, 999)_sizeName(if there be any size) |
| setImageFormat( string )        | uploaded image format                                |
| be( string )                    | sets both image name and format. e.g, ```->be('name.png)```| 

Just available for ```make```:

| setter                          | default                                              |
|---------------------------------|------------------------------------------------------|
| setRootDirectory( string )      | images (written in config file)                      |
| setExclusiveDirectory( string ) |                                                      |
| setArchiveDirectories( string ) | year/month/day                                       |
| setSizesDirectory( string )     | time()                                               |

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

When you are using "raw" method,

nothing will be automatically set(directories, and sizes). For setting directory of image there is two method:

```php
Image::raw($image)
  ->in('book')
  ->save()
  
// without resizing
// will be save in public/book/ 

Image::raw($image)
  ->in('')
  ->save()
  
// without resizing
// will be save in public/
```

For add size, and size customazations see [Size customazations](#size-customazations).

## Save in storage

For saving into storage folder you can use disks, which defined in config:

```php
'disks' => [
    'public' => public_path(),
    'storage' => storage_path('app')
]
```

By default it's ```public```.

```php
$images = Image::make($image)
  ->setExclusiveDirectory('post')
  ->disk('storage')
  ->save();
```

Will be:

- ```storage/app/``` 
  - ```images/post/2021/12/2/1638611107/```
    - ```1638611107_960_large.png```
    - ```1638611107_960_medium.png```
    - ```1638611107_960_small.png```

You may add more disks and use from that.



## Size customazations

You can add your own array of sizes in config file (in ```use_size``` part). 
Then whenever you use "make" method (or adding sizes array with ```->resizeBy()```) it will automatically create images with specified sizes.

Size setters:

| setter                                    | description                                                                         |
|-------------------------------------------|-------------------------------------------------------------------------------------|
| autoResize()                              | removes previous defined sizes                                                      |
| resize( $width, $height, $as = null )     | removes previous defined sizes, and adds a size                                     |
| alsoResize( $width, $height, $as = null ) | adds a size                                                                         |
| resizeBy( array )                         | resize by intended array(the structure shuold be like 'imageSizes' in configuration)|

You may define multiple array of size in config file, and ```->resizeBy(config('image.postSizes'))```.


### Default size

If you want to use default_size functionality, you may define it in config file.

Or create, update default_size:

```php
Image::setDefaultSizeFor($post->image, 'small');
```

Will return previous array but default_size has changed or added.

If you created your result array **manually** pass the key of array, which there is image path(s):

```php
$image = Image::make($this->image)
  ->setExclusiveDirectory('post')
  ->save(false, function ($image) {
    return [
      'paths' => $image->imagePath,
    ];
  });
```

```php
Image::setDefaultSizeFor($post->image, 'small', 'paths');
```




## Result array

After creating image it returns array, which

```index``` key is array,or string(contains one, or more) image paths, which depends on number of sizes.

For example:

```php
[
  "index" => [
      "images/post/2021/12/08/1638966454/1638966454_491_large.png",
      "images/post/2021/12/08/1638966454/1638966454_491_meduim.png",
      "images/post/2021/12/08/1638966454/1638966454_491_small.png",
   ]
   // or 
   "index" => "image path"
   
   "imageDirectory" =>  "images/post/2021/12/08/1638966454"
   "default_size" => 'medium' (if you are using "default_size", and you have more than one size)
   "disk" => 'public'
]
```

### Getting result array manually

```php

Image::make($this->image)
  ->setExclusiveDirectory('post')
  ->save(false, function ($image) {
    return [
      'index' => $image->imagePath,
      'imageDirectory' => $image->imageDirectory,
      'disk' => $image->disk,
    ];
  })
  
  // or 
  
  ->save(closure: fn ($image) => [
    'index' => $image->imagePath,
    'imageDirectory' => $image->imageDirectory,
    'disk' => $image->disk,
  ]);
  
  
  // output
  
[
  "index" => [
      "images/post/2021/12/08/1638966454/1638966454_491_large.png",
      "images/post/2021/12/08/1638966454/1638966454_491_meduim.png",
      "images/post/2021/12/08/1638966454/1638966454_491_small.png",
   ]
   // or 
   "index" => "images/post/2021/12/08/1638966454/"
   
   "imageDirectory" => "images/post/2021/12/08/1638966454"
    "disk" => 'public'
]

```

Properties:

| Property | Description |
|----------------------------|-----------------------------------------------------------|
| $image->image              |    Uploaded image object.                                 |
| $image->sizes              | All used sizes.                                           |
| $image->default_size       | Default size.                                             |
| $image->imagePath          | Full path of stored image(s).                             |
| $image->imageDirectory     | Image's directory.                                        |
| $image->imageName          | Image name.                                               |
| $image->imageFormat        | Image format.                                             |
| $image->disk               | [Disk](#save-in-storage)                                  |
| $image->rootDirectory      | [see Directory customazations](#directory-customazations) |
| $image->exclusiveDirectory | [see Directory customazations](#directory-customazations) |
| $image->archiveDirectories | [see Directory customazations](#directory-customazations) |
| $image->sizesDirectory     | [see Directory customazations](#directory-customazations) |


## Upsize or not

If you want to use upsize of intervention you should:

```php
  ->save(true)
```

## Removeing image(s)

Pass created image:

```php
Image::rm($post->image);
```
which returns ```true``` or ```false```.

</br>

If you created your result array **manually** pass the key of array, which there is image path(s):

```php

$image = Image::make($this->image)
  ->setExclusiveDirectory('post')
  ->save(false, function ($image) {
    return ['paths' => $image->imagePath];
  });
  
Image::rm($image, 'paths');

```

or if it's one string path just pass it:

```php
Image::raw($this->image)
  ->in('post/test')
  ->save(false, function ($image) {
    return $image->imagePath;
  });
  
Image::rm($image);
```

to check that is removed, or not:

```php

if (! Image::rm($image)) {
  // ...
}

// or 

if (! Image::wasRecentlyRemoved()) {
  // ...
}

```

#### Remove when used disk

If you created image with some disks do:

```php
....
  ->disk('storage')
....
  
Image::disk('storage)->rm($image);
```




## Replaceing image(s)

```replace``` method works same as ```save``` method, but if there be image(s) with same name as this image, this will be replace.

```php
$image = Image::raw($this->image)
    ->in('')
    ->be('logo.png')
    ->replace();
 
// like save method

if (! $image) {
  // ...
}    

```

It works for multipe images too.


## Testing

If you don't want to image be ceate use fake before your code:

```php
Image::fake();

Image:: ...
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
$image = Image::setDefaultSizeFor($post->image, $request['default_size']);

if (!$image) {
  return back()
    ->withInput()
    ->withErrors(['image' => __('validation.uploaded')]);
}

$post->image = $image;
$post->save();
```


```php
$request['icon'] = Image::raw($request['icon'])
    ->be('icon.png')
    ->in('')
    ->replace(false, function ($image) {
        return $image->imagePath;
    });
    
if (!$request['icon']) {
  return back()
    ->withInput()
    ->withErrors(['image' => __('validation.uploaded')]);
}
```


<br/>

## License

[License](LICENSE)

