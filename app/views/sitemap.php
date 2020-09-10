<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container sitemap-wrapper">  
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
            </div>
        </div>
        <?php if ($root->hasChildren()) : ?>
            <div class="row">
                <?php foreach ($root->getChildren() as $child) : ?>
                    <?php if (isset($data[$child->id])) : ?>
                        <div class="col-md-4 col-6">
                            <p class="sitemap-section-title">
                                <a href="<?= $this->e($url($data[$child->id]['url'])); ?>"><?= $this->e($data[$child->id]['title'] ?? null); ?></a>
                            </p>
                            <?php if ($child->hasChildren()) : ?>
                                <?= $this->insert('_sitemap_column', [ 'node' => $child, 'data' => $data ]); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>