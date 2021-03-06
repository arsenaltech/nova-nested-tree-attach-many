<?php

namespace PhoenixLib\NovaNestedTreeAttachMany;

use Illuminate\Http\Request;
use Laravel\Nova\Authorizable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\ResourceRelationshipGuesser;

class NestedTreeAttachManyField extends Field
{
    use Authorizable;

    private $resourceClass;
    private $resourceName;
    private $manyToManyRelationship;

    public $component = 'nova-nested-tree-attach-many';

    public $showOnDetail = false;

    public $showOnIndex = false;

    public function __construct($name, $attribute = null, $resource = null)
    {
        parent::__construct($name, $attribute);

        $resource = $resource ?? ResourceRelationshipGuesser::guessResource($name);

        $this->resource = $resource;

        $this->resourceClass = $resource;
        $this->resourceName = $resource::uriKey();
        $this->manyToManyRelationship = $this->attribute;

        $this->fillUsing(function($request, $model, $attribute, $requestAttribute) use($resource) {
            if(is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
                $model::saved(function($model) use($attribute, $request) {
                    $model->{$attribute}()->sync(
                        json_decode($request->{$attribute}, true)
                    );
                });

                unset($request->{$attribute});
            }
        });

        $this->withMeta([
            'idKey'             => 'id',
            'labelKey'          => 'name',
            'childrenKey'       => 'children',
            'multiple'          => true,
            'flat'               => true,
            'searchable'        => true,
            'placeholder'       => __("Select Category"),
            'alwaysOpen'        => true,
            'sortValueBy'       => "LEVEL",
            'disabled'          => false,
            'rtl'               => false,
            'maxHeight'         => 500,
        ]);

        $tree = $this->resourceClass::newModel()::get()
            ->toTree();

        $this->withMeta(['options' => $tree]);
    }

    public function searchable(bool $searchable): NestedTreeAttachManyField
    {
        $this->withMeta([
            "searchable" => $searchable,
        ]);

        return $this;
    }

    public function withIdKey(string $idKey = 'id'): NestedTreeAttachManyField
    {
        $this->withMeta([
            "idKey" => $idKey,
        ]);

        return $this;
    }

    public function withLabelKey(string $labelKey = 'name'): NestedTreeAttachManyField
    {
        $this->withMeta([
            "labelKey" => $labelKey,
        ]);

        return $this;
    }

    public function withChildrenKey(string $childrenKey): NestedTreeAttachManyField
    {
        $this->withMeta([
            "childrenKey" => $childrenKey,
        ]);

        return $this;
    }

    public function withPlaceholder(string $placeholder): NestedTreeAttachManyField
    {
        $this->withMeta([
            "placeholder" => $placeholder,
        ]);

        return $this;
    }

    public function withMaxHeight(int $maxHeight): NestedTreeAttachManyField
    {
        $this->withMeta([
            "maxHeight" => $maxHeight,
        ]);

        return $this;
    }

    public function withAlwaysOpen(bool $alwaysOpen): NestedTreeAttachManyField
    {
        $this->withMeta([
            "alwaysOpen" => $alwaysOpen,
        ]);

        return $this;
    }

    public function withSortValueBy(string $sortBy): NestedTreeAttachManyField
    {
        $this->withMeta([
            "sortValueBy" => $sortBy,
        ]);

        return $this;
    }

    public function authorize(Request $request)
    {
        if(! $this->resourceClass::authorizable()) {
            return true;
        }

        if(! isset($request->resource)) {
            return false;
        }

        return call_user_func([ $this->resourceClass, 'authorizedToViewAny'], $request)
            && $request->newResource()->authorizedToAttachAny($request, $this->resourceClass::newModel())
            && parent::authorize($request);
    }
}
