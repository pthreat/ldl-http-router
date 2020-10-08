<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Collection;

use LDL\Http\Router\Router;
use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Namespaceable\NamespaceableInterface;
use LDL\Type\Collection\Interfaces\Sorting\PrioritySortingInterface;

interface ExceptionHandlerCollectionInterface extends CollectionInterface, NamespaceableInterface, PrioritySortingInterface
{
    /**
     * @param Router $router
     * @param \Exception $exception
     * @param string $context
     * @throws \Exception
     */
    public function handle(
        Router $router,
        \Exception $exception,
        string $context
    ) : void;
}