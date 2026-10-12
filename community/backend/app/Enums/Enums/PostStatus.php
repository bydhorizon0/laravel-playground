<?php

namespace App\Enums\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
