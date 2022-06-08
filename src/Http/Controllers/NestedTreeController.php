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
        if (is_array($categories) && count($categories) > 0){
            $category = Category::with(["stores" => function($q){
                return $q->select('store_name');
            }])->whereIn("category.category_id",$categories)->get();
            foreach ($category as $key => $value){
                $category_tree = "[ " . $value->categoryBreadcrumb() . " ]";
                $stores = implode(' | ',array_column($value->stores->toArray(), 'store_name'));
                $urls[$value["category_id"]] = [html_entity_decode($value["category_name"]),html_entity_decode($category_tree) .' | '.html_entity_decode($stores)];
            }
        }
        return $urls;
    }
}
