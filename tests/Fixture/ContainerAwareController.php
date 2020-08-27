<?php declare(strict_types=1);

namespace Neto\Lambda\Test\Fixture;

use Neto\Lambda\Controller\AbstractController;
use Psr\Log\LogLevel;

class ContainerAwareController extends AbstractController
{
    public function getContainerKeyAction()
    {
        return $this->get('foo', 'bar');
    }

    public function logAction()
    {
        $this->log(LogLevel::ERROR, 'Danger to manifold');
    }
}
