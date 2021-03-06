Create image with multiple sizes based on [intervention](http://image.intervention.io/) easily.

*for Example:*

```php
use AmirHossein5\LaravelImage\Facades\Image;

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

- Laravel ```^8.0|^9.0```
- PHP ```^7.4|^8.0```

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


 
## "make" method

In ```make``` method defaults for directories(archive path(2021/12/2)) and sizes will be set. Beacause of this, when you use:

```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::make($request->image) // or pass **Intervention object**
  ->setExclusiveDirectory('post')
  ->save();
```

saves in:

- ```public/``` 
  - ```images/post/2021/12/2/1638611107/```
    - ```1638611107_960_large.png```
    - ```1638611107_960_medium.png```
    - ```1638611107_960_small.png```


It will create your image in archive path(2021/12/2), and with those sizes, that you defined in config (in ```use_size``` part). 
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
| setImageFormat( string )        | uploaded image format.  e.g, ```->setImageFormat('png')``` |
| be( string )                    | sets both image name and format. e.g, ```->be('name.png')```| 
| setSizesDirectory( string )     | time()                                               |

Just available for ```make```:

| setter                          | default                                              |
|---------------------------------|------------------------------------------------------|
| setRootDirectory( string )      | images (written in config file)                      |
| setExclusiveDirectory( string ) |                                                      |
| setArchiveDirectories( string ) | year/month/day                                       |


> Notice: root directory is also changeable in config file.

Example: 

```php 
use AmirHossein5\LaravelImage\Facades\Image;

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

nothing will be automatically set(archive path(2021/12/2), and sizes). For setting directory of image:

```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::raw($image) // or pass **Intervention object**
  ->in('book')
  ->save()
  
// without resizing
// will be save in public/book/ 

Image::raw($image)->save()
  
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
use AmirHossein5\LaravelImage\Facades\Image;

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

Size setters:

| setter                                    | description                                                                         |
|-------------------------------------------|-------------------------------------------------------------------------------------|
| autoResize()                              | removes previous defined sizes                                                      |
| resize( $width, $height, $as = null )     | removes previous defined sizes, and adds a size                                     |
| alsoResize( $width, $height, $as = null ) | adds a size                                                                         |
| resizeBy( array )                         | resize by intended array(the structure shuold be like 'imageSizes' in configuration)|

> For automatically resizing use ```make``` method and define sizes in config file (in ```use_size``` part).


### Default size

If you want to use default_size functionality, you may define it in config file, or after image was created:

```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::setDefaultSizeFor($post->image, 'small');
```

Will return previous array but default_size has changed or added.

<!-- 
If you created your result array **manually** pass the key of array, which there is image path(s):

```php
use AmirHossein5\LaravelImage\Facades\Image;

$image = Image::make($this->image)
  ->setExclusiveDirectory('post')
  ->save(false, function ($image) {
    return [
      'paths' => $image->imagePath,
    ];
  });
```

```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::setDefaultSizeFor($post->image, 'small', 'paths');
```
 -->



## Result array

After creating image if operation was successful, it returns array, which

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
   "default_size" => 'medium' 
   "disk" => 'public'
]
```
> ```default_size``` key when you are using "default_size", and there are **more than one size**, will be.



### Getting result array manually

```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::make($this->image)
  ->setExclusiveDirectory('post')
  ->save(false, function ($image) {
    return [
      'index' => $image->imagePath,
      'imageDirectory' => $image->imageDirectory,
      'disk' => $image->disk,
    ];
  });
```

output:

``` 
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
| $image->defaultSize        | Default size.                                             |
| $image->imagePath          | Full path of stored image(s).                             |
| $image->imageDirectory     | Image's directory.                                        |
| $image->imageName          | Image name.                                               |
| $image->imageFormat        | Image format.                                             |
| $image->disk               | [Disk](#save-in-storage)                                  |
| $image->quality               | [Image Quality](#image-quality)                                  |
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
use AmirHossein5\LaravelImage\Facades\Image;

Image::rm($post->image);
```
which returns ```true``` or ```false```.

</br>

If you created your result array **manually** pass the key of array, which there is image path(s):

```php
use AmirHossein5\LaravelImage\Facades\Image;

$image = Image::make($this->image)
  ->setExclusiveDirectory('post')
  ->save(false, function ($image) {
    return ['paths' => $image->imagePath];
  });
  
Image::rm($image, 'paths');

```

Or if it's one string path just pass it:

```php
use AmirHossein5\LaravelImage\Facades\Image;

$image = Image::raw($this->image)
  ->in('post/test')
  ->save(false, function ($image) {
    return $image->imagePath;
  });
  
Image::rm($image);
```

to check that is removed, or not:

```php
use AmirHossein5\LaravelImage\Facades\Image;

if (! Image::rm($image)) {
  // ...
}
```

```php
use AmirHossein5\LaravelImage\Facades\Image;

if (! Image::wasRecentlyRemoved()) {
  // ...
}
```

#### Remove when used disk

If you created image with some disks do:

```php
use AmirHossein5\LaravelImage\Facades\Image;

....
  ->disk('storage')
....
  
Image::disk('storage')->rm($image);
```



## Replacing image(s)

```replace``` method works same as ```save``` method, but if there be image(s) with same name as this image, this will be replace.

```php
use AmirHossein5\LaravelImage\Facades\Image;

$image = Image::raw($this->image)
    ->be('logo.png')
    ->replace(); 
```

It works for multipe images too.


## Image Quality

It is normalized for all file types to a range from 0 (poor quality, small file) to 100 (best quality, big file). Quality is only applied if you're encoding JPG format since PNG compression is lossless and does not affect image quality. The default value is 90.

```php
  ->quality(90)
```

## Transactions

If an exception is thrown within the transaction closure, the transaction will automatically be rolled back and the exception is re-thrown. If the closure executes successfully, the transaction will automatically be committed.

```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::transaction(function () {
  Image::raw($image)->save();
});
```

### Max Attempts
The ```transaction``` method accepts an optional second argument which defines the number of times a transaction should be retried when a deadlock occurs.

```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::transaction(function () {
  Image::raw($image)->save();
}, maxAttempts: 4);
```

### Manually Using Transactions

For begin transaction manually:
```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::beginTransaction();
```
You can rollback the transaction via the ```rollBack``` method:
```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::rollBack();
```
Lastly, you can commit a transaction via the ```commit``` method:
```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::commit();
```


## Testing

If you don't want to image be ceate use fake before your code:

```php
use AmirHossein5\LaravelImage\Facades\Image;

Image::fake();

Image:: ...
```


## Examples

```php
use AmirHossein5\LaravelImage\Facades\Image;

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
use AmirHossein5\LaravelImage\Facades\Image;

if(!Image::rm($post->image)){
  return back()->with('fail message');
}

$post->delete;
```

```php
use AmirHossein5\LaravelImage\Facades\Image;

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
use AmirHossein5\LaravelImage\Facades\Image;

$request['icon'] = Image::raw($request['icon'])
    ->be('icon.png')
    ->replace(false, function ($image) {
        return $image->imagePath;
    });
    
if (!$request['icon']) {
  return back()
    ->withInput()
    ->withErrors(['image' => __('validation.uploaded')]);
}
```

```php
use AmirHossein5\LaravelImage\Facades\Image;

$img = Intervention::make('https://avatars.githubusercontent.com/u/68776630?s=40&v=4');

$avatar = Image::raw($img)
    ->in('avatar')
    ->save();
```

<br/>

## License

[License](LICENSE)

