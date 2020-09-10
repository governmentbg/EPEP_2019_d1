<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <div class="container-fluid quick-access">
        <div class="row">
            <div class="links-wrapper offset-lg-7 col-lg-2">
                <p class="links-title"><?= $this->e($intl('home.links')); ?></p>
                <ul>
                    <?php if (isset($links) && count($links)) : ?>
                        <?php foreach ($links as $link) : ?>
                            <li class="links-item"><a href="<?= $this->e($url($link->getUrl())); ?>"><?= $this->e($link->title); ?></a></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="message-wrapper col-lg-2">
                <p class="message-title"><?= $this->e($intl('home.message')); ?></p>
                <?php if (isset($page->content['fordate']) && strtotime($page->content['fordate']) > 0) : ?>
                    <p class="message-date"><?= $this->e($intl->date('short', strtotime($page->content['fordate']))); ?></p>
                <?php endif; ?>
                <?php if (isset($page->content['message']) && strlen($page->content['message'])) : ?>
                    <div class="message-text"><?= $page->content['message']; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="container main-section">
        <div class="row justify-content-center">
            <div class="main-section-title">
                <h3><?= $this->e($page->content['title'] ?? null); ?></h3>
            </div>
            <div class="main-section-subtitle">
                <?= $content($page->content['content']); ?>
            </div>
        </div>
    </div>
    <div class="container-fluid e-services-wrapper">
        <div class="row justify-content-center">
            <h2><?= $this->e($intl('home.e-services')); ?></h2>
        </div>
        <?php if (isset($services) && count($services)) : ?>
            <div class="row services">
                <?php foreach ($services as $service) : ?>
                    <div class="col-lg col-md-4 service">
                        <div class="service-inner">
                            <?php if ($service->image) : ?>
                                <div class="icon-container">
                                    <img src="<?= $this->e($url('upload/' . $service->image)); ?>" alt="">
                                </div>
                            <?php endif; ?>
                            <h4><?= $this->e($service->title); ?></h4>
                            <p class="service-text"><a target="_blank" href="<?= $this->e($url($service->url)); ?>"><?= $this->e($service->description); ?></a></p>
                        </div>
                        <p class="entry-wrapper"><a target="_blank" class="entry" href="<?= $this->e($url($service->url)); ?>"><?= $this->e($service->label); ?></a></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'home' => true, 'langCode' => $page->getLanguageCode() ]); ?>