<?php $this->layout('_html', [ 'page' => $page, 'site' => $site ]); ?>

<?= $this->insert('_head', [ 'menu' => $menu, 'translations' => $translations, 'page' => $page, 'site' => $site  ]); ?>

<main>
    <?= $this->insert('_breadcrumb', [ 'breadcrumb' => $breadcrumb ]); ?>
    <div class="container poll-wrapper">
        <div class="row">
            <div class="col-md-12">
                <h2><?= $this->e($poll->title); ?></h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 poll-questions">
                <form action="" method="POST">
                    <?php if ($isPost && count($errors)) : ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error) : ?>
                                <li><?= $this->e($intl($error)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    <?php $key = 0; ?>
                    <?php foreach ($poll->questions as $question) : ?>
                        <?php $key++; ?>
                        <div class="question-wrapper">
                            <?php switch ($question['type']) :
                                case 'open':
                                    ?>
                                    <div class="question-wrapper">
                                        <p><?= $this->e($key . '. ' . $question['title']); ?></p>
                                        <div class="answer-wrapper">
                                            <textarea name="answer[<?= $this->e($question['question']); ?>]"><?= $this->e($data['answer'][$question['question']] ?? null); ?></textarea>
                                        </div>
                                    </div>
                                    <?php
                                          break; ?>
                                <?php case 'radio':
                                    ?>
                                    <p><?= $this->e($key . '. ' . $question['title']); ?></p>
                                    <div class="answer-wrapper">
                                        <?php foreach ($question['answers'] as $answer) : ?>
                                            <label class="question-container-check"><?= $this->e($answer['title']); ?>
                                                <input value="<?= $this->e($answer['answer']); ?>" type="radio" name="answer[<?= $this->e($question['question']); ?>]"<?= isset($data['answer'][$question['question']]) && (int) $data['answer'][$question['question']] == (int) $answer['answer'] ? ' checked="checked"' : ''; ?>>
                                                <span class="checkmark"></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php
                                          break; ?>
                                <?php case 'checkbox':
                                    ?>
                                    <p><?= $this->e($key . '. ' . $question['title']); ?></p>
                                    <div class="answer-wrapper">
                                        <?php foreach ($question['answers'] as $answer) : ?>
                                            <label class="question-container-check"><?= $this->e($answer['title']); ?>
                                                <input value="<?= $this->e($answer['answer']); ?>" type="checkbox" name="answer[<?= $this->e($question['question']); ?>][<?= $this->e($answer['answer']); ?>]"<?= isset($data['answer'][$question['question']][$answer['answer']]) && (int) $data['answer'][$question['question']][$answer['answer']] == (int) $answer['answer'] ? ' checked="checked"' : ''; ?>>
                                                <span class="checkmark"></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php
                                          break; ?>
                                <?php default:
                                    ?>
                                    <?php
                                          break; ?>
                            <?php endswitch; ?>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit">Изпрати</button>
                </form>
            </div>
        
        </div>
    </div>
</main>

<?= $this->insert('_foot', [ 'footer' => $footer, 'langCode' => $page->getLanguageCode() ]); ?>

<?php if (CLI) : ?>
<script type="index/json">
    <?= json_encode([
    'title'   => htmlspecialchars_decode(strip_tags($poll->title)),
    'data'    => implode(', ', array_map(function ($item) {
        return $item['title'];
    }, $poll->questions)),
    'module'  => 'polls',
    'id'      => $poll->poll
]) ?>
</script>
<?php endif ?>