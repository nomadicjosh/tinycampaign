<?php

use app\src\NodeQ\tc_NodeQ as Node;
use app\src\NodeQ\Helpers\Validate as Validate;


if (!Validate::table('php_encryption')->exists()) {
    Node::dispense('php_encryption');
}