<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?php $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container gallery-wrapper">  
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($gallery->title); ?></h2>
                <p class="inner-page-meta">
                    <span><?= $this->e($intl('gallery.created') . ' ' . $intl->date('short', strtotime($gallery->fordate))); ?></span>
                    <span><?= $this->e($intl('gallery.updated') . ' ' . $intl->date('short', strtotime($gallery->_updated))); ?></span>
                    <?php if (isset($gallery->tags) && is_array($gallery->tags)) : ?>
                        <?php foreach ($gallery->tags as $tag) : ?> 
                            <span><?= $this->e($tag['name'] ?? null); ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- <a class="inner-print" href="#"><?= $this->e($intl('gallery.print')); ?></a> -->
                </p>
                <div class="gallery-content"><?= $content($gallery->content); ?></div>
            </div>
            <div class="col-md-12 main-photo-wrapper p-1">
                <?php if ($gallery->image) : ?>
                    <a href="#" class="image-before"></a>
                    <img title="<?= $this->e($gallery->image['settings']['title'] ?? null); ?>" src="<?= $this->e($url('upload/' . $gallery->image['url'])); ?>" alt="">
                    <a href="#" class="image-after"></a>
                <?php endif; ?>
                <div class="photo-desc<?= (isset($gallery->image['settings']['description']) && strlen($gallery->image['settings']['description']) || (isset($gallery->image['settings']['title']) || strlen($gallery->image['settings']['title'])) ? '' : ' hidden'); ?>">
                    <h4><?= $this->e($gallery->image['settings']['title'] ?? null); ?></h4>
                    <p><?= $this->e($gallery->image['settings']['description'] ?? null); ?></p>
                </div>
            </div>
        </div>
        <?php if (count($gallery->images)) : ?>
            <div class="row">
                <?php foreach ($gallery->images as $image) : ?>
                    <div class="col-md-2 col-6 p-1">
                        <img title="<?= $this->e($image['settings']['title'] ?? null); ?>" data-description="<?= $this->e($image['settings']['description'] ?? null); ?>" class="small-image" src="<?= $this->e($url('upload/' . $image['url'])); ?>" alt="">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script>
        var current = 0;
        $('.small-image').on('click', function () {
            $('.main-photo-wrapper > img').attr('src', $(this).attr('src'));
            $('.photo-desc').addClass('hidden');
            var title = $(this).attr('title');
                descr = $(this).data('description');
            if (descr.length || title.length) {
                $('.photo-desc').find('p').html(descr).end().find('h4').html(title).end().removeClass('hidden');
            }
            current = $(this).closest('.p-1').index();
        });
        $('.image-before, .image-after').on('click', function (e) {
            e.preventDefault();
            var index = $(this).hasClass('image-before') ? -1 : 1;
            var item = $('.col-md-2.p-1').eq(current + index);
            if (!item.length) {
                item = $('.col-md-2.p-1').eq(0);
            }
            item.find('img').click();
        });
    </script> 
</main>

<?php if (CLI) : ?>
<script type="index/json">
    <?= json_encode([
    'title'   => htmlspecialchars_decode(strip_tags($gallery->title)),
    'data'    => htmlspecialchars_decode(strip_tags($gallery->content)),
    'module'  => 'galleries',
    'id'      => $gallery->gallery,
    'fordate' => $gallery->fordate
]) ?>
</script>
<?php endif ?>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>