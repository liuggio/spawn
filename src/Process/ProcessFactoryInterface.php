<?php

namespace Liuggio\Spawn\Process;

use Liuggio\Spawn\Process\Channel\Channel;

interface ProcessFactoryInterface
{
    public function create(Channel $channel, $inputLine, $processCounter, $template = null, $cwd = null);
}
