<?php

declare(strict_types=1);

namespace Laminas\View\Helper;

use Laminas\View\Renderer\RendererInterface as Renderer;

use function assert;

/**
 * This trait is used to signal that the consuming helper will remove AbstractHelper from its hierarchy in version 3.0
 *
 * @internal Laminas\View
 */
trait DeprecatedAbstractHelperHierarchyTrait
{
    /**
     * Set the View object
     *
     * @deprecated
     *
     * @return self
     */
    public function setView(Renderer $view)
    {
        assert($this instanceof AbstractHelper);
        $this->view = $view;
        return $this;
    }

    /**
     * Get the view object
     *
     * @deprecated
     *
     * @return Renderer|null
     */
    public function getView()
    {
        assert($this instanceof AbstractHelper);
        return $this->view;
    }
}
