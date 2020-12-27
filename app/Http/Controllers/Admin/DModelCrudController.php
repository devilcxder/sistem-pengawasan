<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\DModelRequest;
use App\Jobs\ModelJob;
use App\Models\DModel;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Storage;

/**
 * Class DModelCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class DModelCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation {
        destroy as traitDestroy;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\DModel::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/dmodel');
        CRUD::setEntityNameStrings('Model', 'Model');
        CRUD::orderBy('id', 'ASC');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {


        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */

        CRUD::column('id');
        CRUD::column('model_name');        
        CRUD::column('data_split');
        CRUD::column('accuracy');
        $this->crud->denyAccess('update');
        $this->crud->denyAccess('show');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(DModelRequest::class);



        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */

        CRUD::addfield([  // Select
            'label'     => "Kategori Dataset",
            'type'      => 'select',
            'name'      => 'category_id', // the db column for the foreign key

            // optional 
            // 'entity' should point to the method that defines the relationship in your Model
            // defining entity will make Backpack guess 'model' and 'attribute'
            'entity'    => 'category',

            // optional - manually specify the related model and attribute
            'model'     => "App\Models\Category", // related model
            'attribute' => 'category', // foreign key attribute that is shown to user
        ]);

        CRUD::addfield([
            'name' => 'model_name',
            'label' => 'Nama Model',
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'Masukkan nama model'
            ]
        ]);

        CRUD::addfield([   // Textarea
            'name'  => 'model_desc',
            'label' => 'Deskripsi Model',
            'type'  => 'textarea'
        ]);

        CRUD::addfield([   // Number
            'name' => 'data_split',
            'label' => 'Persentase Data Training',
            'type' => 'number',

            // optionals
            'attributes' => [
                'placeholder' => 'Min: 50%, Max:90% '
            ], // allow decimals            
            'suffix'     => "%",
        ]);

        CRUD::replaceSaveActions(
            [
                'name' => 'Simpan',
                'visible' => function ($crud) {
                    return true;
                },
                'redirect' => function ($crud, $request, $itemId) {
                    return $crud->route;
                },
            ],
        );
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function store(DmodelRequest $request)
    {
        dispatch(new ModelJob(request()->all()));
        \Alert::add('success', 'Model sedang dibuat')->flash();
        return \Redirect::to($this->crud->route);
    }

    public function destroy($id)
    {                
        $model_name = DModel::select('model_name')->where('id',$id)->first();
        unlink(storage_path() . '/model/' . $model_name->model_name . '.model');        
        $this->crud->hasAccessOrFail('delete');

        return $this->crud->delete($id);
    }
}
