<ul>
    <?php foreach ($node->getChildren() as $child) : ?>
        <?php if (isset($data[$child->id])) : ?>
            <li><a href="<?= $this->e($url($data[$child->id]['url'])); ?>"><?= $this->e($data[$child->id]['title']); ?></a>
                <?= $this->insert('_sitemap_column', [ 'node' => $child, 'data' => $data ]); ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>