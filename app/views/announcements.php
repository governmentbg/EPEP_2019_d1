<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container inner-page-wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($page->title); ?></h2>
                <ul class="nav justify-content-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= $this->e($url($page->getUrl(), [ 'type' => 'properties' ])); ?>"><?= $this->e($intl('annoucements.properties.title')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= $this->e($url($page->getUrl(), [ 'type' => 'vehicles' ])); ?>"><?= $this->e($intl('annoucements.vehicles.title')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= $this->e($url($page->getUrl(), [ 'type' => 'assets' ])); ?>"><?= $this->e($intl('annoucements.assets.title')); ?></a>
                    </li>
                    <?php if (isset($page->content['link_old']) && strlen(trim($page->content['link_old']))) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->e($page->content['link_old']); ?>"><?= $this->e($intl('annoucements.old.title')); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <?php if (isset($data) && count($data) && isset($type)) : ?>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="results-table">
                                <thead>
                                    <tr>
                                        <?php switch ($type) :
                                            case 'properties': ?>
                                                <th><?= $this->e($intl('annoucements.properties.type')); ?></th>
                                                <th><?= $this->e($intl('annoucements.properties.area')); ?></th>
                                                <th><?= $this->e($intl('annoucements.properties.city')); ?></th>
                                                <th><?= $this->e($intl('annoucements.properties.address')); ?></th>
                                                <th><?= $this->e($intl('annoucements.properties.price')); ?></th>
                                                <th><?= $this->e($intl('annoucements.properties.judge')); ?></th>
                                                <th><?= $this->e($intl('annoucements.properties.published')); ?></th>
                                                <th><?= $this->e($intl('annoucements.properties.term')); ?></th>
                                                <th><?= $this->e($intl('annoucements.properties.public')); ?></th>
                                                <th><?= $this->e($intl('annoucements.properties.file')); ?></th>
                                                <th></th>
                                                <?php break; ?>
                                            <?php case 'vehicles': ?>
                                                <th><?= $this->e($intl('annoucements.vehicles.type')); ?></th>
                                                <th><?= $this->e($intl('annoucements.vehicles.brand')); ?></th>
                                                <th><?= $this->e($intl('annoucements.vehicles.model')); ?></th>
                                                <th><?= $this->e($intl('annoucements.vehicles.city')); ?></th>
                                                <th><?= $this->e($intl('annoucements.vehicles.price')); ?></th>
                                                <th><?= $this->e($intl('annoucements.vehicles.judge')); ?></th>
                                                <th><?= $this->e($intl('annoucements.vehicles.published')); ?></th>
                                                <th><?= $this->e($intl('annoucements.vehicles.term')); ?></th>
                                                <th><?= $this->e($intl('annoucements.vehicles.public')); ?></th>
                                                <th><?= $this->e($intl('annoucements.vehicles.file')); ?></th>
                                                <th></th>
                                                <?php break; ?>
                                            <?php case 'assets': ?>
                                                <th><?= $this->e($intl('annoucements.assets.type')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.name')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.sale_type')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.city')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.address')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.price')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.judge')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.published')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.term')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.public')); ?></th>
                                                <th><?= $this->e($intl('annoucements.assets.file')); ?></th>
                                                <th></th>
                                            <?php break; ?>
                                        <?php endswitch; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $row) : ?>
                                        <tr>
                                            <?php switch ($type) :
                                                case 'properties': ?>
                                                    <td><?= $this->e($row['property_types']['name'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['area'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['cities']['name'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['address'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['price'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['judge']['name_bg'] ?? ''); ?></td>
                                                    <td><?= $this->e(isset($row['_created']) ? date('d.m.Y H:i', strtotime($row['_created'])) : ''); ?></td>
                                                    <td><?= $this->e(isset($row['start_date']) ? date('d.m.Y', strtotime($row['start_date'])) : ''); ?> - <?= $this->e(isset($row['end_date']) ? date('d.m.Y', strtotime($row['end_date'])) : ''); ?></td>
                                                    <td><?= $this->e(isset($row['public_date']) ? date('d.m.Y H:i', strtotime($row['public_date'])) : ''); ?></td>
                                                    <?php if (is_array($row['files']) && isset($row['files'][0]['url'])) : ?>
                                                        <td><a target="_blank" href="<?= $this->e($row['files'][0]['url']); ?>"><?= $this->e($intl('annoucements.properties.file')); ?></a></td>
                                                    <?php else : ?>
                                                        <td></td>
                                                    <?php endif; ?>
                                                    <td><a target="_blank" href="<?= $this->e($row['url']); ?>"><?= $this->e($intl('annoucements.properties.link')); ?></a></td>
                                                    <?php break; ?>
                                                <?php case 'vehicles': ?>
                                                    <td><?= $this->e($row['vehicle_types']['name'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['vehicle_brands']['name'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['vehicle_models']['name'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['cities']['name'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['price'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['judge']['name_bg'] ?? ''); ?></td>
                                                    <td><?= $this->e(isset($row['_created']) ? date('d.m.Y H:i', strtotime($row['_created'])) : ''); ?></td>
                                                    <td><?= $this->e(isset($row['start_date']) ? date('d.m.Y', strtotime($row['start_date'])) : ''); ?> - <?= $this->e(isset($row['end_date']) ? date('d.m.Y', strtotime($row['end_date'])) : ''); ?></td>
                                                    <td><?= $this->e(isset($row['public_date']) ? date('d.m.Y H:i', strtotime($row['public_date'])) : ''); ?></td>
                                                    <?php if (is_array($row['files']) && isset($row['files'][0]['url'])) : ?>
                                                        <td><a target="_blank" href="<?= $this->e($row['files'][0]['url']); ?>"><?= $this->e($intl('annoucements.vehicles.file')); ?></a></td>
                                                    <?php else : ?>
                                                        <td></td>
                                                    <?php endif; ?>
                                                    <td><a target="_blank" href="<?= $this->e($row['url']); ?>"><?= $this->e($intl('annoucements.vehicles.link')); ?></a></td>
                                                    <?php break; ?>
                                                <?php case 'assets': ?>
                                                    <td><?= $this->e($row['assets_types']['name'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['name'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['sale_type'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['cities']['name'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['address'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['price'] ?? ''); ?></td>
                                                    <td><?= $this->e($row['judge']['name_bg'] ?? ''); ?></td>
                                                    <td><?= $this->e(isset($row['_created']) ? date('d.m.Y H:i', strtotime($row['_created'])) : ''); ?></td>
                                                    <td><?= $this->e(isset($row['start_date']) ? date('d.m.Y', strtotime($row['start_date'])) : ''); ?> - <?= $this->e(isset($row['end_date']) ? date('d.m.Y', strtotime($row['end_date'])) : ''); ?></td>
                                                    <td><?= $this->e(isset($row['public_date']) ? date('d.m.Y H:i', strtotime($row['public_date'])) : ''); ?></td>
                                                    <?php if (is_array($row['files']) && isset($row['files'][0]['url'])) : ?>
                                                        <td><a target="_blank" href="<?= $this->e($row['files'][0]['url']); ?>"><?= $this->e($intl('annoucements.assets.file')); ?></a></td>
                                                    <?php else : ?>
                                                        <td></td>
                                                    <?php endif; ?>
                                                    <td><a target="_blank" href="<?= $this->e($row['url']); ?>"><?= $this->e($intl('annoucements.assets.link')); ?></a></td>
                                                <?php break; ?>
                                            <?php endswitch; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="pages-list">
                            <?php if ($currentPage != 1) : ?>
                                <li><a href="<?= $this->e($url($page->getUrl(), ['p' => 1, 'type' => $type])); ?>"><?= $this->e($intl('pagination.first')); ?></a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= ceil($count / $perpage); $i++) : ?>
                                <li><a href="<?= $this->e($url($page->getUrl(), ['p' => $i, 'type' => $type])); ?>" <?php if ($i === $currentPage) : ?> class="active"<?php endif ?>><?= $this->e($i) ?></a></li>
                            <?php endfor; ?>
                            <?php if ($currentPage != ceil($count / $perpage)) : ?>
                                <li><a href="<?= $this->e($url($page->getUrl(), ['p' => ceil($count / $perpage), 'type' => $type])); ?>"><?= $this->e($intl('pagination.last')); ?></a></li>
                            <?php endif; ?>
                            </ul>
                        </div>
                    </div>
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
    'data'    => null,
    'module'  => 'page',
    'id'      => $page->id
]) ?>
</script>
<?php endif ?>