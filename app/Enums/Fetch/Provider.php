<?php

namespace App\Enums\Fetch;

enum Provider : string
{
    case NEWSAPI = "newsapi";
    case GUARDIAN = "guardian";
    case NYT = "nyt";
}
