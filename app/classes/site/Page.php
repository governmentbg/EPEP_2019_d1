<?php
namespace site;

class Page
{
    protected $pages;
    protected $data;
    protected $children = null;
    protected $ancestors = null;

    public function __construct(PageFactory $pages, array $data, array $ancestors = null, array $children = null)
    {
        $this->pages = $pages;
        $this->data = $data;
        $this->ancestors = $ancestors;
        $this->children = $children;
    }
    public function __get($k)
    {
        return $this->data[$k] ?? null;
    }
    public function getUrl(): string
    {
        if (strlen($this->redirect)) {
            return $this->redirect;
        }
        if (strlen($this->url)) {
            return trim($this->url, '/*');
        }
        return $this->getLanguageCode() . '/' . $this->id;
    }
    public function isHidden(bool $checkUp = true): bool
    {
        if ($this->hidden) {
            return true;
        }
        if ($checkUp) {
            foreach ($this->getAncestors() as $ancestor) {
                if ($ancestor->hidden) {
                    return true;
                }
            }
        }
        return false;
    }
    public function hasChildren(bool $includeHidden = false): bool
    {
        return count($this->getChildren($includeHidden)) > 0;
    }
    public function getChildren(bool $includeHidden = false): array
    {
        if ($this->children === null) {
            $temp = $this->pages->getPage($this->id, $this->lang);
            $this->children = $temp->getChildren();
            $this->ancestors = $temp->getAncestors();
        }
        if ($includeHidden) {
            return $this->children;
        }
        return array_filter($this->children, function ($v) {
            return !$v->isHidden(false);
        });
    }
    public function getAncestors(): array
    {
        if ($this->ancestors === null) {
            $temp = $this->pages->getPage($this->id, $this->lang);
            $this->children = $temp->getChildren();
            $this->ancestors = $temp->getAncestors();
        }
        return $this->ancestors;
    }
    public function getMenu(string $type = 'top')
    {
        return array_filter($this->pages->getMenu($this->lang, $type), function ($v) {
            return !$v->isHidden(false);
        });
    }
    public function getLanguageCode()
    {
        return $this->pages->langIDtoCode($this->lang);
    }
    public function getTranslations() : array
    {
        $translations = [];
        foreach ($this->pages->getLanguages(false) as $l => $code) {
            if ($l === $this->lang) {
                $translations[$code] = $this->getUrl();
            } else {
                try {
                    $translations[$code] = $this->pages->getPage($this->id, $l)->getUrl();
                } catch (\Exception $e) {
                    try {
                        $translations[$code] = $this->pages->getHomepage($l)->getUrl();
                    } catch (\Exception $e) {
                    }
                }
            }
        }
        return $translations;
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = array_reverse(
            array_filter(
                $this->getAncestors(),
                function ($v) {
                    return (int)$v->breadcrumb > 0;
                }
            )
        );
        $breadcrumb[] = $this;
        return $breadcrumb;
    }
    public function getTopMenu(): array
    {
        return $this->pages->getTopMenu($this->lang);
    }
    public function site(int $lang)
    {
        return $this->pages->site($lang);
    }
    public function getHomepage(int $lang = 1)
    {
        return $this->pages->getHomepage($lang);
    }
    public function footer()
    {
        return $this->pages->footer($this->lang);
    }
}
