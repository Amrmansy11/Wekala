<?php

namespace App\Http\Controllers\Consumer\API;

use App\Models\Tag;
use App\Models\City;
use App\Models\Size;
use App\Models\Brand;
use App\Models\Color;
use App\Models\State;
use App\Models\Product;
use App\Models\Category;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Resources\GeneralResource;
use App\Http\Controllers\Consumer\API\ConsumerController;

class DropDownController extends ConsumerController
{
    public function index(string $model, Request $request): JsonResponse
    {
        $data = match ($model) {
            'roles' => Role::query()->where('guard_name', 'admin')->get(),
            'permissions' => $this->permissions(),
            'colors' => Color::query()->where('is_active', true)->get(),
            'categories' => Category::query()->whereNull('parent_id')->where('is_active', true)->get(),
            'sub_categories', 'sub_sub_categories' => Category::query()
                ->where('parent_id', $request->integer('parent_id'))
                ->where('is_active', true)
                ->get(),
            'materials' => Material::query()->where('is_active', true)->get(),
            'sizes' => Size::query()->where('is_active', true)->get(),
            'tags' => Tag::query()
                ->where('category_id', $request->integer('category_id'))
                ->where('is_active', true)
                ->get(),
            'tags_filter' => Tag::query()
                ->where('is_active', true)
                ->get(),
            'brands' => Brand::query()
                ->where('vendor_id', $request->integer('vendor_id'))
                ->orWhere('vendor_id', null)
                ->when($request->string('parent_id'), fn($q) => $q->where('category_id', $request->string('parent_id')))
                ->get(),
            'states' => State::all(),
            'cities' => City::query()->where('state_id', $request->integer('state_id'))->get(),
            'products' => Product::query()->where('vendor_id', $request->integer('vendor_id'))->get(),
            'entities' => [
                'categories' => Category::query()->whereNull('parent_id')->where('is_active', true)->get(),
                'sizes' => Size::query()->where('is_active', true)->get(),
                'colors' => Color::query()->where('is_active', true)->get(),
                'materials' => Material::query()->where('is_active', true)->get(),
            ],
            default => [],
        };
        return response()->json([
            'data' => is_array($data)
                ? collect($data)->map(fn($items) => GeneralResource::collection($items))
                : GeneralResource::collection($data),
        ]);
    }
}
