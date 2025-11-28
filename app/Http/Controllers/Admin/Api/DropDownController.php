<?php

namespace App\Http\Controllers\Admin\Api;

use App\Models\Tag;
use App\Models\City;
use App\Models\Size;
use App\Models\Brand;
use App\Models\Color;
use App\Models\State;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Http\Resources\GeneralResource;
use Spatie\Permission\Models\Permission;

class DropDownController extends AdminController
{
    public function index(string $model, Request $request): JsonResponse
    {
        $parent_id = $request->integer('parent_id');
        $data = match ($model) {
            'roles' => Role::query()->where('guard_name', 'admin')->get(),
            'permissions' => Permission::all(),
            'colors' => Color::all(),
            'categories' => Category::query()->whereNull('parent_id')->get(),
            'sub_categories', 'sub_sub_categories' => Category::query()
                ->where('parent_id', $parent_id)
                ->where('is_active', true)
                ->get(),
            'materials' => Material::all(),
            'sizes' => Size::query()
                ->where('category_id', $request->integer('category_id'))
                ->get(),
            'tags' => Tag::query()
                ->where('category_id', $request->integer('category_id'))
                ->get(),
            'brands' => Brand::query()
                ->where('category_id', $request->integer('category_id'))
                ->get(),
            'states' => State::all(),
            'cities' => City::query()->where('state_id', $request->integer('state_id'))->get(),
            'vendors' => Vendor::whereNull('parent_id')->get(),
            default => [],
        };
        return response()->json([
            'data' => GeneralResource::collection($data),
        ]);
    }
}
