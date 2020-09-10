<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container poll-wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
            </div>
        </div>
        <?php if (isset($polls) && count($polls)) : ?>
            <div class="row">
                <?php foreach ($polls as $poll) : ?>
                    <div class="col-md-12">
                        <div class="poll">
                            <a href="<?= $this->e($url($page->getUrl() . '/' . $poll->poll)); ?>">
                                <h3 class="poll-title"><?= $this->e($poll->title); ?></h3>
                                <p class="poll-date"><?= $this->e($intl->date('short', strtotime($poll->visible_beg)) . ' - ' . $intl->date('short', strtotime($poll->visible_end))); ?></p>
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