<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Compiler;

enum CompilerStep
{
    case BEFORE_VALIDATION;
    case AFTER_VALIDATION;
}
