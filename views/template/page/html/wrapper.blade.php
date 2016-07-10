{? $_tag = $block->hasTag() ? $block->getTag() : "div" ?}

<{{$_tag}} id="{{$block->getId()}}" {{ $block->getParams()}}>
	{!! $block->getChildHtml() !!}
</{{$_tag}}>
