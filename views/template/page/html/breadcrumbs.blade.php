@if($crumbs && is_array($crumbs))
<div class="breadcrumbs">
    <ul>
        @foreach($crumbs as $_crumbName=>$_crumbInfo)
            <li class="{{ $_crumbName }}">
            @if($_crumbInfo['link'])
                <a href="{{ $_crumbInfo['link'] }}" title="{{ $_crumbInfo['title'] }}">{{ $_crumbInfo['label'] }}</a>
            @elseif($_crumbInfo['last'])
                <strong>{{ $_crumbInfo['label'] }}</strong>
            @else
                {{ $_crumbInfo['label'] }}
            @endif
            @if(!$_crumbInfo['last'])
                <span>/ </span>
            @endif
            </li>
        @endforeach
    </ul>
</div>
@endif
