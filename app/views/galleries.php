<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container gallery-list-wrapper">  
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
            </div>
        </div>
        <?php if (isset($galleries) && count($galleries)) : ?>
            <div class="row">
                <?php foreach ($galleries as $gallery) : ?>
                    <div class="col-md-4 col-12 gallery-list-item">
                        <a href="<?= $this->e($url($page->getUrl() . '/' . $gallery->gallery)); ?>">
                            <?php if ($gallery->image) : ?>
                                <img src="<?= $this->e($url('upload/' . $gallery->image['url'])); ?>" alt="">
                            <?php endif; ?>
                            <div class="gallery-description">
                                <h3><?= $this->e($gallery->title); ?></h3>
                                <p><?= $this->e($intl->date('short', strtotime($gallery->fordate))); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <ul class="pages-list">
                    <?php if ($currentPage != 1) : ?>
                        <li><a href="<?= $this->e($url($page->getUrl(), ['p' => 1])); ?>"><?= $this->e($intl('pagination.first')); ?></a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= ceil($count / $perpage); $i++) : ?>
                        <li><a href="<?= $this->e($url($page->getUrl(), ['p' => $i])); ?>" <?php if ($i === $currentPage) :
                            ?> class="active"<?php
                                     endif ?>><?= $this->e($i) ?></a></li>
                    <?php endfor; ?>
                    <?php if ($currentPage != ceil($count / $perpage)) : ?>
                        <li><a href="<?= $this->e($url($page->getUrl(), ['p' => ceil($count / $perpage)])); ?>"><?= $this->e($intl('pagination.last')); ?></a></li>
                    <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>