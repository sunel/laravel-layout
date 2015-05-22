<div class="container">
    <header role="banner">
        @if($_this->getIsHomePage())
        <h1 class="logo">
            <strong>{{ $_this->getLogoAlt() }}</strong>
            <a href="{{ $_this->getUrl('home') }}" title="{{ $_this->getLogoAlt() }}" class="logo">
                <img src="{{ $_this->getLogoSrc() }}" alt="{{ $_this->getLogoAlt() }}" />
            </a>
        </h1>
        @else
        <a href="{{ $_this->getUrl('home') }}" title="{{ $_this->getLogoAlt() }}" class="logo">
            <strong>{{ $_this->getLogoAlt() }}</strong>
            <img src="{{ $_this->getLogoSrc() }}" alt="{{ $_this->getLogoAlt() }}" />
        </a>
        @endif
        <div class="quick-access">
            <div class="clearfix">
                {!! $_this->getChildHtml('topLinks') !!}
            </div>
        </div>
        {!! $_this->getChildHtml('topContainer') !!}
    </header>
</div>
{!! $_this->getChildHtml('topMenu') !!}
