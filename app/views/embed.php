<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container inner-page-wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
                <?php if (isset($link) && strlen(trim($link))) : ?>
                    <iframe src="<?= $this->e(trim($link)); ?>"></iframe>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>

<?php if (CLI) : ?>
<script type="index/json">
    <?= json_encode([
    'title'   => htmlspecialchars_decode(strip_tags($page->title)),
    'data'    => htmlspecialchars_decode(strip_tags($page->content['content'] ?? null)),
    'module'  => 'page',
    'id'      => $page->id
]) ?>
</script>
<?php endif ?>