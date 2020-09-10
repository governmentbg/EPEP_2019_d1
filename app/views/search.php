<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container wide-search-wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
            </div>
        </div>
        <form action="" method="GET" class="wide-search-wrapper">
            <div class="row">
                <div class="col-md-12">
                    <label for="search-term"><?= $this->e($intl('search.term')); ?></label>
                    <input id="search-term" name="q" type="text" value="<?= $this->e($q ?? null); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label for="criteria"><?= $this->e($intl('search.criteria')); ?></label>
                    <select class="arrow" name="category" id="criteria">
                        <option value=""><?= $this->e($intl('search.criteria.all')); ?></option>
                        <?php if (isset($categories) && count($categories)) : ?>
                            <?php foreach ($categories as $cat) : ?>
                                <option value="<?= $this->e($cat); ?>"<?= (isset($category) && $category == $cat ? ' selected="selected"' : ''); ?>><?= $this->e($intl('search.criteria.' . $cat)); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn search-submit"><?= $this->e($intl('search.submit')); ?></button>
                </div>
            </div>
        </form>
        <?php if (isset($q) && strlen($q)) : ?>
            <div class="row">
                <div class="col-md-12">
                    <p class="search-results-title"><?= $this->e($intl('search.result')); ?><strong>‘<?= $this->e($q); ?>’</strong><?= (isset($count) && $count ? ' (' . $this->e($count . ' ' . $intl('search.count')) . ')' : ''); ?></p>
                    <?php if (isset($items) && count($items)) : ?> 
                        <ul>
                            <?php foreach ($items as $item) : ?>
                                <li class="single-result">
                                    <a class="result-title" href="<?= $this->e($url($item->url)); ?>" target="_blank">
                                        <h3><?= $this->e($item->title); ?></h3>
                                    </a>
                                    <p class="result-text">
                                        <?= $item->data; ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <div class="alert alert-danger"><?= $this->e($intl('search.noresults')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (isset($items) && count($items)) : ?>
            <div class="row">
                <div class="col-md-12">
                    <ul class="pages-list">
                    <?php if ($currentPage != 1) : ?>
                        <li><a href="<?= $this->e($url($page->getUrl(), ['p' => 1, 'q' => $q, 'category' => $category ])); ?>"><?= $this->e($intl('pagination.first')); ?></a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= ceil($count / $perpage); $i++) : ?>
                        <li><a href="<?= $this->e($url($page->getUrl(), ['p' => $i, 'q' => $q, 'category' => $category ])); ?>" <?php if ($i === $currentPage) :
                            ?> class="active"<?php
                                     endif ?>><?= $this->e($i) ?></a></li>
                    <?php endfor; ?>
                    <?php if ($currentPage != ceil($count / $perpage)) : ?>
                        <li><a href="<?= $this->e($url($page->getUrl(), ['p' => ceil($count / $perpage), 'q' => $q, 'category' => $category ])); ?>"><?= $this->e($intl('pagination.last')); ?></a></li>
                    <?php endif; ?>
                    </ul>
                </div>   
            </div>
        <?php endif; ?>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>