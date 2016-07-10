{? $_links = $block->getLinks(); ?}
@if(count($_links)>0)
<ul class="{{ $block->getClassNames() }} links" @if($block->getName()) id="{{ $block->getName() }}" @endif >
    @foreach($_links as $_link)
        @if($_link instanceof \Layout\Block)
            {!! $_link->toHtml() !!}
        @else
            <li @if($_link->getIsFirst()|| $_link->getIsLast()) class="@if($_link->getIsFirst()) first @endif @if($_link->getIsLast()) last @endif" @endif {{ $_link->getLiParams() }}>
            	<a href="{{ $_link->getUrl() }}" title="{{ $_link->getTitle() }}" {{ $_link->getAParams() }}>{{ $_link->getLabel() }}</a>
          </li>
        @endif
    @endforeach
</ul>
@endif
