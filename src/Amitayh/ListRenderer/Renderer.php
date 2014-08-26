<?php

namespace Amitayh\ListRenderer;

class Renderer
{

    /**
     * @var array
     */
    private $mainListAttributes;

    /**
     * @var array
     */
    private $subListAttributes;

    /**
     * @var int
     */
    private $currentLevel;

    /**
     * @param array $attributes
     */
    public function setMainListAttributes(array $attributes) {
        $this->mainListAttributes = $attributes;
    }

    /**
     * @param array $attributes
     */
    public function setSubListAttributes(array $attributes) {
        $this->subListAttributes = $attributes;
    }

    /**
     * @param \Iterator $itemsIterator
     * @return string
     */
    public function render(\Iterator $itemsIterator) {
        $html = $this->renderTag('ul', $this->mainListAttributes);

        $this->currentLevel = $items = 0;
        foreach ($itemsIterator as $item) {
            $html .= $this->renderListItem($item, $items);
            $items++;
        }
        if ($items > 0) {
            $html .= $this->closeLevels($this->currentLevel);
        }

        $html .= '</ul>';

        return $html;
    }

    /**
     * @param ItemInterface $item
     * @param int $index
     * @return string
     * @throws InvalidLevelException
     */
    private function renderListItem(ItemInterface $item, $index) {
        $html = '';
        $itemLevel = $item->getLevel();
        if ($index == 0 && $itemLevel > 0) {
            throw new InvalidLevelException();
        }
        if ($itemLevel > $this->currentLevel) {
            if ($itemLevel - $this->currentLevel > 1) {
                throw new InvalidLevelException();
            }
            $html .= $this->renderTag('ul', $this->subListAttributes);
        } elseif ($itemLevel < $this->currentLevel) {
            $html .= $this->closeLevels($this->currentLevel - $itemLevel);
        } elseif ($index > 0) {
            $html .= '</li>';
        }
        $html .= $this->renderTag('li');
        $html .= $item->getContents();
        $this->currentLevel = $itemLevel;

        return $html;
    }

    /**
     * @param int $diff
     * @return string
     */
    private function closeLevels($diff) {
        return str_repeat('</li></ul>', $diff) . '</li>';
    }

    /**
     * @param string $tag
     * @param array $attributes
     * @return string
     */
    private function renderTag($tag, array $attributes = null) {
        $html = "<$tag";
        if ($attributes) {
            foreach ($attributes as $attribute => $value) {
                $html .= sprintf(' %s="%s"', $attribute, htmlentities($value, ENT_QUOTES, 'utf-8'));
            }
        }
        $html .= '>';

        return $html;
    }

}
