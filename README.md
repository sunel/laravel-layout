Laravel Layout
===================
> Simple way to build your page in an upgrade-compatible way

> I build this package inspired by magento way of handling layouts, Yes this package is a laravel port of magento layout module.If you are already familiar with Magento Layout then this will a breeze.

> **Note:** The below read me will give you the basic idea of this package and its under lying concepts. A great wiki is in progress which will have all the details of every know things that this package is capable of.

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

 `'Layout\Provider\LayoutServiceProvider',`
 
to providers 

## Configuration

Pull the configuration file into your application by running the following artisan command
```php
$ php artisan vendor:publish --provider="Layout\Provider\LayoutServiceProvider" --tag=config
```

This will create config file "layout.php" which will have the all the configuration required.

> **Note:** Will add the usage details of the config variables 

## Usage

####The basic concept of this package:

- Every page has a common structure which is defined through a layout XML Files. 
- A Layout is a collection of blocks in a tree structure.
- Each layout is a list of instruction on how a page must render what it must render and which Block(s) should kick off the rendering process.
- So basically all section in the layout are called View.
- Each View has a  BLOCK which is a php class and a TEMPLATE file which are tied to each other.
- Blocks are independent components which can have there own data model called within itself.
- This provides ultimate flexibility and re-usability of design.

####So how does this works :

> **Note:**  This package assumes that every router has name **(ROUTER NAME)** for it.

As said before every page has common layout in a XML file, you can find the files in the layout folder.
To move the files to your application run the following artisan command
```php
$ php artisan vendor:publish --provider="Layout\Provider\LayoutServiceProvider" --tag=layout
```

This is export basic css for a page layout

```php
$ php artisan vendor:publish --provider="Layout\Provider\LayoutServiceProvider" --tag=public
```
This will move the sample layout xml files into "resources/layout/vendor/layout/layout" folder.

This package already provides a default structure for a every page which will in the **default.xml** [here][1].

> **Note:**  For the example we are going to use a page which has layout with 3 columns [here][2]

The root element of any layout XML file is ```<layout>```.

Layout Handles
: In these XML files, you will see a number of snippets of XML enclosed in layout handle parent nodes, which are used to determine the type of page being displayed.

The ```<default>``` handle is the one that loads most of the pages and forms the base or to say skeleton of every page. So most of the case we use the one given in the page without modifying it.

The   ```<default>``` handle almost has all the required section in a page.Basically it like having a placeholder for every section. we need to define what content should be loaded for each request.

So how to that ??

> Lets assume we are requesting for home page of the application which has the name **"home"**
```php
Route::get('/',['as'=>'home',function(){	
	return render();	
}]);
```

Very simple just create a new xml file with any name and add the below snippet

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

Now in the layout config file replace the **'xml_location'** to the 

```php
'xml_location' => __DIR__.'/../resources/layout/layout',
```
Now when request for the router with the name **'home'**

**That's it you will see a fully render page**

###Rendering Process

Before rendering the page, all block elements defined in the layout are instantiated. Nested block elements define child blocks. If any block element defines an output attribute, then it is considered as an output block. Only the output blocks are rendered and added to the response. All other child blocks are rendered only if they are called by the parent block. Let’s see how this works.

The block root is defined as an output block. This block is defined in the **default.xml** file. With the particular value of the template attribute, this block defines the page template to be used when rendering it, 

i.e:

- 1 column
- 2 columns with left sidebar 
- 2 columns with right sidebar 
- 3 columns 

By default, the 3 columns template is assigned to the page. There are also other child blocks defined under root like **head, header, breadcrumbs, left, right, content, footer etc**. These child blocks are rendered in the root template file (3columns.blade.php) by calling something like this:

```php
{? $_this->getChildHtml('header') ?}
```

In any template, the child blocks can be rendered by calling the getChildHtml() method as above and passing the child block name as the first argument. If the method is called without arguments, it will render all child blocks of the current block that are defined in the layout XML for that block.

Hence, Layout processes layout using a recursive rendering process. First the root block then its child blocks and then the child’s child blocks and so on.

##What is ??
``` class="\Layout\Page\Html" ```

Its is the base abstract class for a block . you can use as it is or extent the class and add custom function that can be called inside the template file or used to get data from any model.

###Layout Elements

A layout handle may contain the following elements:

**block:** This element is used to define a new block. This element is usually defined inside a reference element when we want to create a new block. The block element must have a name attribute, which is a unique identifier of the block in the layout and a type attribute, which defines the block class name. If the block is of type or subtype of core/template, it can also have the template attribute which defines the actual phtml template file to be used for rendering the block

**reference:** This element is used to link an already defined block in any layout XML. To add any child block to an existing block, to modify attributes of an existing block or to perform any action on an existing block, the reference element is used to link to the existing block. The reference element must have a name attribute which refers to the existing block’s name.

**remove:** This element is used to remove an existing block from the layout. The block to be removed is specified with the name attribute.

**action:** This element defines an action to be performed on the referenced or newly defined block. An action is simply a method of the block instance on which it is to be executed. The method attribute defines the method name in the block instance and all child elements of the action element are treated as parameters to the method. This element can be placed inside reference or block elements.

**update:** This element loads an existing layout handle into the current layout handle. It provides a kind of inheritance of layout handles. It must have the handle attribute, which defines the handle of the block to be included.



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

  [1]:https://github.com/sunel/laravel-layout/blob/develop/layout/default.xml
  [2]: https://photos-3.dropbox.com/t/2/AAA4hiywutlrZrckIqwWzzfA49JehTqpghXAse1kl6S9kg/12/93078715/png/32x32/1/1432735200/0/2/3lc.png/CLuJsSwgASACIAMgBCAFIAYgBygB/SU_wk5eoHQPT-5ISy334QjMS8x-7hZy75CEzG98c3k4?size_mode=5
