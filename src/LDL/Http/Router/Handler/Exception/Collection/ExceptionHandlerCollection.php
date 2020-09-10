<?php declare(strict_types=1);

namespace LDL\Http\Router\Handler\Exception\Collection;

use LDL\Http\Router\Handler\Exception\ExceptionHandlerInterface;
use LDL\Type\Collection\AbstractCollection;
use LDL\Type\Exception\TypeMismatchException;

class ExceptionHandlerCollection extends AbstractCollection
{
    public function validateItem($item) : void
    {
        if($item instanceof ExceptionHandlerInterface){
            return;
        }

        $msg = sprintf(
            'Item must be an instance of "%s", "%s" was given',
            ExceptionHandlerInterface::class,
            get_class($item)
        );

        throw new TypeMismatchException($msg);
    }

    public function sort(string $order = 'asc'): self
    {
        if (!in_array($order, ['asc', 'desc'])) {
            throw new \LogicException('Order must be one of "asc" or "desc"');
        }

        $items = \iterator_to_array($this);

        usort(
            $items,
            /**
             * @var ExceptionHandlerInterface $a
             * @var ExceptionHandlerInterface $b
             *
             * @return bool
             */
            static function ($a, $b) use ($order) {
                $prioA = $a->getPriority();
                $prioB = $b->getPriority();

                return 'asc' === $order ? $prioA <=> $prioB : $prioB <=> $prioA;
            }
        );

        return new static($items);
    }
}