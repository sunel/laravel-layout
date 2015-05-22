{? $items = $_this->getMenus() ?}
@if($items)
	<nav class="navbar navbar-default" role="navigation">
	    <div class="container-fluid">
	        <div class="navbar-header">
	            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu-collapse">
	                <span class="sr-only">Toggle navigation</span>
	                <span class="icon-bar"></span>
	                <span class="icon-bar"></span>
	                <span class="icon-bar"></span>
	            </button>

	            <a href="/" class="navbar-brand">Laravel</a>
	        </div>

	        <div class="collapse navbar-collapse" id="menu-collapse">
	            <ul class="nav navbar-nav">
	                 @include('render::template.page.html.topmenu.items',
      					array('items' => $items))
	            </ul>
	            {!! $_this->getChildHtml('topLinks') !!}
	        </div>
	    </div>
	</nav>
@endif
