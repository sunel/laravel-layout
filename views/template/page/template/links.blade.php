{? $_links = $_this->getLinks(); ?}
@if(count($_links)>0)
<ul class="links" @if($_this->getName()) id="{{ $_this->getName() }}" @endif >
    	@foreach($_links as $_link)
        
        @if($_link instanceof \Ext\Block)
            {!! $_link->toHtml() !!}
        @else
            <li
            	{? if($_link->getIsFirst()|| $_link->getIsLast()): ?}
            		class="@if($_link->getIsFirst()) first @endif
            			   @if($_link->getIsLast()) last @endif "
            		{? endif; ?}

            		{{ $_link->getLiParams() }}
            >
            	{{ $_link->getBeforeText() }}
            	<a href="{{ $_link->getUrl() }}"
            	   title="{{ $_link->getTitle() }}"
            	    {{ $_link->getAParams() }} >
            		
            		  	{{ $_link->getLabel() }}

            	</a>

            	{{ $_link->getAfterText() }}
          </li>
        @endif 
    @endforeach
</ul>
@endif