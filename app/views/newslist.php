<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container news-listing-wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
            </div>
        </div>
        <?php if (isset($news) && count($news)) : ?>
            <div class="row">
                <?php foreach ($news as $item) : ?>
                    <div class="col-md-6 news-item">
                        <a href="<?= $this->e($url($page->getUrl() . '/' . $item->news)); ?>">
                            <h3 class="news-title"><?= $this->e($item->title); ?></h3>
                            <p class="news-date"><?= $this->e($intl->date('short', strtotime($item->fordate))); ?></p>
                            <?php if (isset($item->image)) : ?>
                                <img src="<?= $this->e($url('upload/' . $item->image)); ?>" alt="">
                            <?php endif; ?>
                            <p><?= $this->e($item->description); ?></p>
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
        <?php else : ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger"><?= $this->e($intl('records.notfound')); ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>