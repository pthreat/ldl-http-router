<?php declare(strict_types=1);

namespace LDL\Http\Router\Route\Parameter;

use LDL\Type\Collection\Interfaces;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use Swaggest\JsonSchema\SchemaContract;

class ParameterCollection extends ObjectCollection implements \JsonSerializable
{
    /**
     * @var SchemaContract|null
     */
    private $schema;

    /**
     * ParameterCollection constructor.
     * @param iterable|null $items
     * @param SchemaContract|null $schema
     */

    public function __construct(
        iterable $items = null,
        SchemaContract $schema=null
    )
    {
        parent::__construct($items);
        $this->schema = $schema;
    }

    public function freezeParameters()
    {
        /**
         * @var ParameterInterface $param
         */
        foreach($this as $param){
            $param->freeze();
        }
    }

    /**
     * @param ParameterInterface $item
     * @param null $key
     * @return Interfaces\CollectionInterface
     */
    public function append($item, $key = null): Interfaces\CollectionInterface
    {
        return parent::append($item, $item->getName()); // TODO: Change the autogenerated stub
    }

    /**
     * @return SchemaContract|null
     */
    public function getSchema() : ?SchemaContract
    {
        return $this->schema;
    }

    /**
     * @param string $name
     * @return ParameterInterface
     * @throws \LDL\Type\Collection\Exception\UndefinedOffsetException
     */
    public function get(string $name) : ParameterInterface
    {
        return $this->offsetGet($name);
    }

    public function validateItem($item): void
    {
        parent::validateItem($item);

        if($item instanceof ParameterInterface){
            return;
        }

        $msg = sprintf(
          '"%s" expected value must be of type "%s", "%s" was given',
          __CLASS__,
          ParameterInterface::class,
          get_class($item)
        );

        throw new Exception\InvalidParameterException($msg);
    }

    public function toArray() : array
    {
        return \iterator_to_array($this);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

}