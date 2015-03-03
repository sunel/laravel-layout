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

        <div class="layout layout-2-cols">
            <aside role="complementary">
                {!! $_this->getChildHtml('left') !!}
            </aside>
            <div role="main">
                {!! $_this->getChildHtml('messages') !!}
                {!! $_this->getChildHtml('content') !!}
            </div>
        </div>
    </div>

    {!! $_this->getChildHtml('footer') !!}
    {!! $_this->getChildHtml('before_body_end') !!}
    {!! $_this->getAbsoluteFooter() !!}

</body>
</html>
