<?php

namespace Liuggio\Concurrent\Process;

use Liuggio\Concurrent\Process\Channel\Channel;

interface ProcessFactoryInterface
{
    public function create(Channel $channel, $inputLine, $processCounter, $template = null, $cwd = null);
}
