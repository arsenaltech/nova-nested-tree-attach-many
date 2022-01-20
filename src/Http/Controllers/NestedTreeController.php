<?php

namespace PhoenixLib\NovaNestedTreeAttachMany\Http\Controllers;

use App\Models\Category;
use Illuminate\Routing\Controller;
use Laravel\Nova\Http\Requests\NovaRequest;
use PhoenixLib\NovaNestedTreeAttachMany\Domain\Relation\RelationHandlerFactory;

class NestedTreeController extends Controller
{
    private $factory;

    public function __construct(RelationHandlerFactory $factory)
    {
        $this->factory = $factory;
    }

    public function attached(NovaRequest $request, $resource, $resourceId, $relationship, $idKey)
    {
        $model = $request->findResourceOrFail()->model();

        $handler = $this->factory->make($model->{$relationship}());

        return $handler->retrieve($model, $relationship, $idKey);
    }

    public function getCategoryFullPath(\Laravel\Nova\Http\Requests\NovaRequest $request){
        $categories = $request->input('categories');
        $urls = [];
        if ($categories){
            foreach ($categories as $key => $value){
                $category = Category::with(["stores" => function($q){
                    return $q->select('store_name');
                }])->where("category.category_id",$value)->first();
                $category_tree = "[ " . $category->categoryBreadcrumb() . " ]";
                $stores = implode(' | ',array_column($category->stores->toArray(), 'store_name'));
                $urls[$category["category_id"]] = [html_entity_decode($category["category_name"]),html_entity_decode($category_tree) .' | '.html_entity_decode($stores)];
            }
        }
        return $urls;
    }
}
