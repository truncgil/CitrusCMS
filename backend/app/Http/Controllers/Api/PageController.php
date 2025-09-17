<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Http\Requests\CreatePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;

class PageController extends Controller
{
    public function index()
    {
        $q = Page::query()->with('author')->latest();
        return PageResource::collection($q->paginate(20));
    }

    public function store(CreatePageRequest $r)
    {
        $data = $r->validated();
        $data['author_id'] = $r->user()?->id;
        $page = Page::create($data);
        return (new PageResource($page->load('author')))->response()->setStatusCode(201);
    }

    public function show(Page $page)
    {
        return new PageResource($page->load('author'));
    }

    public function update(UpdatePageRequest $r, Page $page)
    {
        $page->update($r->validated());
        return new PageResource($page->fresh('author'));
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return response()->noContent();
    }
}