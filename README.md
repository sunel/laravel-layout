## This package is under heavy development 
> I am working when i get time so please bare with me.
> I am working on full documentation and this read me gives you a small glimpse what this package about.
> Feel free to open issues and let's start a coverstation.


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
* `'Layout\Provider\LayoutServiceProvider',` to providers 

## Usage

Think of Layout as an easy way to structure a page. 

## Sample page template
```html
<!DOCTYPE html>
<html>
<head>
    {!! $_this->getChildHtml('head') !!}
</head>
<body{!! $_this->getBodyClass()?' class="'.$_this->getBodyClass().'"':'' !!}>

    {!! $_this->getChildHtml('after_body_start') !!}
    {!! $_this->getChildHtml('header') !!}

    <div class="container">
        {!! $_this->getChildHtml('breadcrumbs') !!}

        <div class="layout layout-3-cols">
            <aside role="complementary">
                {!! $_this->getChildHtml('left') !!}
            </aside>
            <div role="main">
                {!! $_this->getChildHtml('messages') !!}
                {!! $_this->getChildHtml('content') !!}
            </div>
            <aside role="complementary">
                {!! $_this->getChildHtml('right') !!}
            </aside>
        </div>
    </div>

    {!! $_this->getChildHtml('footer') !!}
    {!! $_this->getChildHtml('before_body_end') !!}
    {!! $_this->getAbsoluteFooter() !!}

</body>
</html>
```
## Sample page layout
```xml
<?xml version="1.0"?>
<layout version="0.1.0">
<!--
Default layout, loads most of the pages
-->

    <default>
        <block class="\Layout\Page\Html" name="root" output="toHtml" template="render::template.page.3columns">

            <block class="\Layout\Page\Html\Head" name="head" as="head">
                <action method="addCss"><stylesheet>css/app.css</stylesheet></action>
                <action method="addCss">
                    <stylesheet>css/styles.css</stylesheet>
                </action>
                <!--<action method="addItem">
                        <type>css</type>
                        <name>css/styles-ie.css</name>
                        <params/><if>lt IE 8</if>
                </action>
                <action method="addCss">
                    <stylesheet>css/print.css</stylesheet>
                    <params>media="print"</params>
                </action>-->

                <action method="addJs"><script>js/script.js</script></action>
                <action method="addExternalItem">
                    <type>external_js</type>
                    <name>//code.jquery.com/jquery.js</name>
                    <params/>
                </action>
                <action method="addExternalItem">
                    <type>external_js</type>
                    <name>//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js</name>
                    <params/>
                </action>

            </block>

            <block class="\Layout\Page\BlockList" name="after_body_start" as="after_body_start" >
            </block>


            <block class="\Layout\Page\Html\Header" name="header" as="header">
                <block class="\Layout\Page\Html\Links" name="top.links" as="topLinks"/>
                <block class="\Layout\Page\BlockList" name="top.menu" as="topMenu" >
                    <block class="\Layout\Page\Html\TopMenu" name="topnav" template="render::template.page.html.topmenu"/>
                </block>
                <block class="\Layout\Page\Html\Wrapper" name="top.container" as="topContainer">
                    <action method="setElementClass"><value>top-container</value></action>
                </block>
            </block>

            <block class="\Layout\Page\Html\Breadcrumbs" name="breadcrumbs" as="breadcrumbs"/>

            <block class="\Layout\Page\BlockList" name="left" as="left">
            </block>

            <block class="\Layout\Page\Messages" name="messages" as="messages"/>

            <block class="\Layout\Page\BlockList" name="content" as="content">
            </block>

            <block class="\Layout\Page\BlockList" name="right" as="right">
            </block>

            <block class="\Layout\Page\Html\Footer" name="footer" as="footer" template="render::template.page.html.footer">
                <block class="\Layout\Page\Html\Wrapper" name="bottom.container" as="bottomContainer">
                    <action method="setElementClass"><value>bottom-container</value></action>
                </block>

                <block class="\Layout\Page\Html\Links" name="footer_links" as="footer_links" template="render::template.page.template.links"/>
            </block>

            <block class="\Layout\Page\BlockList" name="before_body_end" as="before_body_end">
                <block class="\Layout\Page\Html\CookieNotice" name="global_cookie_notice" as ="global_cookie_notice" template="render::template.page.html.cookienotice" before="-" />
            </block>
        </block>

    </default>
</layout>
```

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

## This extension allows developers to target their layout updates to multiple layout handles at once.
```xml
    <home ifhandle="customer_logged_in">
        <reference name="content">
            ...
        </reference>
    </home>
```