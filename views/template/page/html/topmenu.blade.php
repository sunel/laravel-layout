{? $items = $_this->getMenus() ?}
@if($items)
    <div class="container">
    	<nav class="navbar navbar-inverse" role="navigation">
		    <div class="container">
		        <div class="navbar-header">
		            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu-collapse">
		                <span class="sr-only">Toggle navigation</span>
		                <span class="icon-bar"></span>
		                <span class="icon-bar"></span>
		                <span class="icon-bar"></span>
		            </button>
		
		            <a href="#" class="navbar-brand">Menu</a>
		        </div>
		
		        <div class="collapse navbar-collapse" id="menu-collapse">
		            <ul class="nav navbar-nav">
		                 @include('render::template.page.html.topmenu.items', 
          					array('items' => $items))
		            </ul>
		        </div>
		    </div>
		</nav>
    </div>
@endif