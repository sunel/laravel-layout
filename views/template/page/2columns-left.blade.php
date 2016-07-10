<!DOCTYPE html>
<html>
<head>
    {!! $block->getChildHtml('head') !!}
</head>
<body{!! $block->getBodyClass()?' class="'.$block->getBodyClass().'"':'' !!}>

    {!! $block->getChildHtml('after_body_start') !!}
    {!! $block->getChildHtml('header') !!}

    <div class="container-fluid">
        {!! $block->getChildHtml('breadcrumbs') !!}

        <div class="layout layout-2-cols">
            <aside role="complementary">
                {!! $block->getChildHtml('left') !!}
            </aside>
            <div role="main">
                {!! $block->getChildHtml('messages') !!}
                {!! $block->getChildHtml('content') !!}
            </div>
        </div>
    </div>

    {!! $block->getChildHtml('footer') !!}
    {!! $block->getChildHtml('before_body_end') !!}
    {!! $block->getAbsoluteFooter() !!}

</body>
</html>
