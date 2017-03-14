# Amari

Some useful things for laravel ^5.* and php ^7.0 with monkey code  ~~insine~~ inside

Be free to use it anywhere under MIT ...


> Package refactor after 0.0.3 version after 14.03.2017 use this version for compatibility



Available traits:

#### Sluggable

Simply covert source field to slug field using Illuminate\Support\Str::slug function.

```php
use Amari\Traits\Sluggable;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use Sluggable;
    
    // Optional, default 'title'
    protected static $slugSource = 'name';
    
    // Optional, default 'slug'
    protected static $slugField = 'route';
    
    public static function boot()
    {
        parent::boot();

        static::saving(function (Model $item) {
            $item->generateSlug(); //Check dirty slug before save
        });
    }
    
    ...
}
```
This code generate route attribute from name if route do not provided, if some entity already exists in database with current slug, slug will append "-$index" from 1 to infinite unless script find free...



#### Jsonable

Another monkey in your lawn. You now have full access to json field as usual attributes of your model. (only 1-2 level of them)

How it works:
```php
use Amari\Traits\Jsonable;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use Jsonable;
    
    protected $fillable = ['title', 'header_content', 'block2', 'block3', 'images', 'meta-title', 'keywords', 'description']
    
    // Required static field with map: real model attribute to json attributes, that will be save in real
    protected static $json = [
        'body' => [
            'header_content',
            'block2',
            'block3',
            'images'
        ],
        'meta' => [
            'meta-title' //it's must be unique (not title, if real title exists) to other or it have more priority agains real fields
            'keywords',
            'description',
        ],
        ...
    ];
    
    ...
}
```

Now you can access to second-level attributes as usually:
```php
$model = new Model([
    'title' => 'Awesome monkey!', 
    'header_content' => 'Wellcome again!', 
    'block3' => 'brilliant brilliant!', 
    'images' => [
        'image1' => '/path/to/image/1.png',
        'image2' => '/path/to/image/2.png',
        'image3' => '/path/to/image/3.png',
    ]
]);

$model->block2 = 'Some test';

dd(model->header_content); //Wellcome again!
dd(model->images); //Array [...]
dd(model->block2); //Some test

```

...