<?php $this->layout('_html', [ 'title' => $intl('404.title'), 'lang' => $lang ]); ?>
<header>
    <div class="container-fluid accessability-wrapper">
        <div class="row">
            <div class="top-menu">
                <div class="accessibility-menu">
                    <a href="#" class="accessibility-btn accessibility-normal" title="Standard color version">C</a>
                    <a href="#" class="accessibility-btn accessibility-blue" title="High contrast - blue">C</a>
                    <a href="#" class="accessibility-btn accessibility-dark" title="High contrast - dark">C</a>
                    <a href="#" class="accessibility-btn accessibility-yellow" title="High contrast - yellow">C</a>
                    <a href="#" class="accessibility-btn font-large" title="Large font size">A+</a>
                    <a href="#" class="accessibility-btn font-normal" title="Normal font size">A</a>
                    <a href="#" class="accessibility-btn font-small" title="Small font size">A-</a>
                    <a class="accessibility-menu-btn close-btn" href="#" title="">X</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg offset-md-3 access-tools order-2">
                <a class="accessibility-menu-btn" href="#accessibility" tabindex="1"><?= $this->e($intl('header.accessibility')); ?></a>
            </div>
            <div class="col-lg-4 portal order-1">
                <a href="#"><?= $this->e($intl('header.back')); ?></a>
            </div>
            <div class="col-lg col-md-2 sitemap order-3">
                <a href="<?= $url($lang . '/sitemap'); ?>"><?= $this->e($intl('header.sitemap')); ?></a>
            </div>
        </div>
    </div>
    <div class="container-fluid header-container">
        <div class="row">
            <div class="col-md-2 col-xl-1">
                <img class="logo" src="<?= $url('assets/images/gerb_site.png'); ?>" />
            </div>
            <div class="col-md-6 col-xl-9">
                <a href="<?= $url($homepage->getUrl()); ?>">
                    <p class="site-title"><?= $this->e($site['name'] ?? null); ?></p>
                    <p class="bg"><?= $this->e($intl('header.subtitle')); ?></p>
                </a>
            </div>
            <div class="col-md-3 col-xl-2 search-wrapper">
                <form action="<?= $url($lang . '/search'); ?>" method="GET">
                    <button type="submit"><img src="<?= $url('assets/images/search.svg'); ?>" alt=""></button> 
                    <input type="search" name="q"/>
                </form>
            </div>
        </div>
        <?php if (isset($menu) && count($menu)) : ?>
            <div class="row menu-wrapper">
                <div class="show-menu">
                    <div class="bar1"></div>
                    <div class="bar2"></div>
                    <div class="bar3"></div>
                </div>
                <ul class="menu-list">
                    <?php foreach ($menu as $item) : ?>
                        <li class="menu-item"><a href="<?= $url($item['url']); ?>"><?= $this->e($item['title']); ?><span class="mobile-sub"></span></a>
                        <?php if (isset($item['children']) && count($item['children'])) : ?>
                            <ul class="submenu-list">
                                <?php foreach ($item['children'] as $subItem) : ?>
                                    <li><a href="<?= $url($subItem['url']); ?>"><?= $this->e($subItem['title']); ?>
                                        <?php if (isset($subItem['children']) && count($subItem['children'])) : ?>
                                            <span class="mobile-sub-small"></span>
                                        <?php endif; ?>
                                    </a>
                                    <?php if (isset($subItem['children']) && count($subItem['children'])) : ?>
                                        <ul class="submenu-inner-list">
                                            <?php foreach ($subItem['children'] as $subItemSub) : ?>
                                                <li><a href="<?= $url($subItemSub['url']); ?>"><?= $this->e($subItemSub['title']); ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</header>
<main>
    <div class="container">
        <div class="row">
            <div class="col-md-12 column-404">
                <h4><?= $this->e($intl('404.title')); ?></h4>
                <h2><?= $this->e($intl('404.subtitle')); ?></h2>
                <p><?= $this->e($intl('404.text')); ?></p>
            </div>
        </div>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $lang ]); ?>