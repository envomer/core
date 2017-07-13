<?php

namespace Envo\API;

use Envo\AbstractDTO;

class RequestDTO extends AbstractDTO
{
    public $parameters = null;

    public $limit;

    public $page;

    public $method;
}