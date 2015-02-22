{? $_tag = $_this->hasTag() ? $_this->getTag() : "div" ?}

<{{$_tag}} id="{{$_this->getId()}}" {{ $_this->getParams()}}>
	{!! $_this->getChildHtml() !!}
</{{$_tag}}>
