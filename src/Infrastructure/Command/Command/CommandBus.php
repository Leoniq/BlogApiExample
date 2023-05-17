<?php

namespace App\Infrastructure\Command\Command;

use App\Application\Form\Command\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class CommandBus
{
    private ContainerInterface $container;

    /**
     * CommandBus constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param Command $command
     */
    public function handle(Command $command): void
    {
        $this->getHandler($command)->handle($command);
    }

    /**
     * @param Command $command
     * @return object
     */
    private function getHandler(Command $command): object
    {
        $commandClassName = substr(
            get_class($command),
            strrpos(get_class($command), '\\') + 1,
            strlen(get_class($command))
        );

        $handlerClass =
            'App\Infrastructure\Command\\'.
            $this->getNameFolder($commandClassName).
            '\\'.
            $commandClassName.
            'Handler'
        ;

        return $this->container->get($handlerClass);
    }

    /**
     * @param string $commandClassName
     *
     * @return string
     */
    private function getNameFolder(string $commandClassName): string
    {
        $nbCharacters = strlen($commandClassName);
        $nameFolder = '';

        for ($i = 0; $i < $nbCharacters; ++$i) {
            $letter = $commandClassName[$i];

            if (0 === $i && ctype_upper($letter)) {
                $nameFolder .= $letter;
            } elseif (ctype_upper($letter)) {
                break;
            } else {
                $nameFolder .= $letter;
            }
        }

        return $nameFolder;
    }
}
