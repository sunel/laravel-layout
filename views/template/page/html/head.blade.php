<meta http-equiv="Content-Type" content="{{ $block->getContentType() }}" />
<title>{{ $block->getTitle() }}</title>
<meta name="description" content="{{ htmlspecialchars($block->getDescription()) }}" />
<meta name="keywords" content="{{ htmlspecialchars($block->getKeywords()) }}" />
<meta name="robots" content="{{ htmlspecialchars($block->getRobots()) }}" />
<link rel="icon" href="{{ $block->getFaviconFile() }}" type="image/x-icon" />
<link rel="shortcut icon" href="{{ $block->getFaviconFile() }}" type="image/x-icon" />
{!! $block->getCssJsHtml() !!}
{!! $block->getChildHtml() !!}
{!! $block->getIncludes() !!}