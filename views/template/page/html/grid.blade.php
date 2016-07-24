<div class="col-xs-12">
  <div class="box">
      <div class="box-header">
        <h3 class="box-title">{{ $block->getGridTitle() }}</h3>
        <div class="box-tools pull-right">
	        @if($block->getAddUrl())
          <a href="{{ $block->getAddUrl() }}" class="btn btn-block btn-info"><i class="fa fa-plus"></i>
          Add
          </a>
          @endif
	    </div>
      </div>
      <div class="box-body">
        {!! $block->getGridHtml() !!}
      </div>
  </div>
</div>