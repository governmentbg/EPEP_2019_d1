<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container inner-page-wrapper">
        <div class="row">
            <div class="col-lg">
                <h3 class="inner-page-title"><?= $this->e($news->title); ?></h3>
                <p class="inner-page-meta">
                    <span><?= $this->e($intl('news.created') . ' ' . $intl->date('short', strtotime($news->fordate))); ?></span>
                    <span><?= $this->e($intl('news.updated') . ' ' . $intl->date('short', strtotime($news->_updated))); ?></span>
                    <?php if (isset($news->tags) && is_array($news->tags)) : ?>
                        <?php foreach ($news->tags as $tag) : ?> 
                            <span><?= $this->e($tag['name'] ?? null); ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <a class="inner-print" href="#"><?= $this->e($intl('news.print')); ?></a>
                </p>
                <?php if ($news->image) : ?>
                    <div><img class="news-image" src="<?= $this->e($url('upload/' . $news->image)); ?>"/></div>
                <?php endif; ?>
                <div class="news-content">
                    <?= $content($news->content); ?>
                </div>
                <?php if (isset($news->gallery) && $news->gallery) : ?>
                    <h2 class="subtitle sub-gallery"><?= $this->e($intl('news.gallery')); ?></h2>
                    <?php if ($news->gallery->image) : ?>
                        <div class="row sub-gallery-row">
                            <div class="col-lg">
                                <div class="gallery-image">
                                    <a href="#" class="image-before"></a>
                                    <img src="<?= $this->e($url('upload/' . $news->gallery->image['url'])); ?>" />
                                    <a href="#" class="image-after"></a>
                                </div>
                                <div class="photo-desc<?= (!isset($news->gallery->image['settings']['description']) || !strlen($news->gallery->image['settings']['description']) ? ' hidden' : ''); ?>">
                                    <h4><?= $this->e($news->gallery->image['settings']['title'] ?? null); ?></h4>
                                    <p><?= $this->e($news->gallery->image['settings']['description'] ?? null); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <script>
                        var images = <?= json_encode($news->gallery->images, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
                            current = 0;
                        $('.image-before, .image-after').on('click', function (e) {
                            e.preventDefault();
                            if ($(this).hasClass('image-before')) {
                                current = current === 0 ? images.length - 1 : current - 1;
                            }
                            else {
                                current = current === images.length - 1 ? current = 0 : current + 1;
                            }
                            var item = images[current];
                            if (item.url) {
                                $('.gallery-image img').attr('src', "<?= $this->e($url('upload')); ?>" + "/" + item.url);
                                if (item.settings && (item.settings.description || item.settings.title)) {
                                    $('.photo-desc > p').html(item.settings.description).end().find('h4').html(item.settings.title).closest('.photo-desc').removeClass('hidden');
                                }
                                else {
                                    $('.photo-desc').addClass('hidden');
                                }
                            }
                        });
                    </script>
                <?php endif; ?>
                <?php if (isset($news->files) && is_array($news->files) && count($news->files)) : ?>
                    <h2 class="subtitle"><?= $this->e($intl('news.files')); ?></h2>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="files">
                                <?php foreach ($news->files as $file) : ?>
                                    <li>
                                        <a class="row" href="<?= $this->e($url('upload/' . $file['url'])); ?>">
                                            <div class="col-md-8"><?= $this->e($file['name']); ?></div>
                                            <div class="col-md-2"><?= $this->e($file['size']); ?></div>
                                            <div class="col-md-2 file-type"><span class="file file-<?= $this->e(strtolower($file['ext'])); ?>"><?= $this->e(strtolower($file['ext'])); ?></span></div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (isset($news->related) && is_array($news->related) && count($news->related)) : ?>
                    <h2 class="subtitle"><?= $this->e($intl('news.related')); ?></h2>
                    <div class="row">
                        <?php foreach ($news->related as $related) : ?>
                            <div class="col-md-4 col-12 news-list-item">
                                <?php if ($related->image) : ?>
                                    <a href="<?= $this->e($url($page->getUrl() . '/' . $related->news)); ?>"><img src="<?= $this->e($url('upload/' . $related->image)); ?>" alt=""></a>
                                <?php endif; ?>
                                <div class="news-description">
                                    <a href="<?= $this->e($url($page->getUrl() . '/' . $related->news)); ?>">
                                        <h3><?= $this->e($related->title); ?></h3>
                                        <p><?= $this->e($intl->date('short', strtotime($related->fordate))); ?></p>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<script>
    $('.inner-print').on('click', function (e) {
        e.preventDefault();
        window.open("<?= $this->e($url($page->getUrl() . '/' . $news->news, ['print' => 1], 1)); ?>");
    });
</script>

<?php if (CLI) : ?>
<script type="index/json">
    <?= json_encode([
    'title'   => htmlspecialchars_decode(strip_tags($news->title)),
    'data'    => htmlspecialchars_decode(strip_tags($news->content)),
    'module'  => 'news',
    'id'      => $news->news,
    'fordate' => $news->fordate
]) ?>
</script>
<?php endif; ?>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>