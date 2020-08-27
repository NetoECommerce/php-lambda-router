<?php declare(strict_types=1);

namespace Neto\Lambda\Test\Fixture;

use Phake;
use Psr\Http\Message\ResponseInterface;

class StandaloneController
{
    public function barAction()
    {
        return Phake::mock(ResponseInterface::class);
    }
}
