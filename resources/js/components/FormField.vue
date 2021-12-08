<template>
  <default-field :field="field" :errors="errors" :show-help-text="false" class="categories-tree">
    <template slot="field">
      <div class="flex">
        <div :dir="field.rtl ? 'rtl' : 'ltr'" class="w-3/5 nova-tree-attach-many">
          <treeselect v-model="selectedValues"
                      :id="field.name"
                      :multiple="field.multiple"
                      :options="field.options"
                      :flat="field.flatten"
                      :searchable="field.searchable"
                      :always-open="field.alwaysOpen"
                      :disabled="field.disabled"
                      :sort-value-by="field.sortValueBy"
                      :placeholder="field.placeholder"
                      :max-height="field.maxHeight"
                      :value-consists-of="field.valueConsistsOf"
                      :normalizer="normalizer"
          />
        </div>
        <div class="w-2/5 pl-8 pt-2" v-if="field.displayPath && fullUrl">
          <ul class="category-list pl-6">
            <li class="py-1 text-80 text-sm capitalize" v-for="(url,index) in fullUrl" :key="index"><strong>{{ index }}</strong> {{ url }}</li>
          </ul>
        </div>
      </div>
    </template>
  </default-field>
</template>

<style type="text/css">
.categories-tree .py-6.px-8.w-1\/2 { width: 80%; }
.category-list { list-style: none;}
</style>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'

import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'

export default {
  components: {Treeselect},
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data()
  {
    return {
      selectedValues: null,
      fullUrl:null,
    };
  },
  watch: {
    selectedValues: function(val) {
      //do something when the data changes.
      if (val) {
        this.getCategoryFullPath(val);
      }
    }
  },
  methods: {
    getCategoryFullPath(val){
      Nova.request().get('/nova-vendor/nova-nested-tree-attach-many/get-category-full-path',{
        params:{
          categories: val || this.selectedValues,
        }
      })
          .then( ( data ) => {
            this.fullUrl = data.data;
          } );
    },
    normalizer( node )
    {
      return {
        id: node[this.field.idKey],
        label: node[this.field.labelKey],
        isDisabled: node.hasOwnProperty(this.field.activeKey)
            && node[this.field.activeKey] === this.field.isActiveFalse,
        children: node.hasOwnProperty(this.field.childrenKey)
            && node[this.field.childrenKey].length > 0
            ? node[this.field.childrenKey]
            : false
      }
    },
    setInitialValue()
    {

      let baseUrl = '/nova-vendor/nova-nested-tree-attach-many/';

      if( this.resourceId )
      {
        const url = [
            baseUrl + this.resourceName,
            this.resourceId,
            'attached',
            this.field.attribute,
            this.field.idKey
        ];

        Nova.request( url.join('/') )
            .then( ( data ) => {

                if(!this.field.multiple)
                {
                    this.selectedValues = data.data || undefined;
                }
                else
                {
                    this.selectedValues = data.data || [];
                }
                if (this.selectedValues.length > 0){
                  this.getCategoryFullPath(this.selectedValues);
                }
            } );
      }
      else
      {
          if(!this.field.multiple)
          {
              this.selectedValues = undefined;
          }
          else
          {
              this.selectedValues = [];
          }
      }
    },
    fill( formData )
    {
      formData.append( this.field.attribute, JSON.stringify( this.selectedValues ) )
    },
  }
}
</script>
