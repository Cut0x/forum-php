<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Services\Markdown\MarkdownRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MarkdownPreviewController extends Controller
{
    public function __invoke(Request $request, MarkdownRenderer $renderer): Response
    {
        $content = (string) $request->input('content', '');

        return response($renderer->toHtml($content));
    }
}
