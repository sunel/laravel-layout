<meta http-equiv="Content-Type" content="{{ $_this->getContentType() }}" />
<title>{{ $_this->getTitle() }}</title>
<meta name="description" content="{{ htmlspecialchars($_this->getDescription()) }}" />
<meta name="keywords" content="{{ htmlspecialchars($_this->getKeywords()) }}" />
<meta name="robots" content="{{ htmlspecialchars($_this->getRobots()) }}" />
<link rel="icon" href="{{ $_this->getFaviconFile() }}" type="image/x-icon" />
<link rel="shortcut icon" href="{{ $_this->getFaviconFile() }}" type="image/x-icon" />
{!! $_this->getCssJsHtml() !!}
{!! $_this->getChildHtml() !!}
{!! $_this->getIncludes() !!}