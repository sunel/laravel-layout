Laravel Layout
===================
> Simple way to build your page in an upgrade-compatible way

# Installation
Add `sunel/laravel-layout` as a requirement to composer.json:

```json
{
  "require": {
    "sunel/laravel-layout": "dev-develop"
  }
}
```

Update your packages with `composer update` or install with `composer install`.

In *app/config/app.php* add:
* `'Layout\Provider\LayoutServiceProvider',`

to providers 

## Usage

## Sample Router layout 
```xml
<?xml version="1.0"?>
<layout version="0.1.0">
	<home>
		<reference name="content">
            <block class="\Layout\Page\Html" name="home" template="render::home">
            </block>
        </reference>
    	<reference name="left">
            <block class="\Layout\Page\Html" name="home.left" template="render::left">
            </block>
        </reference>
        <reference name="right">
            <block class="\Layout\Page\Html" name="home.right" template="render::right">
            </block>
        </reference>
	</home>
</layout>
```
## Sample Router

```php
Route::get('/',['as'=>'home',function(){	
	return render();	
}]);
```

## That's it you will see a fully render page 

## Features

* MultipleHandles

This allows developers to target their layout updates to multiple layout handles at once.

```xml
    <home ifhandle="customer_logged_in">
        <reference name="content">
            ...
        </reference>
    </home>
```