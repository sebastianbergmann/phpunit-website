<?php declare(strict_types=1);
namespace SebastianBergmann\PhpunitWebsite;

enum SupportStatus
{
    case BugfixSupport;
    case LifeSupport;
    case EndOfLife;
}
