<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container zop-wrapper inner-zop">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($zop->name); ?></h2>
                <p class="inner-page-meta">
                    <span><?= $this->e($intl('zop.created') . ' ' . $intl->date('short', strtotime($zop->created))); ?></span>
                    <span><?= $this->e($intl('zop.updated') . ' ' . $intl->date('short', strtotime($zop->_updated))); ?></span>
                    <?php if (isset($categories[$zop->category])) : ?>
                        <span class="inner-zop-category"><?= $this->e($categories[$zop->category]); ?></span>
                    <?php endif; ?>
                </p>
                <div class="row zop-row">
                    <div class="col-md-6 zop-content">
                        <?= $content($zop->description); ?>
                    </div>
                    <div class="col-md-6">
                        <?php if (count($zop->files)) : ?>
                            <ul class="files">
                                <?php foreach ($zop->files as $file) : ?>
                                    <li>
                                        <a class="row" href="<?= $this->e($url('upload/' . $file['url'])); ?>">
                                            <div class="col-md-8"><?= $this->e($file['name']); ?></div>
                                            <div class="col-md-2"><?= $this->e($file['size']); ?></div>
                                            <div class="col-md-2 file-type"><span class="file file-<?= $this->e(strtolower($file['ext'])); ?>"><?= $this->e(strtolower($file['ext'])); ?></span></div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>

<?php if (CLI) : ?>
<script type="index/json">
    <?= json_encode([
    'title'   => htmlspecialchars_decode(strip_tags($zop->name)),
    'data'   => htmlspecialchars_decode(strip_tags($zop->description)),
    'module'  => 'zop',
    'id'      => $zop->zop,
]) ?>
</script>
<?php endif ?>