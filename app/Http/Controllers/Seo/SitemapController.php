<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seo;

use App\Actions\Seo\GenerateSitemapAction;
use Illuminate\Http\Response;

class SitemapController
{
    public function __invoke(GenerateSitemapAction $action): Response
    {
        return response($action(), 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
