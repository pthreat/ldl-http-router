<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser\Repository;

use LDL\Http\Router\Response\Parser\ResponseParserInterface;
use LDL\Type\Collection\Traits\Namespaceable\NamespaceableTrait;
use LDL\Type\Collection\Traits\Selection\SingleSelectionTrait;
use LDL\Type\Collection\Traits\Validator\KeyValidatorChainTrait;
use LDL\Type\Collection\Types\Object\ObjectCollection;
use LDL\Type\Collection\Types\Object\Validator\InterfaceComplianceItemValidator;
use LDL\Type\Collection\Validator\UniqueKeyValidator;

class ResponseParserRepository extends ObjectCollection implements ResponseParserRepositoryInterface
{
    use NamespaceableTrait;
    use SingleSelectionTrait;
    use KeyValidatorChainTrait;

    public function __construct(iterable $items = null)
    {
        parent::__construct($items);

        $this->getValidatorChain()
            ->append(new InterfaceComplianceItemValidator(ResponseParserInterface::class))
            ->lock();

        $this->getKeyValidatorChain()
            ->append(new UniqueKeyValidator());
    }
}