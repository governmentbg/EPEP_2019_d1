<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container zop-wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
            </div>
        </div>
        <?php if (isset($zops) && count($zops)) : ?>
            <div class="row">
                <?php foreach ($zops as $zop) : ?>
                    <div class="col-md-12">
                        <div class="zop">
                            <a href="<?= $this->e($url($page->getUrl() . '/' . $zop->zop)); ?>">
                                <h3 class="zop-title"><?= $this->e($zop->name); ?></h3>
                                <p class="zop-date"><?= $this->e($intl->date('long', strtotime($zop->created))); ?></p>
                            </a>
                        </div>
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
            <div class="alert alert-danger"><?= $this->e($intl('records.notfound')); ?></div>
        <?php endif; ?>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>