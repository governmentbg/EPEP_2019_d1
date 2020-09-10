<?php if (isset($breadcrumb) && count($breadcrumb)) : ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <ul class="breadcrumb-wrapper">
                    <?php foreach ($breadcrumb as $crumb) : ?>
                        <li><a href="<?= $this->e($url($crumb['link'])); ?>"><?= $this->e($crumb['title']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>