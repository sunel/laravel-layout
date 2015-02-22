{? $_menu = $_this->getHtml('level-top') ?}
@if($_menu)
    <div class="container">
        <nav class="navbar navbar-default navbar-main" role="navigation">
            <div class="navbar-header">
                <a class="navbar-brand" href="#" data-toggle="collapse" data-target=".navbar-main-collapse">
                     {{ trans('page.menu') }}
                </a>
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <span class="sr-only">{{ trans('Toggle Navigation') }}</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse navbar-main-collapse">
                <ul class="nav navbar-nav">
                   {!! $_menu !!}
                </ul>
            </div>
        </nav>
    </div>
<?php endif ?>