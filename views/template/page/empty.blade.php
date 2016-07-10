<!DOCTYPE html>
<html>
<head>
    {!! $block->getChildHtml('head') !!}
</head>
<body class="page-empty {!! $block->getBodyClass()?$block->getBodyClass():'' !!}">
    {!! $block->getChildHtml('after_body_start') !!}
    {!! $block->getChildHtml('global_messages') !!}
    {!! $block->getChildHtml('content') !!}
    {!! $block->getChildHtml('before_body_end') !!}
    {!! $block->getAbsoluteFooter() !!}
</body>
</html>
