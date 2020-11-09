<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Formatter;

abstract class AbstractResponseFormatter implements ResponseFormatterInterface
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

}