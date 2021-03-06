<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser\Repository;

use LDL\Type\Collection\Interfaces\CollectionInterface;
use LDL\Type\Collection\Interfaces\Selection\SingleSelectionInterface;
use LDL\Type\Collection\Interfaces\Validation\HasKeyValidatorChainInterface;

interface ResponseParserRepositoryInterface extends CollectionInterface, SingleSelectionInterface, HasKeyValidatorChainInterface
{

}