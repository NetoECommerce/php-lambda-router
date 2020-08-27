<?php declare(strict_types=1);

namespace Neto\Lambda\Controller;

use Neto\Container\ContainerAwareInterface;
use Neto\Container\ContainerAwareTrait;
use Psr\Log\LoggerInterface;

class AbstractController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function get($id, $default = null)
    {
        $container = $this->getContainer();

        if ($container && $container->has($id)) {
            return $container->get($id);
        }

        return $default;
    }

    protected function log($level, $message, array $context = [])
    {
        $logger = $this->get(LoggerInterface::class);

        if ($logger) {
            $logger->log($level, $message, $context);
        }
    }
}
