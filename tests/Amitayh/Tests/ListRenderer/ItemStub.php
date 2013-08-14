<?php

namespace Amitayh\Tests\ListRenderer;

use Amitayh\ListRenderer\ItemInterface;

class ItemStub implements ItemInterface
{

    private $level;

    private $contents;

    public function __construct($level, $contents) {
        $this->contents = $contents;
        $this->level = $level;
    }

    public function getLevel() {
        return $this->level;
    }

    public function getContents() {
        return $this->contents;
    }

}
 