<?php declare(strict_types=1);

namespace LDL\Http\Router\Response\Parser;

use LDL\Http\Router\Router;

interface ResponseParserInterface
{
    /**
     * @return string
     */
    public function getContentType() : string;

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return bool
     */
    public function isParsed() : bool;

    /**
     * @return string|null
     */
    public function getResult() : ?string;

    /**
     * @param array $options
     * @return ResponseParserInterface
     */
    public function setOptions(array $options) : ResponseParserInterface;

    /**
     * @return array
     */
    public function getOptions() : ?array;

    /**
     * @param Router $router Router object
     * @param array $data data to parsed by the corresponding parser
     *
     * @return void
     */
    public function parse(
        Router $router,
        $data
    ) : void;
}