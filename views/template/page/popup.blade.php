<!DOCTYPE html>
<html>
<head>
    {!! $_this->getChildHtml('head') !!}
</head>
<body class="page-popup {!! $_this->getBodyClass()?$_this->getBodyClass():'' !!}">
    {!! $_this->getChildHtml('after_body_start') !!}
    {!! $_this->getChildHtml('content') !!}
    {!! $_this->getChildHtml('before_body_end') !!}
    {!! $_this->getAbsoluteFooter() !!}
</body>
</html>
